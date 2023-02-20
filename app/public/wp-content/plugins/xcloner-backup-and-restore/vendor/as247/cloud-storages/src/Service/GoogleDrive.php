<?php


namespace As247\CloudStorages\Service;

use As247\CloudStorages\Exception\ApiException;
use As247\CloudStorages\Support\StorageAttributes;
use Google\Auth\HttpHandler\Guzzle5HttpHandler;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_Permission;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use As247\CloudStorages\Contracts\Storage\StorageContract;
use Psr\Http\Message\RequestInterface;
use Google_Service_Drive_FileList;
use Google_Service_Drive_DriveFile;
use Psr\Http\Message\StreamInterface;

class GoogleDrive
{
	/**
	 * MIME type of directory
	 *
	 * @var string
	 */
	const DIR_MIME = 'application/vnd.google-apps.folder';

	/**
	 * Default options
	 *
	 * @var array
	 */
	protected static $defaultOptions = [
		'additionalFetchField' => '',
		'publishPermission' => [
			'type' => 'anyone',
			'role' => 'reader',
			'withLink' => true
		],
		'appsExportMap' => [
			'application/vnd.google-apps.document' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.google-apps.spreadsheet' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.google-apps.drawing' => 'application/pdf',
			'application/vnd.google-apps.presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/vnd.google-apps.script' => 'application/vnd.google-apps.script+json',
			'default' => 'application/pdf'
		],
		// Default parameters for each command
		// see https://developers.google.com/drive/v3/reference/files
		// ex. 'defaultParams' => ['files.list' => ['includeTeamDriveItems' => true]]
		'defaultParams' => [
			'files.list'=>[
				'corpora'=>'user'// default is user
			],
		],
		'teamDrive' => false,
		'useTrash' => true,
	];
	protected $options;

	protected $publishPermission;
	/**
	 * Fetch fields setting for get
	 *
	 * @var string
	 */
	protected $fetchFieldsGet='id,name,mimeType,modifiedTime,parents,permissions,size,webContentLink,webViewLink';
	protected $fetchFieldsList='files({{fieldsGet}}),nextPageToken';
	protected $additionalFields;
	protected $defaultParams;
	protected $service;
	use HasLogger;
	public function __construct(Google_Service_Drive $service,$options=[])
	{
		$this->service=$service;
		$this->setupLogger($options);
		$this->options = array_replace_recursive(static::$defaultOptions, $options);
		$this->publishPermission = $this->options['publishPermission'];

		if ($this->options['additionalFetchField']) {
			$this->fetchFieldsGet .= ',' . $this->options['additionalFetchField'];
			$this->additionalFields = explode(',', $this->options['additionalFetchField']);
		}
		$this->fetchFieldsList = str_replace('{{fieldsGet}}', $this->fetchFieldsGet, $this->fetchFieldsList);
		if (isset($this->options['defaultParams'])) {
			$this->defaultParams = $this->options['defaultParams'];
		}
		if ($this->options['teamDrive']) {
			if(is_string($this->options['teamDrive'])){
				$this->options['teamDrive']=[
					'driveId'=>$this->options['teamDrive'],
					'corpora'=>'drive',
					'includeItemsFromAllDrives'=>true,
				];
			}
			$this->enableTeamDriveSupport();
		}
	}

