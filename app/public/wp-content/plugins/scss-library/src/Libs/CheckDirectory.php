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

use ScssLibrary\Libs\Errors;

/**
 * Class to filter a style file and create the variables to compile if required.
 */
class CheckDirectory
{
	use \Baxtian\SingletonTrait;

	protected function __construct($arguments = [])
	{
		// Dependencies
		$classes = [
			'errors'   => Errors::class,
		];
		$this->set_dependencies($arguments, $classes);
	}

	/**
	 * Test whether the directory where the compiled files will be stored is writable.
	 * 
	 * @param string $dir	Directory to test
	 * @return bool Indicates whether or not the directory exists and is writable.
	 */
	public function check(string $dir): bool
	{
		// Detects if the directory exists.
		if (is_dir($dir) === false) {
			// If the directory does not exist, create it.
			if (wp_mkdir_p($dir) === false) {
				// If the directory could not be created, add the error message.
				$this->dependency('errors')->enqueue(
					__('SCSS Library', 'scsslib'),
					__('Cache directory.', 'scsslib'),
					__('File Permissions Error, unable to create cache directory. Please make sure the Wordpress Uploads directory is writable.', 'scsslib'),
					Errors::DIRECTORY_CANNOT_CREATE
				);

				// Indicate the problem with the directory
				return false;
			}
		}

		// Check that the directory where the compiled files will be stored
		// has write permissions
		if (is_writable($dir) === false) {
			$this->dependency('errors')->enqueue(
				__('SCSS Library', 'scsslib'),
				__('Cache directory.', 'scsslib'),
				sprintf(__('File Permissions Error, permission denied. Please make %s writable.', 'scsslib'), $dir),
				Errors::DIRECTORY_PERMISSION_DENIED
			);

			// Indicate the problem with the directory
			return false;
		}

		// Looks like we can wite in the directory
		return true;
	}
}
