<?php
/**
 * File class-manceppo-logger.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Class Manceppo_Logger.
 */
class Manceppo_Logger {

	/**
	 * Singleton logger instance.
	 *
	 * @var Manceppo_Logger
	 */
	private static $instance;

	/**
	 * Indicates if debug is enabled.
	 *
	 * @var bool
	 */
	private $is_debug_enabled;

	/**
	 * Manceppo_Logger constructor.
	 *
	 * @param bool $is_debug_enabled indicates if we should print log messages.
	 */
	public function __construct( $is_debug_enabled ) {
		$this->is_debug_enabled = $is_debug_enabled;
	}

	/**
	 * Indicates if debug is enabled.
	 *
	 * @return bool
	 */
	public static function is_debug_enabled() {
		return self::get_instance()->is_debug_enabled;
	}

	/**
	 * Logs message to <code>error_log</code> if debug logging is set to <code>true</code>.
	 *
	 * @param string     $message the message to log.
	 * @param mixed|null ...$args optional arguments to be replaced in the message.
	 */
	public static function log( $message, ...$args ) {
		self::get_instance()->log_message( $message, $args );
	}

	/**
	 * Lazy initializes singleton instance.
	 *
	 * @return Manceppo_Logger
	 */
	private static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( Manceppo_Options::is_debug_enabled() );
		}

		return self::$instance;
	}

	/**
	 * Logs the message to <code>error_log</code>.
	 *
	 * @param string     $message The message to log.
	 * @param mixed|null ...$args optional list of args to be replaced in the message.
	 */
	public function log_message( $message, ...$args ) {
		if ( $this->is_debug_enabled ) {
			if ( 0 === count( ...$args ) ) {
				error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			} else {
				error_log( vsprintf( $message, ...$args ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}
}
