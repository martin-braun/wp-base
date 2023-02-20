<?php
/*

Copyright 2019-2022 Juan Sebastián Echeverry (baxtian.echeverry@gmail.com)

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

namespace ScssLibrary;

use ScssLibrary\Libs\ScssLibrary;
use ScssLibrary\Views\Errors;
use ScssLibrary\Views\AdminNotifications;
use ScssLibrary\Views\AdminBar;

/**
 * Class to add scss style files directly with the wp_enqueue_style function.
 */
class Init
{
	use \Baxtian\SingletonTrait;

	/**
	 * Initializes the plugin
	 */
	protected function __construct($arguments = [])
	{
		// Setup plugin
		add_action('plugins_loaded', [$this, 'plugin_setup']);

		// Attend stile declarations
		add_filter('style_loader_src', [$this, 'style_loader_src'], 10, 2);

		// Display errors in footer if there are any.
		add_action('wp_footer', [Errors::class, 'wp_footer']);

		// Add messages at the top of the admin UI to warn
		// of any special status in the plugin.
		add_action('admin_notices', [AdminNotifications::class, 'notifications']);

		// Add a submenu to the admin bar with the actions of the plugin
		add_action('admin_bar_menu', [AdminBar::class, 'bar_menu'], 100);

		$classes = [
			'scss_library' => ScssLibrary::class,
		];

		$this->set_dependencies($arguments, $classes);
	}

	/**
	 * Actions to setup the plugin
	 *
	 * @return void
	 */
	public function plugin_setup(): void
	{
		// Activate translator
		load_plugin_textdomain('scsslib', false, basename(dirname(__FILE__, 2)) . '/languages/');
	}

	/**
	 * Filter all style files and compile if file is scss code. Returns the link
	 * to the original file or the link to the compiled version.
	 *
	 * @param string $src 		Link to the style file
	 * @param string $handle 	Handle of the style
	 * @return string 			Link to the style file
	 */
	public function style_loader_src(string $src, string $handle) : string {
		// Filter the file and get the same link if it is a css file
		// or the compiled one if it is a scss code.
		$scss_library = $this->dependency('scss_library');
		$src = $scss_library->style_loader_src($src, $handle);

		return $src;
	}

}
