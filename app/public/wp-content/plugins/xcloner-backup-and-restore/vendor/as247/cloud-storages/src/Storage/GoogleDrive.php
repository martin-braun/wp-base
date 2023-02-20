<?php


namespace As247\CloudStorages\Storage;

use As247\CloudStorages\Cache\PathCache;
use As247\CloudStorages\Cache\Stores\GoogleDrivePersistentStore;
use As247\CloudStorages\Cache\Stores\GoogleDriveStore;
use As247\CloudStorages\Exception\FileNotFoundException;
use As247\CloudStorages\Exception\InvalidStreamProvided;
use As247\CloudStorages\Exception\InvalidVisibilityProvided;
use As247\CloudStorages\Exception\UnableToCopyFile;
use As247\CloudStorages\Exception\UnableToCreateDirectory;
use As247\CloudStorages\Exception\UnableToDeleteDirectory;
use As247\CloudStorages\Exception\UnableToDeleteFile;
use As247\CloudStorages\Exception\UnableToMoveFile;
use As247\CloudStorages\Exception\UnableToReadFile;
use As247\CloudStorages\Exception\UnableToRetrieveMetadata;
use As247\CloudStorages\Exception\UnableToSetVisibility;
use As247\CloudStorages\Exception\UnableToWriteFile;
use As247\CloudStorages\Service\StreamWrapper;
use As247\CloudStorages\Support\Config;
use As247\CloudStorages\Support\FileAttributes;
use As247\CloudStorages\Support\Path;
use Exception;
use Generator;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use As247\CloudStorages\Service\GoogleDrive as GoogleDriveService;
use Google_Service_Drive_FileList;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Traversable;

class GoogleDrive extends Storage
{
	/**
	 * Google_Service_Drive instance
	 */
	protected $service;

	protected $root;//Root id
	protected $maxFolderLevel = 128;

	public function __construct(Google_Service_Drive $service, $options)
	{
		if (is_string($options)) {
			$options = ['root' => $options];
		}
		if(isset($options['teamDrive'])){
			if($options['teamDrive']===true){
				$options['teamDrive']=$options['root'];//Team drive same as root if boolean
			}elseif(empty($options['root']) && is_string($options['teamDrive'])){
				$options['root']=$options['teamDrive'];
			}
		}
		$this->service = new GoogleDriveService($service, $options);
		$this->setLogger($this->service->getLogger());
		$this->setRoot($options);
	}
	/**
	 * Gets the service (Google_Service_Drive)
	 *
	 * @return object  Google_Service_Drive
	 */
	public function getService(){
		return $this->service;
	}
	public function getCache(){
		return $this->cache;
	}

	protected function setRoot($options)
	{
		$root = $options['root'];
		$this->root = $root;
		if(isset($options['cache']) && is_string($options['cache'])){
			$options['cache'] = new PathCache(new GoogleDrivePersistentStore($options['cache']));
		}else {
			$options['cache'] = new PathCache(new GoogleDriveStore());
		}
		$this->setupCache($options);
	}
	public function getRoot(){
		return $this->root;
	}
	protected function initializeCacheRoot(){
		$this->cache->getStore()->mapDirectory('/', $this->root);
	}

	/**
	 * @param string|array $path create directory structure
	 * @return string folder id
	 */
	protected function ensureDirectory($path)
	{
		$path = Path::clean($path);
		if ($this->isFile($path)) {
			throw UnableToCreateDirectory::atLocation($path, "File already exists");
		}
		if (isset($this->maxFolderLevel)) {
			$nestedFolderLevel = count(explode('/', $path)) - 1;
			if ($nestedFolderLevel > $this->maxFolderLevel) {// -1 for /
				throw UnableToCreateDirectory::atLocation($path, "Maximum nesting folder exceeded");
			}
		}
		$this->logger->log("mkdir: $path");
		list($parent, $paths, $currentPaths) = $this->detectPath($path);

		if (count($paths) != 0) {
			while (null !== ($name = array_shift($paths))) {
				$currentPaths[] = $name;
				$currentPathString = join('/', $currentPaths);
				if ($this->isFile($currentPaths)) {
					throw  UnableToCreateDirectory::atLocation($currentPathString, "File already exists");
				}

				$created = $this->service->dirCreate($name, $parent);
				//echo 'Created: '.print_r($currentPaths);
				$this->cache->put($currentPaths, $created);
				$this->cache->complete($currentPaths);
				$parent = $created->getId();
			}
		}
		return $parent;
	}


