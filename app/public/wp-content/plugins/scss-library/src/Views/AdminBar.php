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
 * Class to manage the submenu in the admin bar.
 */
class AdminBar
{
	/**
	 * Include a submenu into the administration bar with
	 * actions to manage the development mode
	 *
	 * @param Object $admin_bar
	 * @return void
	 */
	public static function bar_menu($admin_bar): void
	{
		// Only users with permissions to edit topics
		// can have access to these actions.
		if (current_user_can('edit_theme_options')) {

			// Get plugin settings
			$options = get_option('scsslibrary');

			// If there is the get parameter that requests to activate
			// the development mode, then activate it.
			if (isset($_GET['activate_scss_library_devmode'])) {
				$options['dev_mode'] = true;

				// Save plugin settings
				update_option('scsslibrary', $options);
			}

			// If there is the get parameter that requests to deactivate
			// the development mode, then deactivate it.
			if (isset($_GET['deactivate_scss_library_devmode'])) {
				$options['dev_mode'] = false;

				// Save plugin settings
				update_option('scsslibrary', $options);
			}

			// Add the "SCSS Library" item to the admin menu
			// Set class depending if the development mode is active or not. 
			$admin_bar->add_menu([
				'id'    => 'scss-library',
				'title' => __('SCSS Library', 'scsslib'),
				'href'  => '#',
				'meta'  => [
					'class' => (isset($options['dev_mode']) && $options['dev_mode']) ? 'sl-alert' : '',
					'html'  => '<style>#wpadminbar .menupop.sl-alert > a.ab-item { color: white; background: #9c3e3d; }</style>',
				],
			]);

			// Get the URL
			$url = parse_url($_SERVER['REQUEST_URI']);

			// Get the query part of the URL
			$query = [];
			if (isset($url['query'])) {
				parse_str($url['query'], $query);
			}

			// Actions to be added into the query
			$query1['recompile_scss_files']            = true;
			$query2['deactivate_scss_library_devmode'] = true;
			$query3['activate_scss_library_devmode']   = true;

			// Sub item to recompile outside the admin dashboard
			if (!is_admin()) {
				$admin_bar->add_menu([
					'id'     => 'clear-scss',
					'parent' => 'scss-library',
					'title'  => __('Recompile SCSS files', 'scsslib'),
					'href'   => $url['path'] . '?' . http_build_query($query1),
				]);
			}

			// Action to toggle development mode
			if (isset($options['dev_mode']) && $options['dev_mode']) {
				// To deactivate the development mode
				$attr = [
					'id'     => 'deactivate-scss-devmode',
					'parent' => 'scss-library',
					'title'  => __('Deactivate development mode', 'scsslib'),
					'href'   => $url['path'] . '?' . http_build_query($query2),
					'meta'   => [
						'class' => 'sl-active',
						'html'  => '<style>#wpadminbar .ab-submenu .sl-active > a.ab-item { color: white; background: #9c3e3d; }</style>',
					],
				];
				$admin_bar->add_menu($attr);
			} else {
				// To activate the development mode
				$admin_bar->add_menu([
					'id'     => 'activate-scss-devmode',
					'parent' => 'scss-library',
					'title'  => __('Activate development mode', 'scsslib'),
					'href'   => $url['path'] . '?' . http_build_query($query3),
				]);
			}
		}
	}
}
