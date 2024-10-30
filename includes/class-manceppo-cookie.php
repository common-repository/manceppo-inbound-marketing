<?php
/**
 * File class-manceppo-cookie.php.
 *
 * @package manceppo
 */

namespace manceppo;

use Exception;

/**
 * Class Manceppo_Cookie used for using pre-fill of the download form.
 *
 * @package manceppo
 */
class Manceppo_Cookie {

	/**
	 * Fallback cookie key, only needed if <pre>AUTH_KEY</pre> is not defined.
	 */
	const COOKIE_KEY = 'TlBnN29PfClmajxkPnVSe30qdHRFP0p8bSx7Pyo/ZVotVGVzTjBoL30mRy0tVzduKFtYbXhKU2l8NDcyUjlyNwo=';
	/**
	 * Cipher to use for en/de-crypting.
	 */
	const CIPHER = 'AES-256-CBC';
	/**
	 * Name of the cookie to set.
	 */
	const COOKIE_NAME = 'MCP_DOWNLOAD';
	/**
	 * The body of the cookie.
	 *
	 * @var mixed
	 */
	private $cookie_value;

	/**
	 * Set cookie using the form fields, to be used for later form pre-fill.
	 *
	 * @param array $fields the list of download form fields.
	 *
	 * @return string|null the generated cookie value for testing.
	 */
	public function create( $fields ) {
		try {
			$values = array();

			$manceppo_fields = Manceppo_Fields::get_instance()->get_fields();
			foreach ( $manceppo_fields as $manceppo_field ) {
				$json_name = $manceppo_field->get_json_name();
				if ( isset( $fields[ $json_name ] ) ) {
					$values[ $json_name ] = $fields[ $json_name ];
				}
			}

			$data  = wp_json_encode( $values );
			$value = $this->encrypt_openssl( $data );
			setrawcookie( self::COOKIE_NAME, $value, time() + ( 30 * DAY_IN_SECONDS ), '/', '', is_ssl(), true );

			return $value;
		} catch ( Exception $e ) {
			Manceppo_Logger::log( 'WARN manceppo::cookie - error while creating cookie: %s', $e->getMessage() );
		}

		return null;
	}

	/**
	 * Reads the field value of a previous submitted form.
	 *
	 * @param string $field_name the field name to get the value of.
	 *
	 * @return string
	 */
	public function get_field_value( $field_name ) {
		try {
			if ( is_null( $this->cookie_value ) && isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
				$data               = sanitize_key( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
				$result             = $this->decrypt_openssl( $data );
				$this->cookie_value = json_decode( $result );
			}

			if ( ! is_null( $this->cookie_value ) && property_exists( $this->cookie_value, $field_name ) ) {
				Manceppo_Logger::log( 'DEBUG manceppo::cookie - pre-fill field [%s]', $field_name );

				return $this->cookie_value->{$field_name};
			}
		} catch ( Exception $e ) {
			Manceppo_Logger::log( 'WARN manceppo::cookie - error while extracting cookie: %s', $e->getMessage() );
		}

		return '';
	}

	/**
	 * Encrypt the form data to use in the cookie.
	 *
	 * @param mixed $data the from data to encrypt.
	 *
	 * @return string
	 */
	private function encrypt_openssl( $data ) {
		$iv_size           = openssl_cipher_iv_length( static::CIPHER );
		$init_vector       = openssl_random_pseudo_bytes( $iv_size );
		$encrypted_message = openssl_encrypt( $data, static::CIPHER, $this->get_encrypt_key(), OPENSSL_RAW_DATA, $init_vector );

		return bin2hex( $init_vector . $encrypted_message );
	}

	/**
	 * Decrypt the data stored in the cookie.
	 *
	 * @param mixed $value the encrypted data.
	 *
	 * @return string
	 */
	private function decrypt_openssl( $value ) {
		$data        = hex2bin( $value );
		$iv_size     = openssl_cipher_iv_length( static::CIPHER );
		$init_vector = substr( $data, 0, $iv_size );
		$data        = substr( $data, $iv_size );

		return openssl_decrypt( $data, static::CIPHER, $this->get_encrypt_key(), OPENSSL_RAW_DATA, $init_vector );
	}

	/**
	 * Gets the encryption key to use.
	 *
	 * @return false|string
	 */
	private function get_encrypt_key() {
		if ( defined( 'AUTH_KEY' ) ) {
			return AUTH_KEY;
		}

		// XXX fallback only.
		return base64_decode( self::COOKIE_KEY ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}
}
