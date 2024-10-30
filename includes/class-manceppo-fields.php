<?php
/**
 * File class-manceppo-fields.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Holds all the fields that will be send to the Manceppo API.
 */
class Manceppo_Fields {


	const GENERAL_FORM_FIELDS = array(
		'download',
		'campaign',
		'postUrl',
		'visitorId',
		'newsletters',
		'message',
	);

	/**
	 * Singleton instance.
	 *
	 * @var Manceppo_Fields
	 */
	private static $instance;

	/**
	 * List of fields.
	 *
	 * @var array
	 */
	private $fields_array;

	/**
	 * Manceppo_Fields constructor.
	 */
	private function __construct() {
		// XXX Order is important.
		$this->fields_array = array(
			new Manceppo_Field( 'email', 'email', 'Email address', 'email', true ),
			new Manceppo_Field( 'industry', 'industry', 'Industry' ),
			new Manceppo_Field( 'first_name', 'firstName', 'First Name' ),
			new Manceppo_Field( 'address1', 'address1', 'Address 1' ),
			new Manceppo_Field( 'last_name', 'lastName', 'Last Name' ),
			new Manceppo_Field( 'address2', 'address2', 'Address 2' ),
			new Manceppo_Field(
				'gender',
				'gender',
				'Gender',
				'select',
				false,
				array(
					'female',
					'male',
					'other',
					'unknown',
				)
			),
			new Manceppo_Field( 'phone', 'phone', 'Phone' ),
			new Manceppo_Field( 'company', 'company', 'Company' ),
			new Manceppo_Field( 'postal_code', 'postalCode', 'Postal Code' ),
			new Manceppo_Field( 'job_title', 'jobTitle', 'Job Title' ),
			new Manceppo_Field( 'city', 'city', 'City' ),
			new Manceppo_Field( 'job_function', 'jobFunction', 'Job Function' ),
			new Manceppo_Field( 'state_province', 'state', 'State/Province' ),
			new Manceppo_Field( 'number_of_employees', 'numberOfEmployees', 'Number of employees' ),
			new Manceppo_Field( 'country', 'country', 'Country' ),
		);
	}

	/**
	 * Gets list of all the fields.
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->fields_array;
	}

	/**
	 * Gets singleton instance of this class.
	 *
	 * @return Manceppo_Fields
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
