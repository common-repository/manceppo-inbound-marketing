<?php
/**
 * File class-manceppo-field.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Class ManceppoField
 */
class Manceppo_Field {

	/**
	 * Required flag.
	 *
	 * @var bool
	 */
	private $is_required;
	/**
	 * The field type.
	 *
	 * @var string
	 */
	private $type;
	/**
	 * Name used in json.
	 *
	 * @var string
	 */
	private $json_name;
	/**
	 * Field internal name.
	 *
	 * @var string
	 */
	private $name;
	/**
	 * Field display name.
	 *
	 * @var string
	 */
	private $label;
	/**
	 * List of option values.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * ManceppoField constructor.
	 *
	 * @param string $name        Name of the field.
	 * @param string $json_name   Name used in JSON.
	 * @param string $label       Field label.
	 * @param string $type        The type of the field.
	 * @param bool   $is_required Indicates if the field is required.
	 * @param array  $options     Optional list of options.
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function __construct( $name, $json_name, $label, $type = 'text', $is_required = false, array $options = null ) {
		$this->is_required = $is_required;
		$this->type        = $type;
		$this->json_name   = $json_name;
		$this->name        = $name;
		$this->label       = $label;
		$this->options     = $options;
	}

	/**
	 * Indicates if the field is required.
	 *
	 * @return bool
	 */
	public function is_required() {
		return $this->is_required;
	}

	/**
	 * Gets the field type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Gets the json name as used to send request to Manceppo.
	 *
	 * @return string
	 */
	public function get_json_name() {
		return $this->json_name;
	}

	/**
	 * Gets the field name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Gets the display label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get a list of option values.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Renders this field for usage as HTML checkbox in the edit download form.
	 *
	 * @param array $field_values list of stored field values.
	 *
	 * @return string
	 */
	public function to_checkbox_html( $field_values ) {
		$name  = esc_attr( 'manceppo_fields_' . $this->get_name() );
		$label = esc_html( $this->get_label() );
		$value = isset( $field_values[ $this->get_name() ] ) && $field_values[ $this->get_name() ];

		$attrs = array(
			'id="' . $name . '"',
			'name="' . $name . '"',
			$this->is_required() ? 'required="required"' : '',
			( $this->is_required() || $value ) ? 'checked="checked"' : '',
		);

		return '<input type="checkbox" ' . join( ' ', $attrs ) . '><label for="' . $name . '">' . $label . '</label>';
	}

	/**
	 * Renders this field label for usage as HTML text field in the edit download form.
	 *
	 * @param array $field_values list of stored field values.
	 *
	 * @return string
	 */
	public function to_label_input( $field_values ) {
		$name  = esc_attr( 'manceppo_fields_' . $this->get_name() );
		$attrs = array(
			'id="' . $name . '_label"',
			'name="' . $name . '_label"',
			'value="' . esc_html( $this->get_field_label( $field_values ) ) . '"',
		);

		return '<input type="text" ' . join( ' ', $attrs ) . '>';
	}

	/**
	 * Renders this field for usage as HTML input in the download form.
	 *
	 * @param string          $prefix           the unique form id.
	 * @param string|null     $input_text_color optional text color style.
	 * @param Manceppo_Cookie $manceppo_cookie  cookie resolver for pre-fill values.
	 * @param array|null      $field_values     store field values.
	 * @param string|null     $label_position   field label position.
	 *
	 * @return string
	 */
	public function to_download_form_html( $prefix, $input_text_color, $manceppo_cookie, $field_values = array(), $label_position = 'top' ) {
		$type = $this->get_type();
		$name = $prefix . 'manceppo_fields_' . $this->get_name();

		$title       = '';
		$placeholder = '';
		if ( 'in' === $label_position ) {
			$placeholder = ' placeholder="' . esc_attr( $this->get_field_label( $field_values ) ) . '" ';
		} else {
			$title = '<span>' . esc_html( $this->get_field_label( $field_values ) ) . ( $this->is_required() ? '<b style="color:red">*</b>' : '' ) . '</span>';
		}

		$attrs = array(
			'id="' . esc_attr( $name ) . '"',
			$this->is_required() ? 'required="required"' : '',
			empty( $input_text_color ) ? '' : 'style="color:' . esc_attr( $input_text_color ) . '"',
		);

		if ( 'select' === $type ) {
			$input = '<select ' . join( ' ', $attrs ) . $placeholder . '>';
			foreach ( $this->get_options() as $option ) {
				$input .= '<option ' . ( 'unknown' === $option ? 'selected="selected"' : '' ) . '>' . esc_html( $option ) . '</option>';
			}
			$input .= '</select>';
		} else {
			$input = '<input type="' . esc_attr( $type ) . '" ' . join( ' ', $attrs ) . $placeholder . ' value="' . esc_attr( $manceppo_cookie->get_field_value( $this->get_json_name() ) ) . '">';
		}

		if ( 'before' === $label_position ) {
			return '<td class="mcp_ff">' . $title . '</td><td class="mcp_ff">' . $input . '</td>';
		} elseif ( 'in' === $label_position ) {
			return '<td class="mcp_ff">' . $input . '</td>';
		}

		return '<td class="mcp_ff">' . $title . '<br>' . $input . '</td>';
	}

	/**
	 * Gets a saved label or the default.
	 *
	 * @param array|null $field_values store field values.
	 *
	 * @return mixed|string
	 */
	public function get_field_label( $field_values ) {
		$field = $this->get_name() . '_label';
		if ( isset( $field_values[ $field ] ) && ! empty( $field_values[ $field ] ) ) {
			return $field_values[ $field ];
		}

		return $this->get_label();
	}
}
