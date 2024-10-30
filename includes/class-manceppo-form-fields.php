<?php
/**
 * File class-manceppo-form-fields.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Holds general list of download form options and styling fields.
 */
class Manceppo_Form_Fields {

	/**
	 * All the option fields used in the download form.
	 */
	const ALL_FIELDS = array(
		'fields',
		'campaign',
		'column_count',
		'column_width',
		'column_width_unit',
		'download',
		'additional_css',
		'form_intro',
		'show_recaptcha',
		'submit_button_title',
		'submit_button_intro',
		'submit_button_color',
		'submit_button_text_color',
		'input_text_color',
		'thanks_body',
		'show_message_field',
		'message_field_label',
		'message_field_height',
		'message_field_width',
		'label_position',
		'space_top',
		'space_bottom',
		'space_left',
		'space_right',
	);

	/**
	 * Some download form field defaults.
	 */
	const DEFAULT_VALUES = array(
		'submit_button_title' => 'Submit',
		'form_intro'          => 'In order to get the download link, please fill the form below.',
	);

	/**
	 * The id of the post.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Verify if a field exists.
	 *
	 * @var bool
	 */
	private $validate;

	/**
	 * Manceppo_Form_Fields constructor.
	 *
	 * @param int  $post_id  the post id.
	 * @param bool $validate indicates if we should verify if a field exists.
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function __construct( $post_id, $validate = false ) {
		$this->post_id  = $post_id;
		$this->validate = $validate;
	}

	/**
	 * Gets the value for specified field.
	 *
	 * @param string $name the field name.
	 *
	 * @return mixed|null
	 */
	public function get( $name ) {
		if ( $this->validate ) {
			assert( in_array( $name, self::ALL_FIELDS, true ), 'Form field "' . $name . '" does not exist!' );
		}

		return get_post_meta( $this->post_id, '_manceppo_' . $name, true );
	}

	/**
	 * Gets the value for specified field.
	 *
	 * @param string     $name the field name.
	 * @param mixed|null $default_value default value returned when field is empty.
	 *
	 * @return mixed|null
	 */
	public function get_or_default( $name, $default_value ) {
		$value = $this->get( $name );
		if ( empty( $value ) ) {
			return $default_value;
		}
		return $value;
	}

	/**
	 * Gets the value of the newsletters field.
	 *
	 * @return mixed|null
	 */
	public function get_newsletters() {
		return get_post_meta( $this->post_id, '_manceppo_newsletters', true );
	}

	/**
	 * Updates specified field or deletes it when the value is <code>null</code> unless the field has a default value.
	 *
	 * @param string     $name  the field name.
	 * @param mixed|null $value the field value to set.
	 */
	public function update( $name, $value ) {
		if ( $this->validate ) {
			assert( in_array( $name, self::ALL_FIELDS, true ), 'Form field "' . $name . '" does not exist!' );
		}

		if ( is_null( $value ) && array_key_exists( $name, self::DEFAULT_VALUES ) ) {
			$value = self::DEFAULT_VALUES [ $name ];
		}

		if ( is_null( $value ) ) {
			Manceppo_Logger::log( 'DEBUG manceppo::form-fields - deleting field: %s', $name );

			delete_post_meta( $this->post_id, '_manceppo_' . $name );

			if ( 'campaign' === $name ) {
				delete_post_meta( $this->post_id, '_manceppo_thanks_body' );
			}
		} else {
			// make sure the show options use same value.
			if ( false !== strpos( $name, 'show_' ) && 'hide' !== $value ) {
				$value = 'show';
			}

			Manceppo_Logger::log(
				'DEBUG manceppo::form-fields - update field [%s] with value [%s]',
				$name,
				is_array( $value ) ? '[array]' : $value
			);

			add_post_meta( $this->post_id, '_manceppo_' . $name, $value, true ) || update_post_meta( $this->post_id, '_manceppo_' . $name, $value );
		}
	}
}
