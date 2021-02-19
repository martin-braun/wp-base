<?php if ( ! defined( 'ABSPATH' ) ) exit;

function console_log() {
	$encoded_args = [];
	foreach( func_get_args() as $arg ) {
		$encoded_args[] = is_array( $arg ) ? json_encode( $arg ) : '`' . print_r( $arg, true ) . '`';
	}
	$html = '<script type="text/javascript">console.log("[PHP]", ' . implode( ', ', $encoded_args ) . ');</script>';
	add_action( 'wp_enqueue_scripts', function() use( $html ) {
		echo $html;
	} );
	add_action( 'admin_enqueue_scripts', function() use( $html ) {
		echo $html;
	} );
	return $html;
}