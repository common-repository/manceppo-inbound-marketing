<?php
/**
 * File class-manceppo-rest-api.php
 *
 * @package manceppo
 */

namespace manceppo;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Manceppo_Rest_Api handles the front end form submission/ajax call.
 */
class Manceppo_Rest_Api {

	/**
	 * Register the REST API.
	 */
	public static function init() {
		register_rest_route(
			'manceppo/v1',
			'/download',
			array(
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array(
						'manceppo\Manceppo_Rest_Api',
						'download_request',
					),
				),
			)
		);

		register_rest_route(
			'manceppo/v1',
			'/captcha',
			array(
				array(
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => array( 'manceppo\Manceppo_Rest_Api', 'captcha' ),
				),
			)
		);
	}

	/**
	 * Sends download form parameters to Manceppo.
	 *
	 * @param WP_REST_Request $request the download form request.
	 *
	 * @return WP_REST_Response
	 */
	public static function download_request( $request ) {
		Manceppo_Logger::log( 'DEBUG manceppo::rest-api - received download form submit' );

		if ( ! wp_verify_nonce( $request->get_param( '_wpnonce' ), 'wp_rest' ) ) {
			Manceppo_Logger::log( 'ERROR manceppo::rest-api - nonce not valid' );

			return new WP_REST_Response( array( 'message' => 'Cookie nonce is invalid' ), 403 );
		}

		$vals = array();
		foreach ( Manceppo_Fields::GENERAL_FORM_FIELDS as $field ) {
			if ( isset( $request[ $field ] ) ) {
				$vals[ $field ] = $request->get_param( $field );
			}
		}

		$manceppo_fields = Manceppo_Fields::get_instance()->get_fields();
		foreach ( $manceppo_fields as $manceppo_field ) {
			$field = $manceppo_field->get_json_name();
			if ( isset( $request[ $field ] ) ) {
				$vals[ $field ] = $request->get_param( $field );
			}
		}

		if ( Manceppo_Options::is_cookies_enabled() ) {
			Manceppo_Logger::log( 'DEBUG manceppo::rest-api - saving cookie' );

			$manceppo_cookie = new Manceppo_Cookie();
			$manceppo_cookie->create( $vals );
		}

		$json_body         = wp_json_encode( $vals );
		$manceppo_response = Manceppo_Api::get_instance()->create_download_request( $json_body );

		return new WP_REST_Response( array( 'message' => 'Download request send' ), $manceppo_response->get_code() );
	}

	/**
	 * Verifies recaptcha for form download.
	 *
	 * @param WP_REST_Request $request the form initialization request.
	 *
	 * @return WP_REST_Response
	 */
	public static function captcha( $request ) {
		Manceppo_Logger::log( 'DEBUG manceppo::rest-api - received captcha request' );

		if ( ! wp_verify_nonce( $request->get_param( '_wpnonce' ), 'wp_rest' ) ) {
			Manceppo_Logger::log( 'ERROR manceppo::rest-api - nonce not valid' );

			return new WP_REST_Response( array( 'message' => 'Cookie nonce is invalid' ), 403 );
		}

		$token = $request->get_param( 'token' );
		if ( ! empty( $token ) ) {
			$data          = array(
				'secret'   => Manceppo_Options::get_recaptcha_secret(),
				'response' => $request->get_param( 'token' ),
			);
			$query         = http_build_query( $data, null, '&', PHP_QUERY_RFC3986 );
			$recaptcha_url = Manceppo_Options::get_recaptcha_url();
			$url           = $recaptcha_url . '?' . $query;

			Manceppo_Logger::log( 'DEBUG manceppo::rest-api - using captcha url: %s', $recaptcha_url );

			$args = array(
				'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
				'method'  => 'POST',
			);

			$response = wp_remote_request( $url, $args );
			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				if ( $body ) {
					$json_body = json_decode( $body );
					if ( property_exists( $json_body, 'success' ) && true === $json_body->success ) {
						return new WP_REST_Response( array( 'message' => 'reCaptcha ok' ), 200 );
					}

					Manceppo_Logger::log( 'ERROR manceppo::rest-api - invalid recaptcha response: %s', $body );
				}
				Manceppo_Logger::log( 'WARN manceppo::rest-api - invalid recaptcha body' );
			}
		}

		return new WP_REST_Response( array( 'message' => 'Invalid request' ), 400 );
	}
}