	public function isTeamDrive(){
		return $this->options['teamDrive'];
	}
	public function getTeamDriveId(){
		return $this->options['teamDrive']['driveId']??null;
	}
	public function getClient(){
		return $this->service->getClient();
	}
	public function normalizeMetadata(Google_Service_Drive_DriveFile $object, $path)
	{
		$id = $object->getId();
		$result = [
			StorageAttributes::ATTRIBUTE_PATH => is_string($path)? ltrim($path,'\/'):null,
			StorageAttributes::ATTRIBUTE_TYPE => $object->mimeType === self::DIR_MIME ? StorageAttributes::TYPE_DIRECTORY : StorageAttributes::TYPE_FILE,
			StorageAttributes::ATTRIBUTE_LAST_MODIFIED=>strtotime($object->getModifiedTime())
		];
		$result[StorageAttributes::ATTRIBUTE_MIME_TYPE] = $object->getMimeType();
		$result[StorageAttributes::ATTRIBUTE_FILE_SIZE] = (int) $object->getSize();
		$result[StorageAttributes::ATTRIBUTE_VISIBILITY]=$this->getVisibility($object);
		// attach additional fields
		if ($this->additionalFields) {
			foreach($this->additionalFields as $field) {
				if (property_exists($object, $field)) {
					$result['@'.$field] = $object->$field;
				}
			}
		}
		$result['@id']=$id;
		$result['@shareLink']=$result['@link']=$object->getWebViewLink();
		$result['@downloadUrl']=$object->getWebContentLink();
		return $result;
	}
	protected function getVisibility(Google_Service_Drive_DriveFile $object){
		$permissions = $object->getPermissions();
		$visibility = StorageContract::VISIBILITY_PRIVATE;
		foreach ($permissions as $permission) {
			if ($permission->type === $this->publishPermission['type'] && $permission->role === $this->publishPermission['role']) {
				$visibility = StorageContract::VISIBILITY_PUBLIC;
				break;
			}
		}
		return $visibility;
	}
	/**
	 * Enables Team Drive support by changing default parameters
	 *
	 * @return void
	 *
	 * @see https://developers.google.com/drive/v3/reference/files
	 * @see \Google_Service_Drive_Resource_Files
	 */
	public function enableTeamDriveSupport()
	{
		$this->defaultParams = array_merge_recursive(
			array_fill_keys([
				'files.copy', 'files.create', 'files.delete',
				'files.trash', 'files.get', 'files.list', 'files.update',
				'files.watch',
				'files.permission.create',
				'files.permission.delete'
			], ['supportsAllDrives' => true]),
			$this->defaultParams
		);

		$this->mergeCommandDefaultParams('files.list',$this->options['teamDrive']);
	}



	protected function getParams($cmd, ...$params){
		$default=$this->getDefaultParams($cmd);
		return array_replace($default,...$params);
	}
	protected function getDefaultParams($cmd){
		if(isset($this->defaultParams[$cmd]) && is_array($this->defaultParams[$cmd])){
			return $this->defaultParams[$cmd];
		}
		return [];
	}
	protected function mergeCommandDefaultParams($cmd,$params){
		if(!isset($this->defaultParams[$cmd])){
			$this->defaultParams[$cmd]=[];
		}
		$this->defaultParams[$cmd]=array_replace_recursive($this->defaultParams[$cmd],$params);
		return $this;
	}

	/**
	 * Create directory
	 * @param $name
	 * @param $parentId
	 * @return bool|Google_Service_Drive_DriveFile|RequestInterface
	 */
	public function dirCreate($name, $parentId){
		$file = new Google_Service_Drive_DriveFile();
		$file->setName($name);
		$file->setParents([
			$parentId
		]);
		$file->setMimeType(self::DIR_MIME);
		return $this->filesCreate($file);
	}
	/**
	 * Find files by name in given directory
	 * @param $name
	 * @param $parent
	 * @param $mineType
	 * @return Google_Service_Drive_FileList|Google_Service_Drive_DriveFile[]
	 */
	public function filesFindByName($name,$parent, $mineType=null){
		if($parent instanceof Google_Service_Drive_DriveFile){
			$parent=$parent->getId();
		}
		$timerStart=microtime(true);
		$client=$this->service->getClient();
		$collectOthers=false;
		$q='trashed = false and "%s" in parents and name = "%s"';
		$argsMatchName = [
			'pageSize' => 2,
			'q' =>sprintf($q,$parent,$name,static::DIR_MIME),
		];
		if($collectOthers) {
			$client->setUseBatch(true);
			$batch = $this->service->createBatch();

			$filesMatchedName=$this->filesListFiles($argsMatchName);
			$q='trashed = false and "%s" in parents';
			if($mineType){
				$q.=" and mimeType ".$mineType;
			}
			$argsOthers = [
				'pageSize' => 50,
				'q' =>sprintf($q,$parent,$name,static::DIR_MIME),
			];
			$otherFiles=$this->filesListFiles($argsOthers);
			$batch->add($filesMatchedName,'matched');
			$batch->add($otherFiles,'others');
			$results = $batch->execute();
			$files=[];
			$isFullResult=empty($mineType);//if limited to a mime type so it is not full result
			if(!isset($results['response-others'])){
				$isFullResult=false;
			}
			foreach ($results as $key => $result) {
				if ($result instanceof Google_Service_Drive_FileList) {
					if($key==='response-matched'){
						if(count($result)>1){
							throw new ApiException("Duplicated file ".$name.' in '.$parent);
						}
					}
					foreach ($result as $file) {
						if (!isset($files[$file->id])) {
							$files[$file->id] = $file;
						}
					}
					if ($key === 'response-others' && $result->nextPageToken) {
						$isFullResult = false;
					}
				}
			}
			$client->setUseBatch(false);
		}else{
			$isFullResult=false;
			$list=$this->filesListFiles($argsMatchName);
			$files=$list->getFiles();
		}

		$this->logRequest('files.list.by_name',[
		    'query'=>'find for '.$name.' in '.$parent,
            'duration'=>microtime(true)-$timerStart]);
		$list=new Google_Service_Drive_FileList();
		$list->setFiles($files);
		return [$list,$isFullResult];
	}
	/**
	 * @param array $optParams
	 * @return Google_Service_Drive_FileList | RequestInterface
	 */
	public function filesListFiles($optParams = array()){
        $timerStart=microtime(true);
		$optParams=$this->getParams('files.list',['fields' => $this->fetchFieldsList],$optParams);
		$result= $this->service->files->listFiles($optParams);
        if(!$this->service->getClient()->shouldDefer()) {
            $this->logRequest('files.list', [
                'query'=>func_get_args(),
                'duration'=>microtime(true)-$timerStart,
                ]);
        }
		return $result;
	}

