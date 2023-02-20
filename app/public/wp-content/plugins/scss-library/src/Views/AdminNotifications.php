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

namespace ScssLibrary\Views;

/**
 * Class to manage the messages in the admin dashboard.
 */
class AdminNotifications
{
	/**
	 * Messages to be displayed in the upper part of the dashboard.
	 * Currently it shows a warning if the plugin is in development mode.
	 *
	 * @return void
	 */
	public static function notifications(): void
	{
		// Detect if development mode is active
		$options = get_option('scsslibrary');

		// If there is the get parameter that requests to deactivate 
		// the development mode, then deactivate it.
		if (isset($_GET['deactivate_scss_library_devmode'])) {
			$options['dev_mode'] = false;
			update_option('scsslibrary', $options);
		}

		// If the development mode is active, render the warning about the status
		// with a link to deactivate it.
		if (isset($options['dev_mode']) && $options['dev_mode'] === true) {
			// Current URL
			$url = parse_url($_SERVER['REQUEST_URI']);

			// Set the query appart and add the option to deactivate the development mode
			$query = [];
			if (isset($url['query'])) {
				parse_str($url['query'], $query);
			}
			$query['deactivate_scss_library_devmode'] = true;

			// Create the URL with the query to deactivate te development mode
			$url['query']                             = http_build_query($query);
			$url                                      = $url['path'] . '?' . $url['query'];

			// Text to be displayed in the notification area.
			$text = sprintf(__("The development mode from the <strong>SCSS-Library</strong> is active. Remember to <a href='%s'>deactivate it</a> in case this is a production environment.", 'scsslib'), $url);
			printf('<div class="error"><p>%s</p></div>', $text);
		}
	}
}
