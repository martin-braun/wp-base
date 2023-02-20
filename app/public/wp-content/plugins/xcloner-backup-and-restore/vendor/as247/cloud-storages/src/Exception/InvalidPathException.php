<?php


namespace As247\CloudStorages\Exception;


use RuntimeException;
use Throwable;

class InvalidPathException extends RuntimeException
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
	public static function atLocation($path, $notAllowed){
		$notAllowed=is_array($notAllowed)?implode(' ',$notAllowed):$notAllowed;
		$messages="Path [$path] contains not allowed characters: $notAllowed";
		return new static($messages);
	}
}