	/**
	 * @param $fileId
	 * @param array $optParams
	 * @return Google_Service_Drive_DriveFile|RequestInterface
	 */
	public function filesGet($fileId, $optParams = array()){
		if($fileId instanceof Google_Service_Drive_DriveFile){
			$fileId=$fileId->getId();
		}
        $timerStart=microtime(true);

		$optParams=$this->getParams('files.get',['fields' => $this->fetchFieldsGet],$optParams);
		$result= $this->service->files->get($fileId,$optParams);
        $this->logRequest('files.get', [
            'query'=>func_get_args(),
            'duration'=>microtime(true)-$timerStart,
        ]);
        return $result;
	}

	/**
	 * @param Google_Service_Drive_DriveFile $postBody
	 * @param array $optParams
	 * @return Google_Service_Drive_DriveFile | RequestInterface
	 */
	public function filesCreate(Google_Service_Drive_DriveFile $postBody, $optParams = array()){
		$timerStart=microtime(true);
		$optParams=$this->getParams('files.create',['fields' => $this->fetchFieldsGet],$optParams);
		$result= $this->service->files->create($postBody,$optParams);
        $this->logRequest('files.create', [
            'query'=>func_get_args(),
            'duration'=>microtime(true)-$timerStart,
        ]);
        return $result;
	}

	public function filesUpdate($fileId, Google_Service_Drive_DriveFile $postBody, $optParams = array()){
        $timerStart=microtime(true);
	    if($fileId instanceof Google_Service_Drive_DriveFile){
			$fileId=$fileId->getId();
		}
		$optParams=$this->getParams('files.update',['fields' => $this->fetchFieldsGet],$optParams);
		$result= $this->service->files->update($fileId,$postBody,$optParams);
        $this->logRequest('files.update', [
            'query'=>func_get_args(),
            'duration'=>microtime(true)-$timerStart,
        ]);
        return $result;
	}
	public function filesCopy($fileId, Google_Service_Drive_DriveFile $postBody, $optParams = array()){
	    $timerStart=microtime(true);
		if($fileId instanceof Google_Service_Drive_DriveFile){
			$fileId=$fileId->getId();
		}

		$optParams=$this->getParams('files.copy',['fields' => $this->fetchFieldsGet],$optParams);
		$result= $this->service->files->copy($fileId,$postBody,$optParams);
        $this->logRequest('files.copy', [
            'query'=>func_get_args(),
            'duration'=>microtime(true)-$timerStart,
        ]);
        return $result;
	}
	public function filesDelete($fileId, $optParams = array()){
	    $timerStart=microtime(true);
		if($fileId instanceof Google_Service_Drive_DriveFile){
			$fileId=$fileId->getId();
		}
		$optParams=$this->getParams('files.delete',$optParams);
		if($this->options['useTrash']){
			$fileUpdate=new Google_Service_Drive_DriveFile();
			$fileUpdate->setTrashed(true);
			$result=$this->service->files->update($fileId,$fileUpdate,$optParams);
		}else {
			$result = $this->service->files->delete($fileId, $optParams);
		}
        $this->logRequest('files.delete', [
            'query'=>func_get_args(),
            'duration'=>microtime(true)-$timerStart,
        ]);
        return $result;
	}

