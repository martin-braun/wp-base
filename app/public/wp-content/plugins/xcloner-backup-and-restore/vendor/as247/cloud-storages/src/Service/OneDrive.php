<?php


namespace As247\CloudStorages\Service;

use ArrayObject;
use As247\CloudStorages\Exception\InvalidPathException;
use As247\CloudStorages\Exception\InvalidStreamProvided;
use As247\CloudStorages\Support\Path;
use As247\CloudStorages\Support\StorageAttributes;
use Generator;
use As247\CloudStorages\Contracts\Storage\StorageContract;
use InvalidArgumentException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphRequest;

class OneDrive
{
	protected $graph;
	const ROOT = '/me/drive/root';
	protected $publishPermission = [
		'role' => 'read',
		'scope' => 'anonymous',
		'withLink' => true
	];
	protected $options;
	use HasLogger;
	public function __construct(Graph $graph,$options=[])
	{
		$this->options=$options;
		$this->setupLogger($options);
	    $this->graph=$graph;
	}

	function normalizeMetadata(array $response, string $path): array
	{
		$permissions=$response['permissions']??[];
		$visibility = StorageContract::VISIBILITY_PRIVATE;
		$shareLink=null;
		foreach ($permissions as $permission) {
			if(!isset($permission['link']['scope']) || !isset($permission['roles'])){
				continue;
			}
			if(in_array($this->publishPermission['role'],$permission['roles'])
				&& $permission['link']['scope']==$this->publishPermission['scope']){
				$visibility = StorageContract::VISIBILITY_PUBLIC;
				$shareLink=$permission['link']['webUrl']??null;
				break;
			}
		}

		return [
			StorageAttributes::ATTRIBUTE_PATH => ltrim($path,'\/'),
			StorageAttributes::ATTRIBUTE_LAST_MODIFIED => strtotime($response['lastModifiedDateTime']),
			StorageAttributes::ATTRIBUTE_FILE_SIZE => $response['size'],
			StorageAttributes::ATTRIBUTE_TYPE => isset($response['file']) ? 'file' : 'dir',
			StorageAttributes::ATTRIBUTE_MIME_TYPE => $response['file']['mimeType'] ?? null,
			StorageAttributes::ATTRIBUTE_VISIBILITY=>$visibility,
			'@id'=>$response['id']??null,
			'@link' => $response['webUrl'] ?? null,
			'@shareLink'=>$shareLink,
			'@downloadUrl' => $response['@microsoft.graph.downloadUrl']?? null,
		];
	}
	function getEndpoint($path='',$action='',$params=[]){
		$this->validatePath($path);
		$path=Path::clean($path);
		$path=trim($path,'\\/');
		$path=static::ROOT.':/'.$path;
		/**
		 * Path should not end with /
		 * /me/drive/root:/path/to/file
		 * /me/drive/root
		 */
		$path=rtrim($path,':/');
		if($action===true){//path reference
			if(strpos($path,':')===false) {
				$path .= ':';//root path should end with :
			}
		}
		if ($action && is_string($action)) {
			/**
			 * Append action to path
			 * /me/drive/root:/path:/action
			 * trim : for root
			 * /me/drive/root/action
			 */
			$path= rtrim($path,':');
			if(strpos($path,':')!==false) {
				$path .=':/' . $action;//root:/path:/action
			}else{
				$path .= '/' . $action;//root/action
			}
		}
		if($params){
			$path.='?'.http_build_query($params);
		}
		return $path;
	}

	/**
	 * @param $path
	 * @param $newPath
	 * @return array|null
	 * @throws GraphException
	 */
	public function copy($path,$newPath){
		$endpoint = $this->getEndpoint($path,'copy');
		$name=basename($newPath);
		$this->createDirectory(dirname($newPath));
		$newPathParent=$this->getEndpoint(dirname($newPath),true);
		$body=[
			'name' => $name,
			'parentReference' => [
				'path' => $newPathParent,
			],
		];
		return $this->createRequest('POST', $endpoint)
			->attachBody($body)
			->execute()->getBody();
	}

	/**
	 * @param $path
	 * @return array|null
	 * @throws GraphException
	 */
	public function createDirectory($path){
		$path=Path::clean($path);
		if($path==='/'){
			return $this->getItem('/');
		}
		$endpoint=$this->getEndpoint($path);
		return $this->createRequest('PATCH', $endpoint)
			->attachBody([
				'folder' => new ArrayObject(),
			])->execute()->getBody();
	}

