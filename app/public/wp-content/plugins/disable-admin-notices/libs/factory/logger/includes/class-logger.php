<?php

namespace WBCR\Factory_Logger_101;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds ability to log application message into .log file.
 *
 * It has 4 core levels:
 * - info: generic log message
 * - warning: log possible exceptions states or unusual
 * - error: log error-related logs
 * - debug: log stack traces, big outputs, etc.
 *
 * Each level has its constant. See LEVEL_* prefix.
 *
 * Additionally it is possible to configure flush interval and file name.
 *
 * Usage examples:
 *
 * ```php
 * // Info message level
 * $this->info('Some generic message, good to know');
 *
 * // Warning message level
 * $this->warning('Something does not work or unusual');
 *
 * // Error message level
 * $this->error('Something critical happened');
 *
 * // Debug message level
 * $this->debug('Some message used for debug purposed. Could be stack trace.');
 * ```
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020, Webcraftic
 * @version       1.0
 */
class Logger {

	const LEVEL_INFO = 'info';
	const LEVEL_WARNING = 'warning';
	const LEVEL_ERROR = 'error';
	const LEVEL_DEBUG = 'debug';

	/**
	 * @var \Wbcr_Factory443_Plugin Plugin class.
	 */
	public $plugin;

	/**
	 * @var null|string Request hash.
	 */
	public $hash = null;

	/**
	 * @var null|string Directory where log file would be saved.
	 */
	public $dir = null;

	/**
	 * @var string File log name where logs would be flushed.
	 */
	public $file = 'app.log';

	/**
	 * @var int Flushing interval. When $_logs would reach this number of items they would be flushed to log file.
	 */
	public $flush_interval = 1000;

	/**
	 * @var int Rotate size in bytes. Default: 5 Mb.
	 */
	public $rotate_size = 5000000;

	/**
	 * @var int Number of rotated files. When size of $rotate_size matches current file, current file would be rotated.
	 * For example, there are 3 files, current file became size of $rotate_size, third file would be deleted, two first
	 * shifted and empty one created.
	 */
	public $rotate_limit = 3;

	/**
	 * @var array List of logs to be dumped.
	 */
	private $_logs = [];

	/**
	 * Logger constructor.
	 *
	 * @param \Wbcr_Factory443_Plugin $plugin
	 * @param array $settings
	 */
	public function __construct( $plugin, $settings = [] ) {
		$this->plugin = $plugin;
		$this->init( $settings );
	}

	/**
	 * Initiate object.
	 *
	 * @param array $settings
	 */
	public function init( $settings ) {
		$this->hash = substr( uniqid(), - 6, 6 );

		if ( is_array( $settings ) && ! empty( $settings ) ) {
			foreach ( $settings as $key => $value ) {
				if ( isset( $this->$key ) ) {
					$this->$key = $value;
				}
			}
		}

		add_action( 'shutdown', [ $this, 'shutdown_flush' ], 9999, 0 );
	}

	/**
	 * Get directory to save collected logs.
	 *
	 * In addition to that, it manages log rotation so that it does not become too big.
	 *
	 * @return string|false false on failure, string on success.
	 */
	public function get_dir() {

		$base_dir = $this->get_base_dir();

		if ( $base_dir === null ) {
			return false;
		}

		$root_file = $base_dir . $this->file;

		// Check whether file exists and it exceeds rotate size, then should rotate it copy
		if ( file_exists( $root_file ) && filesize( $root_file ) >= $this->rotate_size ) {
			$name_split = explode( '.', $this->file );

			if ( ! empty( $name_split ) && isset( $name_split[0] ) ) {
				$name_split[0] = trim( $name_split[0] );

				for ( $i = $this->rotate_limit; $i >= 0; $i -- ) {

					$cur_name = $name_split[0] . $i;
					$cur_path = $base_dir . $cur_name . '.log';

					$next_path = $i !== 0 ? $base_dir . $name_split[0] . ( $i - 1 ) . '.log' : $root_file;

					if ( file_exists( $next_path ) ) {
						@copy( $next_path, $cur_path );
					}
				}
			}

			// Need to empty root file as it was supposed to be copied to next rotation :)
			@file_put_contents( $root_file, '' );
		}

		return $root_file;
	}

