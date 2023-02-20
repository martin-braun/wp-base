<?php


namespace As247\CloudStorages\Storage;

use As247\CloudStorages\Cache\PathCache;
use As247\CloudStorages\Contracts\Storage\StorageContract;
use As247\CloudStorages\Service\HasLogger;
use Closure;

abstract class Storage implements StorageContract
{
	/**
	 * @var PathCache
	 */
	protected $cache;
	use HasLogger;
	protected function setupCache($options){
		$cache=$options['cache']??null;
		if($cache instanceof Closure){
			$cache=$cache();
		}
		if(!$cache instanceof PathCache){
			$cache=new PathCache();
		}
		$this->setCache($cache);
	}
	public function setCache(PathCache $cache){
		$this->cache=$cache;
		return $this;
	}
	public function getCache(){
		return $this->cache;
	}
}
