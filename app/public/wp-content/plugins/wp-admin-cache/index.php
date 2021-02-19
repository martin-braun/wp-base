<?php

/*
  Plugin Name: WP Admin Cache
  Plugin URI: https://www.wpadmincache.com
  Description: The first cache plugin for WordPress admin area
  Version: 0.2.7
  Author: Grf Studio
  Author URI: https://www.grfstudio.com
  Text Domain: wp-admin-cache
  Domain Path: /languages/
  License:
 */

if (!function_exists('add_action')) {
    exit;
}

/* register_activation_hook(__FILE__, 'wp_admin_cache_activated');

  function wp_admin_cache_activated() {
  AdminCache::movePluginAtTop();
  } */

function detect_plugin_activation($plugin, $network_activation) {
    if ($plugin == 'wp-admin-cache/index.php') AdminCache::movePluginAtTop();
}

add_action('activated_plugin', 'detect_plugin_activation', 10, 2);

class AdminCache {

    private $settings;
    private $beginStarted = false;
    private $currentCaching = '';
    private $enabled = false;

    function __construct() {
        if (!is_admin()) return;
        $this->settings = json_decode(get_option('wp_admin_cache_settings'), true);
        $this->enabled = $this->settings['enabled'];
        add_action('admin_menu', array($this, 'init'));
        add_action('admin_print_footer_scripts', array($this, 'writeScripts'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
        if ($this->enabled) {
            $this->begin();
            $this->autoPurgeCache();
        }
    }

    function add_action_links($links) {
        $mylinks = array(
            '<a href="' . esc_url('options-general.php?page=wp-admin-cache') . '" >' . __('Settings', 'grfwpt') . '</a>'
        );
        return array_merge($links, $mylinks);
    }

    function init() {
        add_options_page('WP Admin Cache', 'WP Admin Cache', 'manage_options', 'wp-admin-cache', array($this, 'options_page'));
        wp_enqueue_script('wp-admin-cache-script', plugin_dir_url(__FILE__) . 'index.js', array(), '0.2.7');
        wp_enqueue_style('wp-admin-cache-style', plugin_dir_url(__FILE__) . 'index.css', array(), '0.2.7');
        $session = wp_get_session_token();
        if (!isset($_COOKIE['wp-admin-cache-session']) || $_COOKIE['wp-admin-cache-session'] != $session) setcookie('wp-admin-cache-session', $session, 0, admin_url());
    }

    function writeScripts() {
        if ($this->enabled) {
            echo '<script>wp_admin_cache_prefetch([';
            foreach ($this->getEnabledUrls() as $url) echo '"' . $url . '",';
            echo ']); </script>';
        }
    }

    public static function movePluginAtTop() {
        $path = str_replace(WP_PLUGIN_DIR . '/', '', __FILE__);
        if ($plugins = get_option('active_plugins')) {
            if ($key = array_search($path, $plugins)) {
                array_splice($plugins, $key, 1);
                array_unshift($plugins, $path);
                update_option('active_plugins', $plugins);
            }
        }
    }

    function options_page() {
        include_once 'settings.php';
    }

    function getToken() {
        if (isset($_COOKIE['wp-admin-cache-session'])) return $_COOKIE['wp-admin-cache-session'];
        return '';
    }

    function autoPurgeCache() {
        add_action('activated_plugin', array($this, 'purgeCache'));
        add_action('deactivated_plugin', array($this, 'purgeCache'));
        add_action('wp_insert_post', array($this, 'purgeCache'));
        add_filter('widget_update_callback', array($this, 'widget_update_callback'), 10, 3);
        add_action('upgrader_process_complete', array($this, 'upgrader_callback'), 10, 2);
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            if (!isset($_COOKIE['wp-admin-cache-lang']) || $lang != $_COOKIE['wp-admin-cache-lang']) {
                add_action('admin_init', array($this, 'purgeCache'));
                setcookie('wp-admin-cache-lang', $lang, 0, admin_url());
            }
        }
    }

    function purgeCache() {
        $enabledUrls=$this->getEnabledUrls();
        if($enabledUrls==null)return;
        foreach ($enabledUrls as $url) {
            delete_transient('wp-admin-cached-' . $this->getToken() . $url);
        }
    }

    function widget_update_callback($array) {
        $this->purgeCache();
        return $array;
    }

    function upgrader_callback($upgrader_object, $options) {
        $this->purgeCache();
    }

    function begin() {
        if ($this->beginStarted) return;
        $token = $this->getToken();
        if ($token == '') return;
        $this->beginStarted = true;
        $currentPage = add_query_arg(NULL, NULL);
        $currentPage = explode('/', $currentPage);
        $currentPage = $currentPage[count($currentPage) - 1];
        if (in_array($currentPage, $this->getEnabledUrls())) {
            $tName = 'wp-admin-cached-' . $token . $currentPage;
            $content = get_transient($tName);
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['wp_admin_cache_prefetch'])) {
                $this->purgeCache();
                return;
            }
            if (isset($_POST['wp_admin_cache_refresh']) && $_POST['wp_admin_cache_refresh'] == '1') $content = false;
            if ($content === false) {
                $this->currentCaching = $tName;
                ob_start(array($this, 'end'));
            } else {
                if (isset($_POST['wp_admin_cache_prefetch'])) {
                    preg_match('/--wp-admin-cached:(.*)--/', $content, $matches);
                    echo 'prefetched:' . (($this->settings['duration'] * 60) - (time() - $matches[1]));
                    die();
                }
                if (isset($this->settings['show-label']) && $this->settings['show-label'] == '1') {
                    echo str_replace('</body>', '<div class="wp-admin-cache-label">cached page</div></body>', $content);
                } else {
                    echo $content;
                }
                die();
            }
        }
    }

    function end($content) {
        if (strpos($content, '</html>') === false) return;
        $duration = $this->settings['duration'];
        if ($duration == '') $duration = '5';
        $content = str_replace('</body>', '<!--wp-admin-cached:' . time() . '--></body>', $content);
        set_transient($this->currentCaching, $content, 60 * $duration);
        if (isset($_POST['wp_admin_cache_prefetch'])) {
            return 'prefetching:' . ($this->settings['duration'] * 60);
        }
        return $content;
    }

    function getPageHooks() {
        global $admin_page_hooks;
        $a = array();
        foreach ($admin_page_hooks as $key => $value) {
            if (strpos($key, '.php') !== false && strpos($key, '/') === false && $key!='link-manager.php') array_push($a, $key);
        }
        $args = array('show_in_menu' => true);
        foreach (get_post_types($args) as $type) {
            if ($type != 'attachment') {
                $url = 'edit.php?post_type=' . $type;
                if (!in_array($url, $a)) array_push($a, $url);
            }
        }
        array_push($a, 'widgets.php');
        sort($a);
        return $a;
    }

    function getEnabledUrls() {
        $urls = $this->settings['enabled-urls'];
        return $urls;
    }

}

new AdminCache();