	/**
	 * Get base directory, location of logs.
	 *
	 * @return null|string NULL in case of failure, string on success.
	 */
	public function get_base_dir() {
		$plugin_slug = $this->plugin->plugin_slug;

		if ( empty( $this->dir ) ) {
			$base_path = wp_normalize_path( trailingslashit( WP_CONTENT_DIR ) . "logs/{$plugin_slug}/" );
		} else {
			$base_path = wp_normalize_path( trailingslashit( $this->dir ) . "{$plugin_slug}/" );
		}

		$folders = glob( $base_path . 'logs-*' );

		if ( ! empty( $folders ) ) {
			$exploded_path        = explode( '/', trim( $folders[0] ) );
			$selected_logs_folder = array_pop( $exploded_path );
		} else {
			if ( function_exists( 'wp_salt' ) ) {
				$hash = md5( wp_salt() );
			} else {
				$hash = md5( AUTH_KEY );
			}

			$selected_logs_folder = 'logs-' . $hash;
		}

		$path = $base_path . $selected_logs_folder . '/';

		if ( ! file_exists( $path ) ) {
			@mkdir( $path, 0755, true );
		}

		// Create .htaccess file to protect log files
		$htaccess_path = $path . '.htaccess';

		if ( ! file_exists( $htaccess_path ) ) {
			$htaccess_content = 'deny from all';
			@file_put_contents( $htaccess_path, $htaccess_content );
		}

		// Create index.htm file in case .htaccess is not support as a fallback
		$index_path = $path . 'index.html';

		if ( ! file_exists( $index_path ) ) {
			@file_put_contents( $index_path, '' );
		}

		return $path;
	}

	/**
	 * Get all available log paths.
	 *
	 * @return array|bool
	 */
	public function get_all() {
		$base_dir = $this->get_base_dir();

		if ( $base_dir === null ) {
			return false;
		}

		$glob_path = $base_dir . '*.log';

		return glob( $glob_path );
	}

	/**
	 * Get total log size in bytes.
	 *
	 * @return int
	 * @see size_format() for formatting.
	 */
	public function get_total_size() {
		$logs  = $this->get_all();
		$bytes = 0;

		if ( empty( $logs ) ) {
			return $bytes;
		}

		foreach ( $logs as $log ) {
			$bytes += @filesize( $log );
		}

		return $bytes;
	}

	/**
	 * Empty all log files and deleted rotated ones.
	 *
	 * @return bool
	 */
	public function clean_up() {

		$base_dir = $this->get_base_dir();

		if ( $base_dir === null ) {
			return false;
		}

		$glob_path = $base_dir . '*.log';

		$files = glob( $glob_path );

		if ( $files === false ) {
			return false;
		}

		if ( empty( $files ) ) {
			return true;
		}

		$unlinked_count = 0;

		foreach ( $files as $file ) {
			if ( @unlink( $file ) ) {
				$unlinked_count ++;
			}
		}

		return count( $files ) === $unlinked_count;
	}

	/**
	 * Flush all messages.
	 *
	 * @return bool
	 */
	public function flush() {

		$messages = $this->_logs;

		$this->_logs = [];

		if ( empty( $messages ) ) {
			return false;
		}

		$file_content = PHP_EOL . implode( PHP_EOL, $messages );
		$is_put       = @file_put_contents( $this->get_dir(), $file_content, FILE_APPEND );

		return $is_put !== false;
	}

	/**
	 * Flush all messages.
	 *
	 */
	public function shutdown_flush() {
		$end_line = "-------------------------------";
		if ( ! empty( $this->_logs ) ) {
			$this->_logs[] = $end_line;
		}

		$this->flush();
	}

