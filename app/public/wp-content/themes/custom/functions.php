<?php if ( ! defined( 'ABSPATH' ) ) exit;

$child_theme_dist_version = '20211110_021122';

/**
 * COMMON CUSTOMIZATION
 */
add_action( 'wp_enqueue_scripts', function() {
	global $child_theme_dist_version;
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css?v=' . $child_theme_dist_version ); // load parent style
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() .'/style.css?v=' . $child_theme_dist_version ); // load child css
	if ( defined( 'WP_LOCAL' ) && WP_LOCAL ) {
		wp_enqueue_style( 'local-wp-style', get_stylesheet_directory_uri() .'/local-wp.css?v=' . $child_theme_dist_version ); // load local-wp css
	}
} );
require_once 'custom-shortcodes.php'; // add shortcodes

/**
 * ADMIN CUSTOMIZATION
 */
if ( is_user_logged_in() && current_user_can( 'administrator' ) ) { // current user is admin?
	add_action('admin_print_scripts', function() { // add admin scripts
		global $child_theme_dist_version;
		echo '<script async="" defer="" type="text/javascript" src="' . get_stylesheet_directory_uri() . '/assets/js/custom-admin.js?v=' . $child_theme_dist_version . '"></script>';
		if ( defined( 'WP_LOCAL' ) && WP_LOCAL ) {
			echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/local-wp-admin.css?v=' . $child_theme_dist_version . '" />';
		}
	});
}

/**
 * FRONTEND CUSTOMIZATION
 */
require_once 'gdpr/gdpr-google-fonts-blocker.php'; // block Google Fonts everywhere
require_once 'custom-menu.php'; // modify nav menu
add_action( 'wp_footer', function() { // add custom scripts
	global $child_theme_dist_version;
	if ( is_plugin_active( 'scss-library/scss-library.php' ) ) { // scss_library active?
		wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() .'/assets/scss/_index.scss' );
	}
	echo '<script async="" defer="" src="' . get_stylesheet_directory_uri() . '/assets/js/custom-theme.js?v=' . $child_theme_dist_version . '"></script>';
} );

/**
 * REST ENDPOINT CUSTOMIZATION
 */
require_once 'rest/rest-auth-v1.php'; // add logout API endpoint