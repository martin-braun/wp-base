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
class DevMode
{
	use \Baxtian\SingletonTrait;

	// protected function __construct($arguments = [])
	// {
	// 	// Dependencies
	// 	$classes = [
	// 		'errors'   => Errors::class,
	// 	];
	// 	$this->set_dependencies($arguments, $classes);
	// }

	/**
	 * Development mode is active if plugin settings is checked, or WP_DEBUG
	 * is active, or the URL has the argument to activate the development mode.
	 * Development mode is forced to false if the URL has the argument to
	 * deactivate the development mode.
	 *
	 * @return boolean Returns the development mode status.
	 */
	public function is_active(): bool
	{

		// Set dev mode based on the plugin settings
		$opciones = get_option('scsslibrary');
		$dev_mode = (isset($opciones['dev_mode']) && $opciones['dev_mode']) ? true : false;

		// If WP_DEBUG is defined and true
		$dev_mode = (defined('WP_DEBUG') && WP_DEBUG === true) ? true : $dev_mode;

		// If activate_scss_library_devmode is set in the URL
		$dev_mode = (isset($_GET['activate_scss_library_devmode']) && $_GET['activate_scss_library_devmode'] == 1) ? true : $dev_mode;

		// If deactivate_scss_library_devmode is set in the URL disable dev_mode, we are now out of dev_mode
		$dev_mode = (isset($_GET['deactivate_scss_library_devmode']) && $_GET['deactivate_scss_library_devmode'] == 1) ? false : $dev_mode;

		return $dev_mode;
	}

	/**
	 * Detect if an action has been required in the URL.
	 *  Does the URL have the variable recompile_scss_files? (Requesting to recompile)
	 *  Does the URL have the variable activate_scss_library_devmode? (changing to development mode)
	 *  Does the URL have the variable deactivate_scss_library_devmode? This way to force to minify and remove map files
	 *
	 * @return bool
	 */
	public function required_action(): bool
	{
		$ans = (
			(isset($_GET['recompile_scss_files'])) || // Recompile the files
			(isset($_GET['activate_scss_library_devmode'])) || // Activating the devmode
			(isset($_GET['deactivate_scss_library_devmode'])) // Deactivatint¿g the devmode, request to remove maps
		);

		return $ans;
	}

	/**
	 * Set the dev mode and return the new options array
	 *
	 * @param bool $mode	The new mode
	 * @return array
	 */
	private function set_mode(bool $mode) : array {
		$options = get_option('scsslibrary');

		$options['dev_mode'] = $mode;
		update_option('scsslibrary', $options);

		return $options;
	}

	/**
	 * Activate the dev mode and return the new options array
	 *
	 * @return array
	 */
	public function activate() : array {
		return $this->set_mode(true);
	}

	/**
	 * Dectivate the dev mode and return the new options array
	 *
	 * @return array
	 */
	public function deactivate() : array {
		return $this->set_mode(false);
	}


}
