<?php
/*

Copyright 2022 Juan Sebastián Echeverry (baxtian.echeverry@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

namespace ScssLibrary\Libs;

/**
 * Class to manage the errors list.
 */
class Errors
{
	use \Baxtian\SingletonTrait;

	public const DIRECTORY_CANNOT_CREATE     = 101;
	public const DIRECTORY_PERMISSION_DENIED = 102;
	public const SOURCE_NOT_FOUND            = 201;

	// Array to hold any error message
	protected $errors = [];

	/**
	 * Magic method to get the private variables.
	 *
	 * @param string $name			Name of the variable to get
	 * @return boolean|array|string	Value of the variable
	 */
	public function __get(string $name)
	{
		$answer = null;
		switch ($name) {
			case 'errors':
				$answer = $this->$name;

				break;
			default:
				$answer = null;
		}

		return $answer;
	}

	/**
	 * Enqueue an error in the list
	 *
	 * @param string $handle	Handle assigned to the file
	 * @param string $file		Scss source filename
	 * @param string $message	Error message
	 * @param int    $code		Error code
	 * @return void
	 */
	public function enqueue(string $handle, string $file, string $message, int $code = 0): void
	{
		array_push($this->errors, [
			'handle'  => $handle,
			'file'    => $file,
			'message' => $message,
			'code'    => $code,
		]);
	}
}