	/**
	 * @param $fileId
	 * @return resource|null
	 * @throws GuzzleException
	 */
	public function filesRead($fileId){
        $timerStart=microtime(true);
		if($fileId instanceof Google_Service_Drive_DriveFile){
			$fileId=$fileId->getId();
		}
		$this->service->getClient()->setUseBatch(true);
		$request=$this->filesGet($fileId, ['alt' => 'media']);
		$this->service->getClient()->setUseBatch(false);
		$stream=null;
		$client=$this->service->getClient()->authorize();
		$handler=HttpHandlerFactory::build($client);
		if($handler instanceof Guzzle5HttpHandler){
			//Handler v5 still working but stream read all to buffer
			//We use native method to read
			$stream=$this->fileReadNative($request);

		}else {
			$response = $handler($request, ['stream' => true]);
			if ($response->getBody() instanceof Stream) {
				$stream = $response->getBody()->detach();
			}
		}

        $this->logRequest('files.read', [
            'query'=>func_get_args(),
            'duration'=>microtime(true)-$timerStart,
        ]);
		return $stream;
	}

	protected function fileReadNative(RequestInterface $request){
		$token=$this->service->getClient()->getAccessToken();
		$token=$token['access_token'];
		$url=$request->getUri()->__toString();
		$auth = "Authorization: Bearer $token";
		$opts = array (
			'http' => array (
				'method' => "GET",
				'header' => $auth,
				'user_agent' => 'as247/cloud-storages',
			)
		);
		$context = stream_context_create($opts);
		$fp = fopen($url, 'rb', false, $context);
		return $fp;
	}

	/**
	 * Publish file
	 * @param Google_Service_Drive_DriveFile $file
	 */
	public function publish(Google_Service_Drive_DriveFile $file)
	{
		if ($this->getVisibility($file) === StorageContract::VISIBILITY_PUBLIC) {//already published
			return;
		}
        $timerStart=microtime(true);
		$permission = new Google_Service_Drive_Permission($this->publishPermission);
		$optParams=$this->getParams('files.permission.create');
		if ($newPermission=$this->service->permissions->create($file->getId(), $permission, $optParams)) {
			$permissions=$file->getPermissions();
			$permissions=array_merge($permissions,[$newPermission]);
			$file->setPermissions($permissions);
		}
        $this->logRequest('files.permission.create', [
            'query'=>$file->getId(),
            'duration'=>microtime(true)-$timerStart,
        ]);
	}

	/**
	 * Un-publish specified path item
	 * @param Google_Service_Drive_DriveFile $file
	 */
	public function unPublish(Google_Service_Drive_DriveFile $file)
	{
        $timerStart=microtime(true);
		$permissions = $file->getPermissions();
		$optParams=$this->getParams('files.permission.delete');
		foreach ($permissions as $index=> $permission) {
			if ($permission->type === 'anyone' && $permission->role === 'reader') {
				$this->service->permissions->delete($file->getId(), $permission->getId(), $optParams);
				unset($permissions[$index]);
			}
		}
		$file->setPermissions($permissions);
        $this->logRequest('files.permission.create', [
            'query'=>$file->getId(),
            'duration'=>microtime(true)-$timerStart,
        ]);
	}

	public function filesUploadChunk(Google_Service_Drive_DriveFile $file,StreamInterface $contents,$chunk){
		$client = $this->service->getClient();

		$client->setDefer(true);
		if (!$file->getId()) {
			$request = $this->filesCreate($file);
		} else {
			$update=new Google_Service_Drive_DriveFile();
			$update->setMimeType($file->getMimeType());
			$request = $this->filesUpdate($file->getId(), $update);
		}
		$mime=$file->getMimeType();
		// Create a media file upload to represent our upload process.
		$media = new Google_Http_MediaFileUpload($client, $request, $mime, null, true, $chunk);
		$media->setFileSize($contents->getSize());
		// Upload the various chunks. $status will be false until the process is
		// complete.
		$status = false;
		$contents->rewind();
		while (!$status && !$contents->eof()) {
			$status = $media->nextChunk($contents->read($chunk));
		}

		$client->setDefer(false);
		return $status;
	}
	/**
	 * @param Google_Service_Drive_DriveFile $file
	 * @param $contents
	 * @return Google_Service_Drive_DriveFile|RequestInterface
	 */
	public function filesUploadSimple(Google_Service_Drive_DriveFile $file,StreamInterface $contents){
		$params = [
			'data' => $contents->getContents(),
			'uploadType' => 'media',
		];
		if (!$file->getId()) {
			$obj = $this->filesCreate($file, $params);
		} else {
			$update=new Google_Service_Drive_DriveFile();
			$update->setMimeType($file->getMimeType());
			$obj = $this->filesUpdate($file->getId(), $update, $params);
		}
		return $obj;
	}

	protected function logRequest($cmd, $query){
		$this->logger->request($cmd,$query);
	}


}
