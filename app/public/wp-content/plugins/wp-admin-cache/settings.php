<?php
if (!isset($this)) die('');
if (!is_admin()) die('');
$status = '';
if (isset($_POST['wp_admin_cache_save'])) {
    check_admin_referer('wp_admin_cache_settings_n');
    $obj = array();
    $obj['enabled'] = sanitize_text_field($_POST['wp_admin_cache_enabled']);
    $this->enabled = $obj['enabled'];
    if (isset($_POST['wp_admin_cache_url_enabled'])) {
        $urlsEnabled = $_POST['wp_admin_cache_url_enabled'];
        $a = array();
        foreach ($urlsEnabled as $value) {
            array_push($a, sanitize_text_field($value));
        }
        $obj['enabled-urls'] = $a;
    }
    $obj['show-label'] = (int) sanitize_text_field($_POST['wp_admin_cache_show_label']);
    $obj['load-first'] = (int) sanitize_text_field($_POST['wp_admin_cache_load_first']);
    $obj['duration'] = (int) sanitize_text_field($_POST['wp_admin_cache_duration']);

    update_option('wp_admin_cache_settings', json_encode($obj));
    if ($obj['load-first'] == 1) {
        self::movePluginAtTop();
    }
    $this->purgeCache();

    $status = '<div class="updated notice"><p>' . __('Settings updated', 'wp-admin-cache') . '</p></div>';
}

$settings = json_decode(get_option('wp_admin_cache_settings'), true);
$enabled = $settings['enabled'];
$enabledUrls = $settings['enabled-urls'];
$duration = $settings['duration'];
if ($duration == '') $duration = '5';
global $menu, $admin_page_hooks;
?>
<?php echo $status ?>
<form method="post">
    <h3>
        <label><input type="checkbox" name="wp_admin_cache_enabled" value="1" <?php if ($enabled) echo 'checked' ?>> <?php echo __('Cache enabled', 'wp-admin-cache') ?></label>	
    </h3>
    <h2><?php echo __('Cached pages', 'wp-admin-cache') ?></h2>
    <p><?php echo __('Select which pages should be cached.', 'wp-admin-cache'); ?></p>
    <p><label><input type="checkbox" class="wp-admin-cache-selectAll" /><?php echo __('Select/unselect all', 'wp-admin-cache') ?></label></p>
    <ul class="wp-admin-cache-pageList">
        <?php
        foreach ($this->getPageHooks() as $url) {
            $checked = '';
            if ($enabledUrls != null) {
                if (in_array($url, $enabledUrls)) $checked = 'checked';
            }
            echo '<li><label><input type="checkbox" name="wp_admin_cache_url_enabled[]" value="' . $url . '" ' . $checked . ' > ' . $url . '</label></li>';
        }
        ?>
    </ul>
    <p>
        <?php echo __('Max cache duration', 'wp-admin-cache') ?>: <input type="number" name="wp_admin_cache_duration" value="<?php echo $duration ?>"> <?php echo __('minutes', 'wp-admin-cache') ?><br>
        <?php echo __('The cache is regenerated after the specified time, or when certain events occur (adding or updating a post or a page, saving options, saving widgets, activating plugins).', 'wp-admin-cache') ?><br>
        <?php echo __('Important: the cache is managed for each user; if more than one user is connected, not all users may have updated pages; in these cases, reduce the duration of the cache.', 'wp-admin-cache') ?>
    </p>
    <p>
        <label><input type="checkbox" name="wp_admin_cache_show_label" value="1" <?php if ($settings['show-label'] == '1') echo 'checked' ?>> <?php echo __('Show a label on each cached page (shows which pages are actually cached)', 'wp-admin-cache') ?></label>	
    </p>
    <p>
        <label><input type="checkbox" name="wp_admin_cache_load_first" value="1" <?php if ($settings['load-first'] == '1') echo 'checked' ?>> <?php echo __('Load this plugin before the others (can improve performance of cached pages)', 'wp-admin-cache') ?></label>	
    </p>
    <input type="submit" name="wp_admin_cache_save" value="<?php echo __('Save and purge cache', 'wp-admin-cache') ?>" class="button button-primary" >

    <p><?php echo __('The plugin is under development, more features and optimizations will be activated soon.') ?></p>       
    <?php
    if (function_exists('wp_nonce_field')) wp_nonce_field('wp_admin_cache_settings_n');
    ?>
</form>