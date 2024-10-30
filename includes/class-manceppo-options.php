<?php
/**
 * File class-manceppo-options.php.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Wrapper around the WP options functionality.
 */
class Manceppo_Options {


	// default urls, can be overridden in connect form.
	const MANCEPPO_API_URL         = 'https://app.manceppo.com/api';
	const GOOGLE_RECAPTCHA_URL     = 'https://www.google.com/recaptcha/api/siteverify';
	const MANCEPPO_API_URL_KEY     = 'manceppo_url';
	const GOOGLE_RECAPTCHA_URL_KEY = 'manceppo_google_recaptcha_url';
	const MANCEPPO_API_KEY         = 'manceppo_api_key';
	const CUSTOM_CSS_KEY           = 'manceppo_css';
	const RECAPTCHA_SITE_KEY       = 'manceppo_recaptcha_key';
	const RECAPTCHA_SECRET_KEY     = 'manceppo_recaptcha_secret';
	const ENABLE_COOKIES_KEY       = 'manceppo_enable_cookies';
	const ENABLE_DEBUG_KEY         = 'manceppo_enable_debug';
	const SECRET_IS_SAVED          = '***Key has been set***';

	/**
	 * Gets the Manceppo API url.
	 *
	 * @return string
	 */
	public static function get_api_url() {
		$url = get_option( self::MANCEPPO_API_URL_KEY );
		if ( empty( $url ) ) {
			$url = self::MANCEPPO_API_URL;
		}

		return $url;
	}

	/**
	 * Gets the Manceppo API key.
	 *
	 * @param bool $for_edit get value in connection edit form.
	 *
	 * @return string
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public static function get_api_key( $for_edit = false ) {
		$secret = get_option( self::MANCEPPO_API_KEY );

		if ( $for_edit ) {
			if ( empty( $secret ) ) {
				return '';
			}

			return self::SECRET_IS_SAVED;
		}

		return $secret;
	}

	/**
	 * Indicates if the build-in css styles are overridden.
	 *
	 * @return bool
	 */
	public static function use_custom_css() {
		return 'on' === get_option( self::CUSTOM_CSS_KEY );
	}

	/**
	 * Google reCaptcha key.
	 *
	 * @return string
	 */
	public static function get_recaptcha_key() {
		return get_option( self::RECAPTCHA_SITE_KEY );
	}

	/**
	 * Google reCaptcha secret.
	 *
	 * @param bool $for_edit get value in connection edit form.
	 *
	 * @return string
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public static function get_recaptcha_secret( $for_edit = false ) {
		$secret = get_option( self::RECAPTCHA_SECRET_KEY );

		if ( $for_edit ) {
			if ( empty( $secret ) ) {
				return '';
			}

			return self::SECRET_IS_SAVED;
		}

		return $secret;
	}

	/**
	 * Google reCaptcha url.
	 *
	 * @return string
	 */
	public static function get_recaptcha_url() {
		$url = get_option( self::GOOGLE_RECAPTCHA_URL_KEY );
		if ( empty( $url ) ) {
			$url = self::GOOGLE_RECAPTCHA_URL;
		}

		return $url;
	}

	/**
	 * Indicates if cookies are enabled.
	 *
	 * @return bool
	 */
	public static function is_cookies_enabled() {
		return 'on' === get_option( self::ENABLE_COOKIES_KEY );
	}

	/**
	 * Indicates if the Manceppo plugin logs debug messages.
	 *
	 * @return bool
	 */
	public static function is_debug_enabled() {
		return 'on' === get_option( self::ENABLE_DEBUG_KEY );
	}

	/**
	 * Sets the Manceppo API url.
	 *
	 * @param string $url the Manceppo API url.
	 */
	public static function set_api_url( $url ) {
		update_option( self::MANCEPPO_API_URL_KEY, $url );
	}

	/**
	 * Sets the Google reCaptcha url.
	 *
	 * @param string $url the Google reCaptcha url.
	 */
	public static function set_recaptcha_url( $url ) {
		update_option( self::GOOGLE_RECAPTCHA_URL_KEY, $url );
	}

	/**
	 * Sets the Manceppo API key.
	 *
	 * @param string $api_key the Manceppo API key.
	 */
	public static function set_api_key( $api_key ) {
		if ( self::SECRET_IS_SAVED !== $api_key ) {
			update_option( self::MANCEPPO_API_KEY, $api_key );
		}
	}

	/**
	 * Indicates that the Manceppo plugin logs debug messages.
	 */
	public static function enable_debug() {
		update_option( self::ENABLE_DEBUG_KEY, 'on' );
	}

	/**
	 * Saves all the none required option fields.
	 *
	 * @param object $request the request object.
	 */
	public static function save_options( $request ) {
		self::save_option( $request, self::CUSTOM_CSS_KEY );
		self::save_option( $request, self::RECAPTCHA_SITE_KEY );

		$captcha_secret = Manceppo_Request_Utils::get_request_value( $request, self::RECAPTCHA_SECRET_KEY );
		if ( self::SECRET_IS_SAVED !== $captcha_secret ) {
			update_option( self::RECAPTCHA_SECRET_KEY, $captcha_secret );
		}

		self::save_option( $request, self::ENABLE_COOKIES_KEY );
		self::save_option( $request, self::ENABLE_DEBUG_KEY );
	}

	/**
	 * Update specified option with the request parameter value.
	 *
	 * @param object $request HTTP post object.
	 * @param string $name    the name of the option.
	 */
	private static function save_option( $request, $name ) {
		Manceppo_Logger::log( 'DEBUG manceppo::options - saving option: %s', $name );
		update_option( $name, Manceppo_Request_Utils::get_request_value( $request, $name ) );
	}

	/**
	 * Clears all the options in the database.
	 */
	public static function uninstall() {
		delete_option( self::MANCEPPO_API_URL_KEY );
		delete_option( self::MANCEPPO_API_KEY );
		delete_option( self::CUSTOM_CSS_KEY );
		delete_option( self::RECAPTCHA_SITE_KEY );
		delete_option( self::RECAPTCHA_SECRET_KEY );
		delete_option( self::ENABLE_COOKIES_KEY );
		delete_option( self::ENABLE_DEBUG_KEY );
		delete_option( self::GOOGLE_RECAPTCHA_URL_KEY );
	}
}
