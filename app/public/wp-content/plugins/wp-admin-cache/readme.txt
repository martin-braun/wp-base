=== WP Admin Cache ===
Contributors: grfstudio
Tags: admin cache, admin performance, admin speed, slow admin, woocommerce performance, slow woocommerce
Stable tag: 0.2.7
Requires PHP: 5.6
Requires at least: 4.6
Tested up to: 5.4
License: GPLv2 or later

The first cache plugin for the WordPress admin area.

== Description ==

This lightweight plugin caches the most visited pages in the admin area; it uses ajax to prefetch the pages and stores them on the server for each user.

The cache is useful for sites with many installed plugins, which often make the administrative section very slow, especially in the case of woocommerce with many displayed products.


== Installation ==

= Minimum Requirements =

* WordPress 4.6 or greater
* PHP version 5.6 or greater

= Recommended requirements: =

* WordPress 5.0 or greater
* PHP version 7.0 or greater


= Installation =

1. Upload the plugin files to the `/wp-content/plugins/wp-admin-cache` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings > WP Admin Cache
4. Check "Cache enabled" to activate the cache.
5. Choose which pages are to be cached.
6. Set the cache duration.
7. Save the settings.


== Changelog ==

= 0.2.7 =

* Fixed: some php warnings

= 0.2.6 =

* Removed: link manager from page hook

= 0.2.5 =

* Added: compatibility with generic language plugins

= 0.2.4 =

* Added: WPML compatibility

= 0.2.3 =

* Changed prefetching script: optimized page load

= 0.2.2 =

* Changed settings link in plugins list table

= 0.2.1 =

* Added settings link in plugins list table

= 0.2.0 =

* Added the possibility to execute the plugin before the others, in order to speed up the sending of cached pages much more!
* Improved prefetching mechanism
* Improved UI

= 0.1.3 =

* Added cleanup after uninstalling

= 0.1.2 =

* Added autopurge management at plugins update event

= 0.1.1 =

* Fix - fixed a bug that generated a blank page after saving a cached page

= 0.1 =

* First public beta release




