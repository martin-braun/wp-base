<?php
/**
 * Factory Bootstrap
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>
 * @since         1.0.0
 * @package       factory-bootstrap
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

// module provides function only for the admin area
if( !is_admin() ) {
	return;
}

if( defined('FACTORY_BOOTSTRAP_462_LOADED') ) {
	return;
}

define('FACTORY_BOOTSTRAP_462_VERSION', '4.6.2');
define('FACTORY_BOOTSTRAP_462_LOADED', true);

if( !defined('FACTORY_FLAT_ADMIN') ) {
	define('FACTORY_FLAT_ADMIN', true);
}

define('FACTORY_BOOTSTRAP_462_DIR', dirname(__FILE__));
define('FACTORY_BOOTSTRAP_462_URL', plugins_url(null, __FILE__));

require_once(FACTORY_BOOTSTRAP_462_DIR . '/includes/functions.php');

/**
 * @param Wbcr_Factory461_Plugin $plugin
 */
add_action('wbcr_factory_bootstrap_462_plugin_created', function ($plugin) {
	$manager = new Wbcr_FactoryBootstrap462_Manager($plugin);
	$plugin->setBootstap($manager);
});


