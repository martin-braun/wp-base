<?php if ( ! defined('ABSPATH') ) exit;

/**
 * Disable Google Fonts in Elementor.
 */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/**
 * Disable Google Fonts in Redux.
 */
add_action( 'redux/loaded', function( $redux ) {
	$redux->args['disable_google_fonts_link'] = true;
} );