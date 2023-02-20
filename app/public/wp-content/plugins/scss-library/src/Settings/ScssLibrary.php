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

namespace ScssLibrary\Settings;

/**
 * Components and controls for the settings panel 
 * of the ScssLibrary plugin.
 */
class ScssLibrary
{
	use \Baxtian\SingletonTrait;

	/**
	 * Initializes the control panel.
	 */
	protected function __construct()
	{
		add_action('customize_register', [$this, 'options']);
	}

	/**
	 * Add the panel to the administration and customization screen
	 * @param  WP_Customize_Manager $wp_customize Instance of the customizer controller
	 */
	public function options($wp_customize)
	{
		// Add ScssLibrary section
		$wp_customize->add_section(
			'scsslibrary',
			[
				'title' => __('SCSS Compiler', 'scsslib'),
			]
		);

		// Declare field for development mode
		$wp_customize->add_setting(
			'scsslibrary[dev_mode]',
			[
				'type' => 'option', // or 'theme_mod'
			]
		);

		// Set the checkbox field for the development mode
		$wp_customize->add_control(
			'scsslibrary[dev_mode]',
			[
				'label'       => __('Developer mode', 'scsslib'),
				'description' => __('Enable this option if you want to always compile the files. This is helpful while developing but remember to disable it when in production.', 'scsslib'),
				'section'     => 'scsslibrary',
				'settings'    => 'scsslibrary[dev_mode]',
				'type'        => 'checkbox',
			]
		);
	}
}
