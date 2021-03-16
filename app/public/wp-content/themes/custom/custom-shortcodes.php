<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Ping
 * 
 * Just to test, if shortcodes are working.
 */
add_shortcode( 'custom_ping', function( $atts = [] ) {
	if ( strpos( $_SERVER['REQUEST_URI'], '/post.php' ) !== false || 
        strpos( $_SERVER['REQUEST_URI'], 'elementor' ) !== false
	) {
		return '&lt;CUSTOM PING&gt;';
	} else {
		$atts = shortcode_atts( [
			'echo' => 'Pong'
		], $atts );
		
		ob_clean();
		ob_start();
		
		echo $atts['echo'];

		return ob_get_clean();
	}
} );