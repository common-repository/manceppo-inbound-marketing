<?php
/**
 * File class-manceppo-html-utils.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Helper class for download form HTML functionality.
 */
class Manceppo_Html_Utils {

	/**
	 * List of allowed html tags to be used in the fields that can have HTML text.
	 */
	const ALLOW_HTML_TAGS = array(
		'h1'   => array(
			'style' => array(),
			'class' => array(),
		),
		'h2'   => array(
			'style' => array(),
			'class' => array(),
		),
		'h3'   => array(
			'style' => array(),
			'class' => array(),
		),
		'h4'   => array(
			'style' => array(),
			'class' => array(),
		),
		'p'    => array(
			'style' => array(),
			'class' => array(),
		),
		'div'  => array(
			'style' => array(),
			'class' => array(),
		),
		'span' => array(
			'style' => array(),
			'class' => array(),
		),
		'b'    => array(
			'style' => array(),
			'class' => array(),
		),
		'i'    => array(
			'style' => array(),
			'class' => array(),
		),
		'a'    => array(
			'href'   => array(),
			'target' => array(),
			'style'  => array(),
			'class'  => array(),
		),
	);

	/**
	 * The form to render.
	 *
	 * @var int
	 */
	private $post_id;
	/**
	 * Unique id of the form.
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Manceppo_Html_Utils constructor.
	 *
	 * @param int    $post_id The id of the form to render.
	 * @param string $prefix  Unique id of the form.
	 */
	public function __construct( $post_id, $prefix ) {
		$this->post_id = $post_id;
		$this->prefix  = $prefix;
	}

	/**
	 * Creates and styles the form submit button.
	 *
	 * @return string HTML button snippet.
	 */
	public function get_submit_button_html() {
		$button_color      = get_post_meta( $this->post_id, '_manceppo_submit_button_color', true );
		$button_title      = get_post_meta( $this->post_id, '_manceppo_submit_button_title', true );
		$button_text_color = get_post_meta( $this->post_id, '_manceppo_submit_button_text_color', true );

		$builder = new Manceppo_Html_Input_Builder( 'input' );

		return $builder->style( 'background-color', $button_color )
			->type( 'submit' )
			->style( 'color', $button_text_color )
			->value( $button_title, 'Submit' )
			->input_id( $this->prefix . 'submit' )
			->build();
	}

	/**
	 * Creates and styles the message box.
	 *
	 * @param string       $prefix         the unique form id (FIXME Remove this param).
	 * @param string|null  $label_position field label position.
	 * @param numeric|null $colspan        colspan.
	 *
	 * @return string HTML snippet.
	 */
	public function get_message_field_html( $prefix, $label_position = 'top', $colspan = 1 ) {
		$height = get_post_meta( $this->post_id, '_manceppo_message_field_height', true );
		$width  = get_post_meta( $this->post_id, '_manceppo_message_field_width', true );
		$label  = get_post_meta( $this->post_id, '_manceppo_message_field_label', true );

		if ( empty( $label ) ) {
			$label = 'Comments (optional)';
		}

		$builder = new Manceppo_Html_Input_Builder( 'textarea' );
		$builder->input_id( $prefix . 'manceppo_message' )->closing_element( true )->attr( 'maxlength', '300' );

		if ( ! empty( $height ) ) {
			$builder->style( 'height', $height . 'px' );
		}

		if ( ! empty( $width ) ) {
			$builder->style( 'width', $width . 'px' );
		}

		if ( 'in' === $label_position ) {
			$builder->placeholder( $label );
		}

		$title = '<span>' . $label . '</span>';
		$html  = $builder->build();

		if ( 'in' === $label_position ) {
			return '<td class="mcp_ff" colspan="' . $colspan . '">' . $html . '</td>';
		}

		return '<td class="mcp_ff" colspan="' . $colspan . '">' . $title . '<br>' . $html . '</td>';
	}

	/**
	 * Creates and styles the newsletter checkboxes of the download form.
	 *
	 * @param array| null $newsletters list of newsletter items.
	 * @param string      $prefix      the unique form id (FIXME Remove this param).
	 *
	 * @return array tuple HTML button snippet.
	 */
	public function get_newsletter_html( $newsletters, $prefix ) {
		if ( $newsletters ) {
			$html        = '';
			$auto_fields = '';
			$css_class   = esc_attr( $prefix . 'manceppo_news_letter' );
			foreach ( $newsletters as $list_id => $list_value ) {
				if ( isset( $list_value['mode'] ) && ! empty( $list_value['mode'] ) ) {
					$html_id         = $prefix . 'manceppo_list_' . $list_id;
					$auto_subscribed = isset( $list_value['auto'] ) ? $list_value['auto'] : '';
					if ( empty( $auto_subscribed ) ) {
						$list_checked = $list_value['required'] ? 'checked="checked"' : '';
						$list_title   = empty( $list_value['label'] ) ? $list_value['title'] : $list_value['label'];

						$html .= '<tr>';
						$html .= '<td><input ' . $list_checked . ' id="' . esc_attr( $html_id ) . '" class="' . $css_class . '" type="checkbox" value="' . esc_attr( $list_id ) . '"></td>';
						$html .= '<td><label for="' . esc_attr( $html_id ) . '">' . esc_html( $list_title ) . '</label></td>';
						$html .= '</tr>';
					} else {
						$auto_fields .= '<input type="hidden" id="' . esc_attr( $html_id ) . '" value="' . esc_attr( $list_id ) . '">';
					}
				}
			}

			if ( ! empty( $html ) ) {
				$html = '<table class="manceppo_news_letters">' . $html . '</table>';
			}

			return array( $html, $auto_fields );
		}

		return array( '', '' );
	}

	/**
	 * Wrapper around function <pre>wp_kses</pre>.
	 *
	 * @param string $html The html to filter.
	 *
	 * @return string the filtered html text.
	 */
	public function filter_html( $html ) {
		return wp_kses( $html, self::ALLOW_HTML_TAGS );
	}


	/**
	 * Creates inline css for amongst others the field spacing.
	 *
	 * @return string
	 */
	public function get_inline_css() {
		$html  = '<style>';
		$html .= '#manceppo_download_holder_' . esc_attr( $this->prefix ) . ' td.mcp_ff { ';

		foreach ( array( 'top', 'left', 'right', 'bottom' ) as $type ) {
			$value = get_post_meta( $this->post_id, '_manceppo_space_' . $type, true );
			if ( ! empty( $value ) ) {
				$html .= 'padding-' . $type . ':' . esc_html( $value ) . 'px; ';
			}
		}

		return $html . '}</style>';
	}
}