	/**
	 *
	 * @param $level
	 * @param $message
	 *
	 * @return string
	 */
	public function get_format( $level, $message ) {

		// Example: 2019-01-14 12:03:29.0593  [127.0.0.1][ee6a12][info] {message}
		$template = '%s [%s][:%s:] %s';
		$date     = date( 'd-m-Y H:i:s' );

		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';

		return sprintf( $template, $date, $ip, $level, $message );
	}

	/**
	 * Get latest file content.
	 *
	 * @return bool|string
	 */
	public function get_content() {
		if ( ! file_exists( $this->get_dir() ) ) {
			return null;
		}

		return @file_get_contents( $this->get_dir() );
	}

	/**
	 * Get Export object.
	 *
	 * @return bool|string
	 */
	public function get_export() {
		return new Log_Export( $this, "{$this->plugin->plugin_slug}_log_export-{datetime}.zip" );
	}

	/**
	 * Add new log message.
	 *
	 * @param string $level Log level.
	 * @param string $message Message to log.
	 *
	 * @return bool
	 */
	public function add( $level, $message ) {

		//if ( $level === $this->LEVEL_DEBUG ) {
		//$log_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;

		//if ( ! $log_debug ) {
		//return false;
		//}
		//}

		$this->_logs[] = $this->get_format( $level, $message );

		if ( count( $this->_logs ) >= $this->flush_interval ) {
			$this->flush();
		}

		return true;
	}

	/**
	 * Add info level log.
	 *
	 * @param string $message Message to log.
	 */
	public function info( $message ) {
		$this->add( self::LEVEL_INFO, $message );
	}

	/**
	 * Add error level log.
	 *
	 * @param string $message Message to log.
	 */
	public function error( $message ) {
		$this->add( self::LEVEL_ERROR, $message );
	}

	/**
	 * Add debug level log.
	 *
	 * @param $message
	 */
	public function debug( $message ) {
		$this->add( self::LEVEL_DEBUG, $message );
	}

	/**
	 * Add warning level log.
	 *
	 * @param string $message Message to log.
	 */
	public function warning( $message ) {
		$this->add( self::LEVEL_WARNING, $message );
	}

	/**
	 * Writes information to log about memory.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.3.6
	 */
	public function memory_usage() {
		$memory_avail = ini_get( 'memory_limit' );
		$memory_used  = number_format( memory_get_usage( true ) / ( 1024 * 1024 ), 2 );
		$memory_peak  = number_format( memory_get_peak_usage( true ) / ( 1024 * 1024 ), 2 );

		$this->info( sprintf( "Memory: %s (avail) / %sM (used) / %sM (peak)", $memory_avail, $memory_used, $memory_peak ) );
	}

	/**
	 * Prettify log content.
	 *
	 * Helps to convert log file content into easy-to-read HTML.
	 *
	 * Usage example:
	 *
	 * @return bool|mixed|string
	 */
	public function prettify() {
		$content = $this->get_content();

		$search = [
			"\r\n",
			"\n\r",
			"\025",
			"\n",
			"\r",
			"\t",
		];

		$replacement = [
			'<br>',
			'<br>',
			'<br>',
			'<br>',
			'<br>',
			str_repeat( '&nbsp;', 4 ),
		];

		$content = str_replace( $search, $replacement, $content );


		$color_map = [
			self::LEVEL_INFO    => [ 'color' => '#fff', 'bg' => '#52d130' ],
			self::LEVEL_ERROR   => [ 'color' => '#fff', 'bg' => '#ff5e5e' ],
			self::LEVEL_WARNING => [ 'color' => '#fff', 'bg' => '#ef910a' ],
			self::LEVEL_DEBUG   => [ 'color' => '#fff', 'bg' => '#8f8d8b' ],
		];

		/**
		 * Highlight log levels
		 */
		foreach ( $color_map as $level => $item ) {
			$content = preg_replace( "/(\[:$level:\])/",
				"<span class='wbcr_logger_level' style='color: {$item['color']};background-color: {$item['bg']}'>$level</span>", $content );
		}

		return $content;
	}
}
