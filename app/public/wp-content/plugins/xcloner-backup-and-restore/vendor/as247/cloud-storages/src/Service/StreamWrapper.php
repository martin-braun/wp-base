<?php


namespace As247\CloudStorages\Service;

use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;


/**
 * Custom implement Psr7 Stream to fix AssemblyStream
 * Google require min chunk size: 262144 but assembly stream may have smaller chunk size
 * Eg NextCloud chunk size: 8192 and you will got following error
 * Invalid request. The number of bytes uploaded is required to be equal or greater than 262144, except for the final request (it's recommended to be the exact multiple of 262144). The received request contained 8192 bytes, which does not meet this requirement.
 * Class Stream
 * @package As247\CloudStorages\Service
 */
class StreamWrapper extends Stream
{
	protected $psr7Stream;
	public function __construct($stream, $options = [])
	{
		$this->psr7Stream=Utils::streamFor($stream,$options);
	}
	public static function wrap($stream,$options=[]){
		return new static($stream,$options);
	}


	public function __toString()
	{
		return $this->psr7Stream->__toString();
	}

	public function close()
	{
		$this->psr7Stream->close();
	}

	public function detach()
	{
		return $this->psr7Stream->detach();
	}

	public function getSize()
	{
		return $this->psr7Stream->getSize();
	}

	public function tell()
	{
		return $this->psr7Stream->tell();
	}

	public function eof()
	{
		return $this->psr7Stream->eof();
	}

	public function isSeekable()
	{
		return $this->psr7Stream->isSeekable();
	}

	public function seek($offset, $whence = SEEK_SET)
	{
		$this->psr7Stream->seek($offset,$whence);
	}

	public function rewind()
	{
		$this->psr7Stream->rewind();
	}

	public function isWritable()
	{
		return $this->psr7Stream->isWritable();
	}

	public function write($string)
	{
		return $this->psr7Stream->write($string);
	}

	public function isReadable()
	{
		return $this->psr7Stream->isReadable();
	}

	public function read($length)
	{
		$bytes=$this->psr7Stream->read($length);
		while(strlen($bytes)<$length && !$this->eof()){
			$bytes.=$this->psr7Stream->read($length);
		}
		return $bytes;
	}

	public function getContents()
	{
		return $this->psr7Stream->getContents();
	}

	public function getMetadata($key = null)
	{
		return $this->psr7Stream->getMetadata();
	}
}
