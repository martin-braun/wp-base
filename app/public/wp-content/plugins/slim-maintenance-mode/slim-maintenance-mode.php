<?php
/**
 * Plugin Name: Slim Maintenance Mode
 * Plugin URI: https://wpdoc.de/plugins/
 * Description: A lightweight solution for scheduled maintenance. Simply activate the plugin and only administrators can see the website.
 * Version: 1.4.3
 * Author: Johannes Ries
 * Author URI: https://wpdoc.de
 * Text Domain: slim-maintenance-mode
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Avoid direct calls
*/
defined( 'ABSPATH' ) || exit;

/*
 * Require plugin.php
 */
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

/**
 * Activation and deactivation with Cache Support
*/
function slim_maintenance_mode_on_activation()  {
  if ( !current_user_can( 'activate_plugins' ) )
  return;
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  check_admin_referer( "activate-plugin_{$plugin}" );

    // Clear Cachify Cache
    if ( has_action('cachify_flush_cache') ) {
    do_action('cachify_flush_cache');
    }

    // Clear LiteSpeed Cache
    if ( has_action('litespeed_purge_all') ) {
    do_action( 'litespeed_purge_all' );
    }

    // Clear Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
    ob_end_clean();
    wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    ob_end_clean();
    w3tc_pgcache_flush();
    }

    // Clear WP-Rocket Cache
    if ( function_exists( 'rocket_clean_domain' ) ) {
    rocket_clean_domain();
    }

    // Clear WP Fastest Cache
    if ( isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache') ) {
    $GLOBALS['wp_fastest_cache']->deleteCache();
    }
}

function slim_maintenance_mode_on_deactivation() {
  if ( !current_user_can( 'activate_plugins' ) )
  return;
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  check_admin_referer( "deactivate-plugin_{$plugin}" );

    // Clear Cachify Cache
    if ( has_action('cachify_flush_cache') ) {
    do_action('cachify_flush_cache');
    }

    // Clear LiteSpeed Cache
    if ( has_action('litespeed_purge_all') ) {
    do_action( 'litespeed_purge_all' );
    }

    // Clear Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
    ob_end_clean();
    wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
    ob_end_clean();
    w3tc_pgcache_flush();
    }

    // Clear WP-Rocket Cache
    if ( function_exists( 'rocket_clean_domain' ) ) {
    rocket_clean_domain();
    }

    // Clear WP Fastest Cache
    if ( isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache') ) {
    $GLOBALS['wp_fastest_cache']->deleteCache();
    }
}

register_activation_hook(   __FILE__, 'slim_maintenance_mode_on_activation' );
register_deactivation_hook( __FILE__, 'slim_maintenance_mode_on_deactivation' );

/**
 * Localization
*/
load_plugin_textdomain( 'slim-maintenance-mode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Alert message when active
*/
function slim_maintenance_mode_admin_notices() {
	echo '<div id="message" class="error fade"><p>' . __( '<strong>Maintenance mode</strong> is <strong>active</strong>!', 'slim-maintenance-mode' ) . ' <a href="plugins.php?s=Slim Maintenance Mode&plugin_status=all">' . __( 'Deactivate it, when work is done.', 'slim-maintenance-mode' ) . '</a></p></div>';
}
if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
add_action( 'network_admin_notices', 'slim_maintenance_mode_admin_notices' );
add_action( 'admin_notices', 'slim_maintenance_mode_admin_notices' );
add_filter( 'login_message',
	function() {
		return '<div id="login_error">' . __( '<strong>Maintenance mode</strong> is <strong>active</strong>!', 'slim-maintenance-mode' ) . '</div>';
	} );

/**
 * Maintenance message when active
*/
function slim_maintenance_mode()
{
  nocache_headers();
  if ( !current_user_can('activate_plugins') || !is_user_logged_in() ) {
  wp_die( '<h1>' . __( 'Maintenance', 'slim-maintenance-mode' ) . '</h1><p>' . __( 'Please check back soon.', 'slim-maintenance-mode' ) . '</p>', __( 'Maintenance', 'slim-maintenance-mode' ), array('response' => '503'));
  }
}
add_action('parse_request', 'slim_maintenance_mode');

/**
 * Deactivate feeds when plugin is active
*/
function slim_maintenance_mode_disable_feed() {
  wp_die( __( 'Maintenance', 'slim-maintenance-mode' ) . '. ' .__( 'Please check back soon.', 'slim-maintenance-mode' ), __( 'Maintenance', 'slim-maintenance-mode' ), array('response' => '503') );
}
add_action('do_feed', 'slim_maintenance_mode_disable_feed', 1);
add_action('do_feed_rdf', 'slim_maintenance_mode_disable_feed', 1);
add_action('do_feed_rss', 'slim_maintenance_mode_disable_feed', 1);
add_action('do_feed_rss2', 'slim_maintenance_mode_disable_feed', 1);
add_action('do_feed_atom', 'slim_maintenance_mode_disable_feed', 1);
add_action('do_feed_rss2_comments', 'slim_maintenance_mode_disable_feed', 1);
add_action('do_feed_atom_comments', 'slim_maintenance_mode_disable_feed', 1);

?>