	/**
	 * @inheritDoc
	 */
	public function writeStream(string $path, $contents, Config $config = null): void
	{
		$this->upload($path, $contents, $config);
	}

	/**
	 * Delete file only
	 * @param $path
	 * @return void
	 * @throws FileNotFoundException
	 */
	public function delete(string $path): void
	{
		if ($this->isDirectory($path)) {
			throw UnableToDeleteFile::atLocation($path, "$path is directory");
		}
		$file = $this->find($path);
		if (!$file) {//already deleted
			throw FileNotFoundException::create($path);
		}
		if ($file->getId() === $this->root) {
			throw UnableToDeleteDirectory::atLocation($path, "Root directory cannot be deleted");
		}
		$this->service->filesDelete($file);
		$this->cache->delete($path);
		$this->logger->log("Deleted $path");
	}

	/**
	 * @inheritDoc
	 */
	public function deleteDirectory(string $path): void
	{
		if ($this->isFile($path)) {
			throw UnableToDeleteDirectory::atLocation($path, "$path is file");
		}
		$file = $this->find($path);
		if (!$file) {//already deleted
			throw FileNotFoundException::create($path);
		}
		if ($file->getId() === $this->root) {
			throw UnableToDeleteDirectory::atLocation($path, "Root directory cannot be deleted");
		}
		$this->service->filesDelete($file);
		$this->cache->deleteDir($path);
		$this->logger->log("Deleted $path");
	}


	/**
	 * Find for path
	 * @param $path
	 * @return false|Google_Service_Drive_DriveFile
	 */
	protected function find($path)
	{
		if ($path instanceof Google_Service_Drive_DriveFile) {
			return $path;
		}

		list(, $paths) = $this->detectPath($path);

		if (count($paths) >= 2) {
			//remaining 2 segments /A/B/C/file.txt
			//C not exists mean file.txt also not exists
			return false;
		}
		if (null!==($cached=$this->cache->get($path))) {
			return $cached;
		}
		return false;

	}

	public function createDirectory(string $path, Config $config = null): void
	{
		$this->ensureDirectory(Path::clean($path));
		$result = $this->getMetadata($path);
		if ($config && $visibility = $config->get('visibility')) {
			$this->setVisibility($path, $visibility);
			$result['visibility'] = $visibility;
		}
	}

	public function copy(string $fromPath, string $toPath, Config $config = null): void
	{
		$fromPath = Path::clean($fromPath);
		$toPath = Path::clean($toPath);
		$from = $this->find($fromPath);
		if (!$from) {
			throw UnableToCopyFile::fromLocationTo($fromPath, $toPath, "$fromPath not exists");
		}
		if ($this->isDirectory($fromPath)) {
			throw UnableToCopyFile::fromLocationTo($fromPath, $toPath, "$fromPath is directory");
		}


		if ($this->isDirectory($toPath)) {
			throw UnableToCopyFile::fromLocationTo($fromPath, $toPath, "$toPath is directory");
		}
		if ($this->has($toPath)) {
			$this->delete($toPath);
		}
		$paths = $this->parsePath($toPath);
		$fileName = array_pop($paths);
		$dirName = $paths;
		$parents = [$this->ensureDirectory($dirName)];
		$file = new Google_Service_Drive_DriveFile();
		$file->setName($fileName);
		$file->setParents($parents);
		$newFile = $this->service->filesCopy($from->id, $file);
		$this->cache->put($toPath, $newFile);
		$this->logger->log("Copied file: $fromPath -> $toPath");
	}

