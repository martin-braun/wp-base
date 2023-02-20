<?php
/*
Plugin Name: SCSS-Library
Description: Adds support for SCSS stylesheets to wp_enqueue_style.
Author: Juan Sebastián Echeverry
Version: 0.4.1
Tested up to: 6.1
Requires PHP: 7.4
Text Domain: scsslib

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

// If we are using wp-cli, do not run the plugin
if (defined('WP_CLI')) {
	return;
}

use ScssLibrary\Init;
use ScssLibrary\Settings\ScssLibrary as Settings;

require_once('vendor/autoload.php');

// Activate plugin and settings
Init::get_instance();
Settings::get_instance();
