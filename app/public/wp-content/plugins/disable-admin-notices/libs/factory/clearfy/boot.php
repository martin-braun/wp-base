<?php
/**
 * Factory clearfy
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @since         1.0.0
 * @package       clearfy
 * @copyright (c) 2018, Webcraftic Ltd
 *
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

if( defined('FACTORY_CLEARFY_234_LOADED') ) {
	return;
}

define('FACTORY_CLEARFY_234_LOADED', true);

define('FACTORY_CLEARFY_234', '2.3.4');

define('FACTORY_CLEARFY_234_DIR', dirname(__FILE__));
define('FACTORY_CLEARFY_234_URL', plugins_url(null, __FILE__));

load_plugin_textdomain('wbcr_factory_clearfy_234', false, dirname(plugin_basename(__FILE__)) . '/langs');

require(FACTORY_CLEARFY_234_DIR . '/includes/ajax-handlers.php');
require(FACTORY_CLEARFY_234_DIR . '/includes/class-helpers.php');
require(FACTORY_CLEARFY_234_DIR . '/includes/class-configurate.php');

// module provides function only for the admin area
if( is_admin() ) {
	/**
	 * Подключаем скрипты для установки компонентов Clearfy
	 * на все страницы админпанели.
	 */
	add_action('admin_enqueue_scripts', function ($hook) {
		wp_enqueue_script('wbcr-factory-clearfy-234-global', FACTORY_CLEARFY_234_URL . '/assets/js/clearfy-globals.js', [
			'jquery',
			'wfactory-443-core-general'
		], FACTORY_CLEARFY_234);

		require_once FACTORY_CLEARFY_234_DIR . '/includes/class-search-options.php';
		$all_options = \WBCR\Factory_Clearfy_234\Search_Options::get_all_options();

		if( empty($all_options) ) {
			return;
		}

		$allow_print_data = false;
		$formated_options = [];

		foreach($all_options as $option) {
			if( !$allow_print_data && isset($_GET['page']) && $option['page_id'] === $_GET['page'] ) {
				$allow_print_data = true;
			}

			$formated_options[] = [
				'value' => $option['title'],
				'data' => [
					//'hint' => isset($option['hint']) ? $option['hint'] : '',
					'page_url' => $option['page_url'],
					'page_id' => $option['page_id']
				]
			];
		}

		if( !$allow_print_data ) {
			return;
		}

		wp_localize_script('wbcr-factory-clearfy-234-global', 'wfactory_clearfy_search_options', $formated_options);
	});

	if( defined('FACTORY_PAGES_442_LOADED') ) {
		require(FACTORY_CLEARFY_234_DIR . '/pages/class-pages.php');
		require(FACTORY_CLEARFY_234_DIR . '/pages/class-page-more-features.php');
		require(FACTORY_CLEARFY_234_DIR . '/pages/class-page-license.php');
		require(FACTORY_CLEARFY_234_DIR . '/pages/class-pages-components.php');

		require(FACTORY_CLEARFY_234_DIR . '/pages/setup-parts/class-step.php');
		require(FACTORY_CLEARFY_234_DIR . '/pages/setup-parts/class-step-form.php');
		require(FACTORY_CLEARFY_234_DIR . '/pages/setup-parts/class-step-custom.php');
		require(FACTORY_CLEARFY_234_DIR . '/pages/class-page-setup.php');
	}
}