	public function move(string $fromPath, string $toPath, Config $config = null): void
	{
		$fromPath = Path::clean($fromPath);
		$toPath = Path::clean($toPath);
		if ($fromPath === $toPath) {
			return;
		}
		$from = $this->find($fromPath);
		if (!$from) {
			throw UnableToMoveFile::fromLocationTo($fromPath, $toPath, "$fromPath not found");
		}
		$oldParent = $from->getParents()[0];
		$newParentId = null;
		if ($this->isFile($from)) {//we moving file
			if ($this->has($toPath)) {
				if ($this->isDirectory($toPath)) {//Destination path is directory
					throw UnableToMoveFile::fromLocationTo($fromPath, $toPath, "Destination path exists as a directory, cannot overwrite");
				} else {
					$this->delete($toPath);
				}
			}
		} else {//we moving directory
			if ($this->has($toPath)) {
				if ($this->isFile($toPath)) {//Destination path is file
					throw UnableToMoveFile::fromLocationTo($fromPath, $toPath, "Destination path exists as a file, cannot overwrite");
				} else {
					$this->deleteDirectory($toPath);//overwrite, remove it first
				}
			}
		}
		$paths = $this->parsePath($toPath);
		$fileName = array_pop($paths);
		$dirName = $paths;
		$newParentId = $this->ensureDirectory($dirName);
		$file = new Google_Service_Drive_DriveFile();
		$file->setName($fileName);
		$opts=[];
		if ($newParentId !== $oldParent) {
			$opts['addParents'] = $newParentId;
			$opts['removeParents'] = $oldParent;
		}

		$result=$this->service->filesUpdate($from->getId(), $file, $opts);
		if(!in_array($newParentId,$result->getParents())){
			throw UnableToMoveFile::fromLocationTo($fromPath, $toPath,'Service update failure');
		}
		$this->cache->move($fromPath, $toPath);
		$this->logger->log("Moved file: $fromPath -> $toPath");
	}


	/**
	 * Upload|Update item
	 *
	 * @param string $path
	 * @param $contents
	 * @param Config|null $config
	 * @return FileAttributes
	 * @throws FileNotFoundException
	 */
	protected function upload(string $path, $contents, Config $config = null)
	{
		try {
			$contents = StreamWrapper::wrap($contents);
		}catch (InvalidArgumentException $e){
			throw new InvalidStreamProvided("Invalid contents. ".$e->getMessage());
		}
		$contents->rewind();
		if ($this->isDirectory($path)) {
			throw UnableToWriteFile::atLocation($path, "$path is directory");
		}

		$paths = $this->parsePath($path);
		$fileName = array_pop($paths);
		$dirName = $paths;
		//Try to find file before, because if it was removed before, ensure directory will recreate same directory and it may available again
		$parentId = $this->ensureDirectory($dirName);
		if (!$parentId) {
			throw UnableToWriteFile::atLocation($path, "Not able to create parent directory $dirName");
		}
		$file = $this->find($path);
		if (!$file) {
			$file = new Google_Service_Drive_DriveFile();
			$file->setName($fileName);
			$file->setParents([
				$parentId
			]);

		}
		$newMimeType = $config ? $config->get('mimetype', $config->get('mime_type')) : null;
		if ($newMimeType) {
			$file->setMimeType($newMimeType);
		}

		$size5MB = 5 * 1024 * 1024;
		$chunkSize = $config ? $config->get('chunk_size', $size5MB) : $size5MB;
		if ($contents->getSize() <= $size5MB) {
			$obj = $this->service->filesUploadSimple($file, $contents);
		} else {
			$obj = $this->service->filesUploadChunk($file, $contents, $chunkSize);
		}
		$this->logger->log("Uploaded: $path");
		if ($obj instanceof Google_Service_Drive_DriveFile) {
			$this->cache->put($path, $obj);//update cache first

			if ($config && $visibility = $config->get('visibility')) {
				$this->setVisibility($path, $visibility);
			}
			return $this->getMetadata($path);
		}

		throw UnableToWriteFile::atLocation($path);
	}