	/**
	 * @param $path
	 * @return array|null
	 * @throws GraphException
	 */
	public function delete($path){
		$endpoint=$this->getEndpoint($path);
		return $this->createRequest('DELETE', $endpoint)->execute()->getBody();
	}

	/**
	 * @param $path
	 * @param null $format
	 * @return resource|null
	 * @throws GraphException
	 */
	public function download($path,$format=null){
		$args=[];
		if($format){
			if(is_string($format)){
				$args=['format'=>$format];
			}elseif(is_array($format)){
				$args=$format;
			}
		}
		$endpoint=$this->getEndpoint($path,'content',$args);
		$response=$this->createRequest('GET',$endpoint)->setReturnType('GuzzleHttp\Psr7\Stream')->execute();
		/**
		 * @var StreamWrapper $response
		 */
		return $response->detach();

	}

	/**
	 * @param $path
	 * @param array $args
	 * @return array|null
	 * @throws GraphException
	 */
	public function getItem($path,$args=[]){
		$endpoint=$this->getEndpoint($path,'',$args);
		$response = $this->createRequest('GET', $endpoint)->execute();
		return $response->getBody();
	}

	/**
	 * @param $path
	 * @return Generator
	 * @throws GraphException
	 */
	public function listChildren($path){
		$endpoint = $this->getEndpoint($path,'children');
		$nextPage=null;

		do {
			if ($nextPage) {
				$endpoint = $nextPage;
			}
			$response = $this->createRequest('GET', $endpoint)
				->execute();
			$nextPage = $response->getNextLink();
			$items = $response->getBody()['value']??[];
			if(!is_array($items)){
				$items=[];
			}
			yield from $items;
		}while($nextPage);
	}

	/**
	 * @param $path
	 * @param $newPath
	 * @return array|null
	 * @throws GraphException
	 */
	public function move($path,$newPath){
		$endpoint = $this->getEndpoint($path);
		$name=basename($newPath);
		$this->createDirectory(dirname($newPath));
		$newPathParent=$this->getEndpoint(dirname($newPath),true);
		$body=[
			'name' => $name,
			'parentReference' => [
				'path' => $newPathParent,
			],
		];
		return $this->createRequest('PATCH', $endpoint)
			->attachBody($body)
			->execute()->getBody();
	}

	/**
	 * @param $path
	 * @param $contents
	 * @return array|null
	 * @throws GraphException
	 */
	public function upload($path,$contents){
		$endpoint = $this->getEndpoint($path,'content');
		try {
			$stream = StreamWrapper::wrap($contents);
		}catch (InvalidArgumentException $e){
			throw new InvalidStreamProvided("Invalid contents. ".$e->getMessage());
		}

		$this->createDirectory(dirname($path));

		return $this->createRequest('PUT', $endpoint)
				->attachBody($stream)
				->execute()->getBody();
	}

    /**
     * @param $path
     * @return array|mixed
     * @throws GraphException
     */
	public function getPermissions($path){
		$endpoint=$this->getEndpoint($path,'permissions');
		$response = $this->createRequest('GET', $endpoint)->execute();
		return $response->getBody()['value']??[];
	}

    /**
     * @param $path
     * @return array
     * @throws GraphException
     */
	function publish($path){
		$endpoint=$this->getEndpoint($path,'createLink');
		$body=['type'=>'view','scope'=>'anonymous'];
		$response = $this->createRequest('POST', $endpoint)
			->attachBody($body)->execute();
		return $response->getBody();
	}

	/**
	 * @param $path
	 * @throws GraphException
	 */
	function unPublish($path){
		$permissions=$this->getPermissions($path);
		$idToRemove='';
		foreach ($permissions as $permission){
			if(in_array($this->publishPermission['role'],$permission['roles'])
				&& $permission['link']['scope']==$this->publishPermission['scope']){
				$idToRemove=$permission['id'];
				break;
			}
		}
		if(!$idToRemove){
			return ;
		}
		$endpoint=$this->getEndpoint($path,'permissions/'.$idToRemove);
		$this->createRequest('DELETE', $endpoint)->execute();
	}

	/**
	 * @param $requestType
	 * @param $endpoint
	 * @return GraphRequest
	 * @throws GraphException
	 */
	protected function createRequest($requestType, $endpoint){
		$this->logger->request($requestType,$endpoint);
		return $this->graph->createRequest($requestType,$endpoint);
	}
	protected function validatePath($path){
		$invalidChars=['"','*',':','<','>','?', '|'];
		foreach ($invalidChars as $char){
			if(strpos($path,$char)!==false){
				throw InvalidPathException::atLocation($path,$invalidChars);
			}
		}
	}
}
