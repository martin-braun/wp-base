<?php if ( ! defined( 'ABSPATH' ) ) exit;

if( defined( 'WP_LOCAL' ) && WP_LOCAL ) {
	
	/** Disable specific plugins in WP_LOCAL mode */
	deactivate_plugins( [
			'w3-total-cache/w3-total-cache.php',
			'slim-maintenance-mode/slim-maintenance-mode.php',
			'wordfence-login-security/wordfence-login-security.php',
			'wordfence/wordfence.php',
			'wp-admin-cache/index.php',
			'wp-mail-smtp/wp_mail_smtp.php',
			'xcloner-backup-and-restore/xcloner.php'
	] );

}
