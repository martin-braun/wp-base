<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

/**
 * For developers: The LocalWP debug mode.
 *
 * Set this to “false” to let the website work in the live environment.
 * Set this value only to “true” when the website runs in LocalWP, locally.
 * Setting this value to “true” on a live instance will cause a database error!
 */
define('WP_LOCAL', true);

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', defined('WP_LOCAL') && WP_LOCAL ? 'local' : 'database_name_here');

/** MySQL database username */
define('DB_USER',  defined('WP_LOCAL') && WP_LOCAL ? 'root' : 'username_here');

/** MySQL database password */
define('DB_PASSWORD',  defined('WP_LOCAL') && WP_LOCAL ? 'root' : 'password_here');

/** MySQL hostname */
define('DB_HOST',  defined('WP_LOCAL') && WP_LOCAL ? 'localhost' : 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/** Adjust memory limit */
define('WP_MEMORY_LIMIT', '8G');

/** Define SSL force for admins */
define('FORCE_SSL_ADMIN', true);

/** Define file edit lock */
define('DISALLOW_FILE_EDIT', true);

/** Define CRON DIY setting. */
define('DISABLE_WP_CRON', !(defined('WP_LOCAL') && WP_LOCAL));

/** Define free product on free product campaign. */
define('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_FREE_PRODUCT_ID', 0);

/** Define price trigger on free product campaign. */
define('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRICE_TRIGGER', 0);

/** Define product trigger on free product campaign. */
define('WOOCOMMERCE_FREE_PRODUCT_CAMPAIGN_PRODUCT_TRIGGER_PRODUCT_ID', 0);

/**
 * WP Mail SMTP settings.
 *
 * These constants should be used to configure a custom SMTP server.
 */
// define( 'WPMS_ON', true );
// define( 'WPMS_MAILER', 'smtp' );
// define( 'WPMS_SSL', 'tls' ); // Possible values '', 'ssl', 'tls'
// define( 'WPMS_SMTP_AUTH', true );
// define( 'WPMS_SMTP_HOST', '' ); // The SMTP mail host.
// define( 'WPMS_SMTP_PORT', 587 ); // The SMTP server port number.
// define( 'WPMS_MAIL_FROM', '' );
// define( 'WPMS_SMTP_USER', '' );
// define( 'WPMS_SMTP_PASS', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '9uKmlbIhnXTOiKkf3EMWdUbX2klGfG0IpuiI3DUS2HsvXFXpIO9exxNMXekY3dn0R5A3kO9M3zQ2bK536gxlFQ==');
define('SECURE_AUTH_KEY',  'LxrMe7arqEm6c2fJxlRf6rgzLXe8xJfrPCcwiLZH2QsGLuXjBbiLqFwVgkbNuiUu2tqKW8ka9NofJykA40z0RA==');
define('LOGGED_IN_KEY',    'VGYDcmVWCDmC234Dyqh3fHOSZmcS8/6geIDBdrfGGDxp+8dmERep5YNs89ODd7es/52VUbiFahS2rUu6vwFVEA==');
define('NONCE_KEY',        'gwxirJFtD3kbW9Ouym7JU0I2F4JdBea3mRWtklLhO7rfpfiZp1nTLFg8qntFYgNAzR8mnVfN/WvoBxzAmqkF3w==');
define('AUTH_SALT',        '4u8nDPhteJ8x55l/sK4WFIx3zzLmYF0wOUc04QjgjSv4h13vPsOURf/pCJPOoUIheaUnzoTqzx1FPJEdzwvcIQ==');
define('SECURE_AUTH_SALT', '0aQNkSdie+tztRcjN5c6n6rf8kBj+U7y0P99TbFCP3NwHIPFqiFYOr8VCDXDT9t59NW3FDSN6PMRJyI6gWs4jA==');
define('LOGGED_IN_SALT',   'fTXknPfAJO2zAFb0TZG26dSHs1rKBDALYSIYB25Fbs9H4+uUicG8wAM9ls2+ZkY+zTax/tOHPXCUYTfJed1ebg==');
define('NONCE_SALT',       '/ARBkolc/iUUnWpIgPM4Gf8seVeyUuA0oMOLHanIt6N7oG08FlipjeeZpULIUeW6pkr9cfAl3O6oKkYxZjiZXQ==');

/**
 * Constant to Configure Core Updates.
 * 
 * To enable automatic updates for major releases or development purposes, 
 * the place to start is with the WP_AUTO_UPDATE_CORE constant. 
 * Defining this constant one of three ways allows you to blanket-enable, 
 * or blanket-disable several types of core updates at once.
 * 
 * Value of true – Development, minor, and major updates are all enabled
 * Value of false – Development, minor, and major updates are all disabled
 * Value of 'minor' – Minor updates are enabled, development, and major updates are disabled
 */
define('WP_AUTO_UPDATE_CORE', defined('WP_LOCAL') && WP_LOCAL || 'minor');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', defined('WP_LOCAL') && WP_LOCAL || false);

/**
 * For developers: WordPress logging.
 *
 * Change this to true to enable the logging of notices during development.
 * This will create a debug.log in wp-content
 *
 * To manually log something, please use error_log.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/#wp_debug_log
 */
define('WP_DEBUG_LOG', defined('WP_LOCAL') && WP_LOCAL || false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', dirname(__FILE__) . '/');
}

/** Include debug helper */
if ((defined('WP_LOCAL') && WP_LOCAL) || (defined('WP_DEBUG') && WP_DEBUG) && file_exists(ABSPATH . 'mb-wp-debug.php')) {
	include_once ABSPATH . 'mb-wp-debug.php';
} else {
	function console_log()
	{
	}
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

/** Ensure staging color indicator for local dev environments for safety reasons. */
if (defined('WP_LOCAL') && WP_LOCAL && function_exists('add_local_wp_css')) {
	add_local_wp_css();
}
