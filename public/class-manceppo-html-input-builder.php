<?php
/**
 * File class-manceppo-html-input-builder.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Convenience class for writing HTML input elements.
 * <p>
 * Currently only verified support for HTML element <tt>input</tt> and <tt>select</tt>.
 */
class Manceppo_Html_Input_Builder {


	/**
	 * Style array.
	 *
	 * @var array
	 */
	private $style;
	/**
	 * Text on the button or in text input.
	 *
	 * @var string
	 */
	private $value;
	/**
	 * The element id.
	 *
	 * @var string
	 */
	private $input_id;
	/**
	 * The input type.
	 *
	 * @var string
	 */
	private $type;
	/**
	 * The element type.
	 *
	 * @var string
	 */
	private $element;
	/**
	 * The CSS classes array.
	 *
	 * @var array
	 */
	private $css_class;
	/**
	 * Optional placeholder value.
	 *
	 * @var string
	 */
	private $placeholder_value;
	/**
	 * Determines if field is required.
	 *
	 * @var boolean
	 */
	private $is_required;
	/**
	 * List of select options when type is select.
	 *
	 * @var array
	 */
	private $select_options;

	/**
	 * List of select arbitrary attributes.
	 *
	 * @var array
	 */
	private $attrs;

	/**
	 * Determines if field is has closing element.
	 *
	 * @var boolean
	 */
	private $has_closing_element;

	/**
	 * Manceppo_Html_Input_Builder constructor.
	 *
	 * @param string $element the html element name.
	 */
	public function __construct( $element ) {
		$this->element        = $element;
		$this->style          = array();
		$this->css_class      = array();
		$this->select_options = array();
		$this->attrs          = array();
	}

	/**
	 * Sets the button value.
	 *
	 * @param string $value       the button text.
	 * @param string $alternative alternative text if <tt>$value</tt> is empty.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function value( $value, $alternative = null ) {
		if ( empty( $value ) ) {
			$this->value = $alternative;
		} else {
			$this->value = $value;
		}

		return $this;
	}

	/**
	 * Sets the element type.
	 *
	 * @param string $type the input type.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function type( $type ) {
		$this->type = $type;

		return $this;
	}

	/**
	 * Adds a CSS style value.
	 *
	 * @param string $name  the CSS name.
	 * @param string $value the value to set.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function style( $name, $value ) {
		if ( ! empty( $value ) ) {
			array_push( $this->style, $name . ':' . esc_attr( $value ) );
		}

		return $this;
	}

	/**
	 * Adds a select option.
	 *
	 * @param string $value the option value.
	 * @param string $label the option label to set.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function option( $value, $label ) {
		$this->select_options[ $value ] = $label;

		return $this;
	}

	/**
	 * Adds an arbitrary attribute.
	 *
	 * @param string $name  the attribute name.
	 * @param string $value the attribute value to set.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function attr( $name, $value ) {
		$this->attrs[ $name ] = $value;

		return $this;
	}

	/**
	 * Adds a CSS class value.
	 *
	 * @param string $name the CSS name.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function css_class( $name ) {
		if ( ! empty( $name ) ) {
			array_push( $this->css_class, esc_attr( $name ) );
		}

		return $this;
	}

	/**
	 * Sets the HTML element id.
	 *
	 * @param string $input_id the element id.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function input_id( $input_id ) {
		$this->input_id = $input_id;

		return $this;
	}

	/**
	 * Add placeholder value.
	 *
	 * @param string $placeholder_value the optional value.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function placeholder( $placeholder_value ) {
		$this->placeholder_value = $placeholder_value;

		return $this;
	}

	/**
	 * Determines if a field is required.
	 *
	 * @param bool $is_required the required flag.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function required( $is_required ) {
		$this->is_required = $is_required;

		return $this;
	}

	/**
	 * Determines if the input has a closing element.
	 *
	 * @param bool $has_closing_element the flag.
	 *
	 * @return Manceppo_Html_Input_Builder
	 */
	public function closing_element( $has_closing_element ) {
		$this->has_closing_element = $has_closing_element;

		return $this;
	}

	/**
	 * Constructs the HTML.
	 *
	 * @return string with the button HTML.
	 */
	public function build() {
		$vals = array(
			'id="' . esc_attr( $this->input_id ) . '"',
		);

		if ( ! empty( $this->style ) ) {
			array_push( $vals, 'style="' . join( ';', $this->style ) . ';"' );
		}

		if ( ! empty( $this->css_class ) ) {
			array_push( $vals, 'class="' . join( ' ', $this->css_class ) . '"' );
		}

		if ( ! empty( $this->placeholder_value ) ) {
			array_push( $vals, 'placeholder="' . esc_attr( $this->placeholder_value ) . '"' );
		}

		if ( $this->is_required ) {
			array_push( $vals, 'required="required"' );
		}

		if ( 'select' === $this->element ) {
			$tag = '<select ' . join( ' ', $vals ) . '><option></option>';
			foreach ( $this->select_options as $value => $label ) {
				$tag .= '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
			}

			return $tag . '</select>';
		}

		if ( ! empty( $this->type ) ) {
			array_push( $vals, 'type="' . esc_attr( $this->type ) . '"' );
		}

		if ( ! empty( $this->value ) ) {
			array_push( $vals, 'value="' . esc_attr( $this->value ) . '"' );
		}

		foreach ( $this->attrs as $name => $value ) {
			array_push( $vals, $name . '="' . esc_attr( $value ) . '"' );
		}

		$html = '<' . $this->element . ' ' . join( ' ', $vals ) . '>';
		if ( $this->has_closing_element ) {
			$html .= '</' . $this->element . '>';
		}
		return $html;
	}
}