	/**
	 * @param string $directory
	 * @param bool $recursive
	 * @return Generator
	 */
	public function listContents(string $directory, bool $recursive = false): Traversable
	{
		if (!$this->isDirectory($directory)) {
			yield from [];
			return;
		}
		$results = $this->fetchDirectory($directory, 1000);
		foreach ($results as $id => $result) {
			yield $id => $result;
			if ($recursive && $result['type'] === 'dir') {
				yield from $this->listContents($result['path'], $recursive);
			}
		}
	}

	protected function fetchDirectory($directory, $pageSize = 1000)
	{
		//echo 'Fetching: '.$directory;
		if ($this->cache->isCompleted($directory)) {
			foreach ($this->cache->query($directory) as $path => $file) {
				if ($file instanceof Google_Service_Drive_DriveFile) {
					yield $file->getId() => $this->service->normalizeMetadata($file, $path);
				}
			}
			return null;
		}

		list($itemId) = $this->detectPath($directory);
		$pageSize = min($pageSize, 1000);//limit range of page size
		$pageSize = max($pageSize, 1);//
		$parameters = [
			'pageSize' => $pageSize,
			'q' => sprintf('trashed = false and "%s" in parents', $itemId)
		];
		$pageToken = NULL;
		do {
			try {
				if ($pageToken) {
					$parameters['pageToken'] = $pageToken;
				}
				$fileObjs = $this->service->filesListFiles($parameters);
				if ($fileObjs instanceof Google_Service_Drive_FileList) {
					foreach ($fileObjs as $obj) {
						$id = $obj->getId();
						$result = $this->service->normalizeMetadata($obj, rtrim($directory,'\/') . '/' . $obj->getName());
						yield $id => $result;
						$this->cache->put($result['path'], $obj);
					}
					$pageToken = $fileObjs->getNextPageToken();
				} else {
					$pageToken = NULL;
				}
			} catch (Exception $e) {
				$pageToken = NULL;
			}
		} while ($pageToken);

		$this->cache->complete($directory);
	}

	/**
	 * Publish specified path item
	 *
	 * @param string $path
	 */
	protected function publish(string $path)
	{
		if (!$file = $this->find($path)) {
			throw UnableToSetVisibility::atLocation($path, 'File not found');
		}
		$this->service->publish($file);
		$this->cache->put($path,$file);
	}

	/**
	 * Un-publish specified path item
	 *
	 * @param string $path
	 */
	protected function unPublish(string $path)
	{
		if (!$file = $this->find($path)) {
			throw UnableToSetVisibility::atLocation($path, 'File not found');
		}
		$this->service->unPublish($file);
		$this->cache->put($path,$file);
	}

	/**
	 * @param string $path
	 * @param mixed $visibility
	 */
	public function setVisibility(string $path, $visibility): void
	{
		if ($visibility === Storage::VISIBILITY_PUBLIC) {
			$this->publish($path);
		} elseif ($visibility === Storage::VISIBILITY_PRIVATE) {
			$this->unPublish($path);
		} else {
			throw InvalidVisibilityProvided::withVisibility($visibility, join(' or ', [Storage::VISIBILITY_PUBLIC, Storage::VISIBILITY_PRIVATE]));
		}
	}


	public function readStream(string $path)
	{
		$file = $this->find($path);
		if (!$this->isFile($path)) {
			throw FileNotFoundException::create($path);
		}
		try {
			return $this->service->filesRead($file);
		} catch (GuzzleException $e) {
			throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
		}
	}



