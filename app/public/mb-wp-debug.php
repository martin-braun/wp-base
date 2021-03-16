<?php if ( ! defined( 'ABSPATH' ) ) exit;

function console_log() {
	$encoded_args = [];
	foreach( func_get_args() as $arg ) {
		$encoded_args[] = is_array( $arg ) ? json_encode( $arg ) : '`' . print_r( $arg, true ) . '`';
	}
	$msg = implode( ', ', $encoded_args );
	$html = '<script type="text/javascript">console.log("[PHP]", ' . $msg . ');</script>';
	add_action( 'wp_enqueue_scripts', function() use( $html ) {
		echo $html;
	} );
	add_action( 'admin_enqueue_scripts', function() use( $html ) {
		echo $html;
	} );
	error_log('[CONSOLE] ' . $msg);
	return $html;
}