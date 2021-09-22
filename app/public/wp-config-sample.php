<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

/**
 * For developers: The LocalWP debug mode.
 *
 * This this to “false” to let the website work in the live environment.
 * Set this value only to “true” when the website runs in LocalWP, locally.
 * Setting this value to “true” on a live instance will cause a database error!
 */
define( 'WP_LOCAL', true );

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', defined( 'WP_LOCAL' ) && WP_LOCAL ? 'local' : 'database_name_here' );

/** MySQL database username */
define( 'DB_USER',  defined( 'WP_LOCAL' ) && WP_LOCAL ? 'root' : 'username_here' );

/** MySQL database password */
define( 'DB_PASSWORD',  defined( 'WP_LOCAL' ) && WP_LOCAL ? 'root' : 'password_here' );

/** MySQL hostname */
define( 'DB_HOST',  defined( 'WP_LOCAL' ) && WP_LOCAL ? 'localhost' : 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** Adjust memory limit */
define( 'WP_MEMORY_LIMIT', '512M' );

/** Define SSL force for admins */
define( 'FORCE_SSL_ADMIN', true );

/** Define file edit lock */
define( 'DISALLOW_FILE_EDIT', true );

/** Define CRON DIY setting. */
define( 'DISABLE_WP_CRON', false );

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
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**#@-*/

/**
 * WordPress database table prefix.
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
define( 'WP_DEBUG', defined( 'WP_LOCAL' ) && WP_LOCAL || false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Include MB WP debug helper */
if ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( ABSPATH . 'mb-wp-debug.php' ) ) {
    include_once ABSPATH . 'mb-wp-debug.php';
}
if ( ! function_exists( 'console_log' ) ) {
    function console_log() {
    }
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';