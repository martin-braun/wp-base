<?php


namespace As247\CloudStorages\Exception;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException implements FilesystemException
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
