<?php
/**
 * File class-manceppo-request-utils.php.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Contains some convenience utils regarding request parameter reading.
 */
class Manceppo_Request_Utils {

	/**
	 * Fields that can have HTML in the text.
	 */
	const HTML_FIELDS = array( 'manceppo_thanks_body', 'manceppo_form_intro', 'manceppo_submit_button_intro' );

	/**
	 * Gets the <tt>post_type</tt> by query-ing the <tt>$_GET</tt> parameters.
	 *
	 * @return string|null
	 */
	public static function get_post_type() {
		$post_type = self::get_request_value( $_GET, 'post_type' ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( is_null( $post_type ) ) {
			return isset( $_GET['post'] ) ? get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) : null;  // phpcs:ignore WordPress.Security.NonceVerification
		}

		return $post_type;
	}

	/**
	 * Retrieves and sanitizes value from HTTP request object.
	 *
	 * @param mixed       $request       The HTTP request object.
	 * @param string      $parameter     The request parameter to get the value from.
	 * @param string|null $default_value Default returned if current value is empty.
	 *
	 * @return string|null
	 */
	public static function get_request_value( $request, $parameter, $default_value = null ) {
		if ( isset( $request[ $parameter ] ) ) {
			$value = wp_unslash( $request[ $parameter ] );
			if ( empty( $value ) ) {
				return $default_value;
			}

			if ( in_array( $parameter, self::HTML_FIELDS, true ) ) {
				return balanceTags( $value );
			}

			if ( false !== strpos( $parameter, 'color' ) ) {
				return sanitize_hex_color( $value );
			}

			if ( 'manceppo_additional_css' === $parameter ) {
				return sanitize_textarea_field( $value );
			}

			return sanitize_text_field( $value );
		}

		return $default_value;
	}
}
