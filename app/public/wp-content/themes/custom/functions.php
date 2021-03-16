<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * COMMON CUSTOMIZATION
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); // load parent style
} );
require_once 'custom-shortcodes.php'; // add shortcodes

/**
 * ADMIN CUSTOMIZATION
 */
if ( is_user_logged_in() && current_user_can( 'administrator' ) ) { // current user is admin?
	add_action('admin_print_scripts', function() { // add admin scripts
		echo '<script async="" defer="" src="' . get_stylesheet_directory_uri() . '/assets/js/custom-admin.js"></script>';
	});
}

/**
 * FRONTEND CUSTOMIZATION
 */
require_once 'gdpr/gdpr-google-fonts-blocker.php'; // block Google Fonts everywhere
require_once 'custom-menu.php'; // modify nav menu
add_action( 'wp_footer', function() { // add custom scripts
	if ( is_plugin_active( 'scss-library/scss-library.php' ) ) { // scss_library active?
		wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() .'/assets/scss/_index.scss' );
	}
	echo '<script async="" defer="" src="' . get_stylesheet_directory_uri() . '/assets/js/custom-theme.js"></script>';
} );

/**
 * REST ENDPOINT CUSTOMIZATION
 */
require_once 'rest/custom-rest-auth.php'; // add logout API endpoint 