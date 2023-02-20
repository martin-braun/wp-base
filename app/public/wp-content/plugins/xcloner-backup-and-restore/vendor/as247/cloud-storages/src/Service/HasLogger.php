<?php


namespace As247\CloudStorages\Service;


trait HasLogger
{
    protected $logger;
    public function getLogger(){
        return $this->logger;
    }
    public function setLogger($logger){
        $this->logger=$logger;
        return $this;
    }
    protected function setupLogger($options){
    	$dir=__DIR__.'/../../';
    	$log=$options['log']??false;
    	if(is_string($log)){
			$dir=$log;
			$log=true;
		}
    	$dir=$options['log.dir']??$dir;
		$this->logger=new Logger($dir);
    	$this->logger->enable($log);
	}
}
