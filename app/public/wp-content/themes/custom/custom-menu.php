<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adjust navigation menu elements.
 */
add_filter( 'wp_nav_menu_objects', function( $items ) {
	$isLoggedIn = is_user_logged_in();

	foreach ( $items as $key => $item ) {

		/**
		 * Convert sign-in link to sign-out link and hide user links, when logged out.
		 */
		$sign_out_url = '/wp-login.php?action=logout&redirect_to=%2F&_wpnonce=' . wp_create_nonce( 'log-out' );
		if ( $isLoggedIn && in_array( 'sign-link', $item->classes ) ) {
			switch($item->title) {
				// case 'Anmelden': $item->title = 'Abmelden'; break;
				// case 'Einloggen': $item->title = 'Ausloggen'; break;
				case 'Sign In': $item->title = 'Sign Out'; break;
				default: $item->title = 'Logout';
			}
			$item->url = $sign_out_url;
		}	elseif (!$isLoggedIn && in_array( 'user-link', $item->classes ) ) {
			unset($items[$key]);
		}

	}
	return $items;
} );