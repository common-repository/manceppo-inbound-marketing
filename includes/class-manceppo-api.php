<?php
/**
 * File class-manceppo-api.php.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Handles communication with the (remote) Manceppo API.
 */
class Manceppo_Api {

	const EMBEDDED_DOWNLOADS_URI = '/v1/embeddedDownloads';
	const DOWNLOAD_REQUEST_URI   = '/v1/downloadRequest';
	const CAMPAIGNS_URI          = '/v1/campaigns';
	const DOWNLOADS_URI          = '/v1/downloads';
	const API_KEY_CHECK_URI      = '/v1/campaigns';
	const FORMS_SYNC_URI         = '/v1/forms/sync';

	/**
	 * Singleton API instance.
	 *
	 * @var Manceppo_Api
	 */
	private static $instance;

	/**
	 * Gets singleton instance of this class.
	 *
	 * @return Manceppo_Api
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers embed of a Manceppo download in a WP page (to the Manceppo API).
	 *
	 * @param mixed  $did  the download id (as registered in Manceppo).
	 * @param mixed  $cid  the campaign id (as registered in Manceppo).
	 * @param string $slug the url of the WP page the downloads is embedded in.
	 * @param string $form the form identifier.
	 *
	 * @return mixed the response object.
	 */
	public function create_embedded_download( $did, $cid, $slug, $form ) {
		Manceppo_Logger::log( 'DEBUG manceppo::api - embedding a download with id: %s, campaign: %s, slug: %s, form: %s', $did, $cid, $slug, $form );

		$json_post_body = wp_json_encode(
			array(
				'download' => $did,
				'campaign' => $cid,
				'slug'     => $slug,
				'form'     => strval( $form ),
			)
		);

		return self::execute_call( self::EMBEDDED_DOWNLOADS_URI, $json_post_body );
	}

	/**
	 * Update settings of a WP form (to the Manceppo API).
	 *
	 * @param mixed  $did  the download id (as registered in Manceppo).
	 * @param mixed  $cid  the campaign id (as registered in Manceppo).
	 * @param string $form the form identifier.
	 *
	 * @return mixed the response object.
	 */
	public function sync_form_settings( $did, $cid, $form ) {
		Manceppo_Logger::log( 'DEBUG manceppo::api - sync form: %s, with campaign: %s and download: %s', $form, $cid, $did );

		$json_post_body = wp_json_encode(
			array(
				'download' => strval( $did ),
				'campaign' => strval( $cid ),
				'form'     => strval( $form ),
			)
		);

		return self::execute_call( self::FORMS_SYNC_URI, $json_post_body );
	}

	/**
	 * Perform call to Manceppo API.
	 *
	 * @param string $uri     the api endpoint to call.
	 * @param mixed  $payload optional payload to send.
	 *
	 * @return mixed the response object to parse in page.
	 */
	private function execute_call( $uri, $payload ) {
		$api_key = Manceppo_Options::get_api_key();

		if ( empty( $api_key ) ) {
			Manceppo_Logger::log( 'ERROR manceppo::api - Manceppo API key not valid' );

			return new Manceppo_Api_Response( 400, null );
		}

		$url = Manceppo_Options::get_api_url() . $uri;

		Manceppo_Logger::log( 'DEBUG manceppo::api - prepare call to API url: %s', $url );

		$args = array(
			'headers' => array(
				'Content-Type'       => 'application/json',
				'Accept'             => 'application/json',
				'X-Manceppo-Api-Key' => $api_key,
				'X-Manceppo-Client'  => MANCEPPO_VERSION,
			),
		);

		if ( null !== $payload ) {
			$args['method'] = 'POST';
			$args['body']   = $payload;
		}

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return new Manceppo_Api_Response( $response->get_error_code(), $response->get_error_message() );
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( Manceppo_Api_Response::is_error( $status ) ) {
			Manceppo_Logger::log(
				'ERROR manceppo::api - error response from API: status = %s, message = %s',
				$status,
				wp_remote_retrieve_response_message( $response )
			);

			// fail fast.
			return new Manceppo_Api_Response( $status, null );
		}

		if ( ! self::is_valid_json_response( $response ) ) {
			return new Manceppo_Api_Response( 400, null );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( $body ) {
			Manceppo_Logger::log( 'DEBUG manceppo::api - response from API: %s', $body );

			return new Manceppo_Api_Response( $status, json_decode( $body ) );
		}

		return new Manceppo_Api_Response( $status, null );
	}

	/**
	 * Call initiated when frontend form submit has been performed.
	 *
	 * @param string $json_post_body json string to send as payload to the Manceppo API.
	 *
	 * @return mixed the response object.
	 */
	public function create_download_request( $json_post_body ) {
		return self::execute_call( self::DOWNLOAD_REQUEST_URI, $json_post_body );
	}

	/**
	 * Gets list of campaigns the Manceppo user has created.
	 *
	 * @return mixed the response object.
	 */
	public function get_campaigns() {
		return self::execute_call( self::CAMPAIGNS_URI, null );
	}

	/**
	 * Gets single campaign by its (Manceppo) id.
	 *
	 * @param string $cid the campaign id.
	 *
	 * @return mixed  the response object.
	 */
	public function get_campaign( $cid ) {
		return self::execute_call( self::CAMPAIGNS_URI . '/' . $cid, null );
	}

	/**
	 * Gets list of downloads the Manceppo user has created.
	 *
	 * @return mixed the response object.
	 */
	public function get_downloads() {
		return self::execute_call( self::DOWNLOADS_URI, null );
	}

	/**
	 * Verifies we can establish a valid connection with the Manceppo API.
	 *
	 * @param string $api_key Manceppo api key to verify.
	 *
	 * @return int the HTTP status code.
	 */
	public function verify_api_key( $api_key ) {
		$url = Manceppo_Options::get_api_url() . self::API_KEY_CHECK_URI;

		Manceppo_Logger::log( 'DEBUG manceppo::api - prepare call to API url: %s', $url );

		$args = array(
			'headers' => array(
				'Content-Type'       => 'application/json',
				'Accept'             => 'application/json',
				'X-Manceppo-Api-Key' => $api_key,
			),
		);

		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			Manceppo_Logger::log( 'ERROR manceppo::api - verify api key: %s', $response->get_error_code() );

			return -1;
		}

		if ( ! self::is_valid_json_response( $response ) ) {
			return -1;
		}

		$code = wp_remote_retrieve_response_code( $response );
		Manceppo_Logger::log( 'DEBUG manceppo::api - api key check: status = %s', $code );

		return $code;
	}

	/**
	 * Verifies the content type returned by the Manceppo API.
	 *
	 * @param array $response the remote call response.
	 *
	 * @return bool
	 */
	private function is_valid_json_response( $response ) {
		$content_type = wp_remote_retrieve_header( $response, 'Content-Type' );
		if ( $content_type && false === strpos( strtolower( $content_type ), 'json' ) ) {
			Manceppo_Logger::log( 'WARN manceppo::api - invalid json response: %s', $content_type );

			return false;
		}

		return true;
	}
}
