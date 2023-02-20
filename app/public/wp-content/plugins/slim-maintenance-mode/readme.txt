=== Slim Maintenance Mode ===
Contributors: wpdoc.de
Tags: maintenance, unavailable, admin, maintenance mode, cache
Requires at least: 3.5
Tested up to: 6.1
Stable tag: trunk
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Simple and lightweight solution for scheduled maintenance. No settings page, just activate it and do your maintenance work stress-free.

== Description ==
Slim Maintenance Mode is a lightweight solution for scheduled maintenance. Simply activate the plugin and only administrators can see the website.

= Features =
* No extra settings, just activate it, do maintenance work, deactivate it.
* Alert message in the backend, when the plugin is active.
* Works with any theme.
* Support for the following cache plugins: Cachify, LiteSpeed Cache, Super Cache, WP Rocket, WP Fastest Cache and W3 Total Cache.
* Sends HTTP response status code `503 Service Unavailable`, especially relevant for search engines.

= Bug reports and Contributions =
Bug reports and other contributions are highly appreciated. Please open an issue in the [support forum](https://wordpress.org/support/plugin/slim-maintenance-mode).

== Installation ==
Install Slim Maintenance Mode like any other plugin directly from your plugins page.

Activate the plugin through the plugins page every time you need the maintenance mode and deactivate it as soon as work is done.

== Frequently Asked Questions ==
= Where are the settings of the plugin? =
There is no settings page. This plugin is designed to be small and simple.

= How can I change the content or layout of the maintenance page? =
This is not intended by design. Please use another solution if you want a custom maintenance page.

= How can I submit a new translation?  =
Please visit the [plugins page](https://translate.wordpress.org/projects/wp-plugins/slim-maintenance-mode) at WordPress Translate.

== Screenshots ==
1. Maintenance message for website visitors in English
2. Maintenance message for website visitors in German

== Changelog ==
= 1.4.3 =
* Fixed a bug that prevented logging into the administration area

= 1.4.2 =
* Fixed a bug with the HTTP response code

= 1.4.1 =
* Fixed a bug that prevented the plugin from working reliably with block themes

= 1.4 =
* Disable Web feeds (e.g. RSS, Atom) when the plugin is active

= 1.3.7 =
* Added support for the LiteSpeed Cache plugin

= 1.3.6 =
* Fix for a problem which occurs when DISALLOW_FILE_EDIT is set true.

= 1.3.5 =
* Added support for the WP Fastest Cache plugin

= 1.3.4 =
* Opening the plugin to WordPress Translate

= 1.3.3 =
* Added support for the WP-Rocket Cache plugin
* Changed the way to get directly to the plugin deactivation from the note in the backend

= 1.3.2 =
* Fixed a bug which caused problems in multisite environments

= 1.3.1 =
* Russian translation

= 1.3 =
* Fixed a bug which caused problems, when loading translated strings
* Brazilian Portuguese translation
* Fixed the FAQ

= 1.2 =
* French translation
* Spanish translation

= 1.1 =
* Several corrections and enhancements of the information texts
* Added two screenshots
* Polish translation

= 1.0 =
* Initial version
* Support for the following cache plugins: Cachify, Super Cache, W3 Total Cache
* German translation

== Upgrade Notice ==
= 1.4 =
Web feeds (e.g. RSS, Atom) are now disabled when the plugin is active.

= 1.3.7 =
Slim Maintenance Mode now supports the LiteSpeed Cache plugin.

= 1.3.6 =
Fix for a problem which occurs when DISALLOW_FILE_EDIT is set true.

= 1.3.5 =
Slim Maintenance Mode now supports the WP Fastest Cache plugin.

= 1.3.4 =
Opened Slim Maintenance Mode to the WordPress Translate community.

= 1.3.3 =
Slim Maintenance Mode now supports the WP-Rocket Cache plugin and provides a better way to get quicker to the plugin deactivation from the note in the backend.

= 1.3.1 =
Slim Maintenance Mode is now available in Russian.

= 1.3 =
Slim Maintenance Mode is now available in Brazilian Portuguese and comes with a bug fix for better loading translated strings.

= 1.2 =
Slim Maintenance Mode is now available in French and Spanish.
