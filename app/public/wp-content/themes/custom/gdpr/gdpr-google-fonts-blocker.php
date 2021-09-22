<?php if ( ! defined( 'ABSPATH' ) ) exit;

/* PLUGIN LIBRARIES */

/**
 * Disable Google Fonts in Redux.
 */
add_action( 'redux/loaded', function( $redux ) {
    $redux->args['disable_google_fonts_link'] = true;
} );

/* PLUGINS */

/* PAGE BUILDERS */

/**
* Disable Google Fonts in Elementor.
*/
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

///**
// * Disable Google Fonts in Visual Composer.
// */
//add_filter( 'vc_google_fonts_get_fonts_filter', '__return_empty_array' ); // won't work

/* THEMES */

// /**
//  * Disable Google Fonts in Porto.
//  */
// add_action( 'wp_print_styles', function() {
//     wp_deregister_style( 'porto-customize-fonts' );
//     wp_deregister_style( 'porto-admin-fonts' );
//     wp_deregister_style( 'porto-builder-fonts' );
//     wp_deregister_style( 'porto-vc-editor-fonts' );
//     wp_deregister_style( 'porto-vc-front-editor-fonts' );
//     wp_deregister_style( 'porto-wizard-fonts' );
//     wp_deregister_style( 'porto-speed-optimize-fonts' );
//     wp_deregister_style( 'porto-speed-optimize' );
//     wp_deregister_style( 'porto-builder-fonts' );
// }, 1001 );

/* SPECULATIVE */

/**
 * Disable Google Fonts in Maintenance.
 */
add_action( 'wp_print_styles', function() {
    wp_deregister_style( 'arvo' );
}, 1001 );

/**
 * Disable Google Fonts in RevSlider.
 */
add_action( 'wp_print_styles', function() {
    wp_deregister_style( 'rs-roboto' );
    wp_deregister_style( 'rs-open-sans' );
    wp_deregister_style( 'tp-material-icons' );
    // t.b.d.
}, 1001 );

/**
 * Disable Google Fonts in WooCommerce Product Filter.
 */
add_action( 'wp_print_styles', function() {
    wp_deregister_style( 'open_sans_font' );
    wp_deregister_style( 'rs-open-sans' );
    wp_deregister_style( 'tp-material-icons' );
}, 1001 );

/**
 * Disable Google Fonts in YITH.
 */
add_action( 'wp_print_styles', function() {
    wp_deregister_style( 'yith-wcan-material-icons' );
    wp_deregister_style( 'yith-wcwl-material-icons' );
    wp_deregister_style( 'raleway-font' );
}, 1001 );

