<?php

use WBCR\Factory_Adverts_138\Base;

/**
 * Factory Adverts
 *
 * @author        Alexander Vitkalov <nechin.va@gmail.com>
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @since         1.0.0
 *
 * @package       factory-ad-inserter
 * @copyright (c) 2019, Webcraftic Ltd
 *
 * @version       1.2.4
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

if( defined('FACTORY_ADVERTS_138_LOADED') || (defined('FACTORY_ADVERTS_BLOCK') && FACTORY_ADVERTS_BLOCK) ) {
	return;
}

# Устанавливаем константу, что модуль уже загружен
define('FACTORY_ADVERTS_138_LOADED', true);

# Устанавливаем версию модуля
define('FACTORY_ADVERTS_138_VERSION', '1.3.8');

# Регистрируем тектовый домен, для интернализации интерфейса модуля
load_plugin_textdomain('wbcr_factory_adverts_138', false, dirname(plugin_basename(__FILE__)) . '/langs');

# Устанавливаем директорию модуля
define('FACTORY_ADVERTS_138_DIR', dirname(__FILE__));

# Устанавливаем url модуля
define('FACTORY_ADVERTS_138_URL', plugins_url(null, __FILE__));

require_once(FACTORY_ADVERTS_138_DIR . '/includes/class-rest-request.php');
require_once(FACTORY_ADVERTS_138_DIR . '/includes/class-base.php');

/**
 * @param Wbcr_Factory461_Plugin $plugin
 */
add_action('wbcr_factory_adverts_138_plugin_created', function ($plugin) {
	$plugin->set_adverts_manager("WBCR\Factory_Adverts_138\Base");
});
