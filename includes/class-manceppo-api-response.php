<?php
/**
 * File class-manceppo-api-response.php.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Class Manceppo_Api_Response.
 *
 * @package manceppo
 */
class Manceppo_Api_Response {

	/**
	 * Http status code.
	 *
	 * @var int
	 */
	private $code;
	/**
	 * Response body.
	 *
	 * @var mixed
	 */
	private $content;

	/**
	 * Manceppo_ApiResponse constructor.
	 *
	 * @param int|string $code    the HTTP response code.
	 * @param mixed      $content the response body as JSON object.
	 */
	public function __construct( $code, $content ) {
		$this->code    = $code;
		$this->content = $content;
	}

	/**
	 * Gets the status code or string.
	 *
	 * @return int|string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Gets the response body.
	 *
	 * @return mixed
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Whether status code is in the HTTP series <i>200</i>.
	 *
	 * @return bool
	 */
	public function is_successful() {
		if ( is_numeric( $this->code ) ) {
			return intval( $this->code / 100 ) === 2;
		}

		return false;
	}

	/**
	 * Whether status code is not in the HTTP series <i>200</i>.
	 *
	 * @param int $status the HTTP status.
	 *
	 * @return bool
	 */
	public static function is_error( $status ) {
		if ( is_numeric( $status ) ) {
			return $status >= 400 && $status < 600;
		}

		return true;
	}
}