	protected function parsePath($path)
	{
		$paths = Path::explode($path);
		$directory = [];
		$file = [];
		$level = 0;
		foreach ($paths as $path) {
			if ($level++ > $this->maxFolderLevel) {
				$file[] = $path;
			} else {
				$directory[] = $path;
			}
		}
		if (!$file) {
			$file[] = array_pop($directory);
		}
		$file = join('/', $file);
		$directory[] = $file;
		return $directory;
	}

	/**
	 * Travel through the path tree then return folder id, remaining path, current path
	 * eg: /path/to/the/file/text.txt
	 *    - if we have directory /path/to then it return [path_to_id, ['the','file','text.txt'], ['path','to']
	 *  - if we have /path/to/the/file/text.txt then it return [id_of_path_to_the_file, ['text.txt'], ['path','to','the','file'] ]
	 * @param $path
	 * @return array
	 */
	protected function detectPath($path)
	{
		$paths = $this->parsePath($path);
		$this->logger->log("Path finding: " . join(', ',$paths));
		$currentPaths = [];
		if($this->cache->get('/')===null) {
			$this->initializeCacheRoot();
		}
		$parent = $this->cache->get('/');
		while (null !== ($name = array_shift($paths))) {
			$parentPaths = $currentPaths;
			$currentPaths[] = $name;
			$foundDir = $this->cache->get($currentPaths);
			if (!is_null($foundDir)) {
				if ($foundDir && $this->isDirectory($foundDir)) {
					$parent = $foundDir;
					continue;
				} else {
					//echo 'break at...'.implode($currentPaths);
					array_pop($currentPaths);
					array_unshift($paths, $name);

					break;
				}
			}
			list($files, $isFull) = $this->service->filesFindByName($name, $parent);
			if ($isFull) {
				$this->cache->complete($parentPaths);
			}
			$foundDir = false;
			//Set current path as not exists, it will be updated again when we got matched file
			$this->cache->put($currentPaths, false);
			if ($files->count()) {
				foreach ($files as $file) {
					if ($file instanceof Google_Service_Drive_DriveFile) {
						$fileFound = $parentPaths;
						$fileFound[]=$file->getName();
						$this->cache->put($fileFound, $file);
						if ($this->isDirectory($file) && $file->getName() === $name) {
							$foundDir = $file;
						}
					}
				}
			}

			if (!$foundDir) {
				array_pop($currentPaths);
				array_unshift($paths, $name);
				break;
			}
			$parent = $foundDir;
		}
		$parent = $parent->getId();
		$this->logger->log("Found: " . $parent . '(' . join('/',$currentPaths) . ") " . join('/',$paths));
		return [$parent, $paths, $currentPaths];
	}


	/**
	 * Check if given path exists
	 * @param $path
	 * @return bool
	 */
	protected function has($path)
	{
		try {
			$this->getMetadata($path);
			return true;
		}catch (FileNotFoundException $e){
			return false;
		}
	}

	protected function isDirectory($path)
	{
		try {
			$meta = $this->getMetadata($path);
			return $meta->isDir();
		}catch (FileNotFoundException $e){
			return false;
		}

	}

	protected function isFile($path)
	{
		try {
			$meta = $this->getMetadata($path);
			return $meta->isFile();
		}catch (FileNotFoundException $e){
			return false;
		}
	}



	/**
	 * @param $path
	 * @return FileAttributes
	 * @throws FileNotFoundException
	 */
	public function getMetadata($path):FileAttributes
	{
		if ($obj = $this->find($path)) {
			if ($path instanceof Google_Service_Drive_DriveFile) {
				$path = null;
			}
			if ($obj instanceof Google_Service_Drive_DriveFile) {
				$attributes = $this->service->normalizeMetadata($obj, $path);

				return FileAttributes::fromArray($attributes);
			}
			throw UnableToRetrieveMetadata::create($path, 'metadata');
		}
		throw FileNotFoundException::create(Path::clean($path));
	}
}
