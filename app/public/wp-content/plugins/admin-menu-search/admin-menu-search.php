<?php
/*
Plugin Name: Admin Menu Search
Plugin URI: https://herchen.com/admin-menu-search/
Description: Adds a search box to the top of the WordPress Admin Menu. Allows you to easily locate admin menu functions for sites with large amounts of menus or menu items. 
Version: 1.2
Author: Michael Herchenroder
Author URI: http://herchen.com
*/

add_action('admin_enqueue_scripts', 'fhqwhgads_load_scripts');
function fhqwhgads_load_scripts() {
	wp_enqueue_script( 'admin-menu-search', plugin_dir_url( __FILE__ )  . "/admin-menu-search.js", array(), '1.2', true );
}


if ( is_admin() ) {
	add_filter( 'plugin_row_meta', 'fhqwhgads_donate_link', 10, 2 );
}

//Come on, Fhqwhgads...
function fhqwhgads_donate_link( $links, $file ) {
	if ( $file == 'admin-menu-search/admin-menu-search.php' ) {
		$donation_url  = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8NJPQ6RHLT5HN&source=url';
		$donation_url .= urlencode( sprintf( __( 'Donation for Admin Menu Search plugin: %s', 'admin-menu-search' ), "Admin Menu Search" ) );
		$links[] = '<a href="' . esc_url( $donation_url ) . '" target="_blank">' . __( 'Donate', 'admin-menu-search' ) . '</a>';
	}
	return $links;
}