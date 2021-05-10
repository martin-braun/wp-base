<?php if ( ! defined( 'ABSPATH' ) ) exit;

$namespace = 'auth/v1';

add_action( 'rest_api_init', function () use( $namespace ) {
	register_rest_route( $namespace, '/logout/', array(
		'methods'             => 'GET',
		'callback'            => function() {
			wp_logout();
			echo '1';
			exit;
		},
		'permission_callback' => '__return_true'
	) );
} );

add_action( 'rest_api_init', function () use( $namespace ) {
	register_rest_route( $namespace, '/logout_redirect/', array(
		'methods'             => 'GET',
		'callback'            => function() {
			wp_logout();
			wp_redirect(home_url());
			exit;
		},
		'permission_callback' => '__return_true'
	) );
} );