<?php
/**
 * The actual HTML download form.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * This generates the form that provides the download.
 *
 * @param array $args Function arguments containing the post id.
 *
 * @return mixed the form as HTML.
 */
function manceppo_download_form( $args ) {
	Manceppo_Logger::log( 'DEBUG manceppo::download - preparing download form' );

	$post_id = (int) $args['id'];
	$nonce   = wp_create_nonce( 'wp_rest' );

	ob_start();

	$form_fields = new Manceppo_Form_Fields( $post_id, Manceppo_Options::is_debug_enabled() );
	$prefix      = wp_rand() . '_';
	$fields      = $form_fields->get( 'fields' );
	if ( ! is_array( $fields ) ) {
		$fields = array();
	}

	$show_recaptcha = $form_fields->get( 'show_recaptcha' );
	$show_recaptcha = ! empty( $show_recaptcha ) && 'show' === $show_recaptcha;
	if ( $show_recaptcha ) {
		wp_enqueue_script( 'manceppo_download_captcha_js', 'https://www.google.com/recaptcha/api.js?render=' . Manceppo_Options::get_recaptcha_key(), array(), '1.0.0', true );
	}

	$manceppo_html_utils = new Manceppo_Html_Utils( $post_id, $prefix );
	$form_css            = 'manceppo_api_form manceppo_api_form_no_border';
	$show_message_box    = 'show' === $form_fields->get( 'show_message_field' );

	?>

	<div class="manceppo_download_holder" id="manceppo_download_holder_<?php echo esc_attr( $prefix ); ?>">

		<div id="<?php echo esc_attr( $prefix . 'manceppo_api_form' ); ?>" class="<?php echo esc_attr( $form_css ); ?>">

			<?php

			$form_intro = $form_fields->get( 'form_intro' );
			if ( empty( $form_intro ) ) {
				echo '<p>', 'In order to get the download link, please fill the form below.', '</p>';
			} else {
				echo '<p>', $manceppo_html_utils->filter_html( $form_intro ), '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>

			<form onsubmit="return false;">
				<input type="hidden" id="<?php echo esc_attr( $prefix . 'manceppo_form_id' ); ?>"
					value="<?php echo esc_attr( $post_id ); ?>">
				<input type="hidden" id="<?php echo esc_attr( $prefix . 'manceppo_campaign' ); ?>"
					value="<?php echo esc_attr( $form_fields->get( 'campaign' ) ); ?>">
				<input type="hidden" id="<?php echo esc_attr( $prefix . 'manceppo_download' ); ?>"
					value="<?php echo esc_attr( $form_fields->get( 'download' ) ); ?>">
				<input type="hidden" id="<?php echo esc_attr( $prefix . 'visitorId' ); ?>" class="manceppo_visitor_id">
				<input type="hidden" id="<?php echo esc_attr( $prefix . 'postUrl' ); ?>"
					value="<?php echo esc_attr( get_site_url() ); ?>">

				<?php

				$manceppo_label_position = $form_fields->get( 'label_position' );
				$manceppo_column_count   = $form_fields->get( 'column_count' );
				$manceppo_column_count   = ctype_digit( $manceppo_column_count ) ? intval( $manceppo_column_count ) : 1;
				$manceppo_colspan        = ( 'before' === $manceppo_label_position ? 2 : 1 ) * $manceppo_column_count;
				$manceppo_cookie         = new Manceppo_Cookie();
				$manceppo_fields         = Manceppo_Fields::get_instance()->get_fields();

				echo '<table class="manceppo_table"><tr>';
				$idx = 0;
				foreach ( $manceppo_fields as $manceppo_field ) {
					if ( isset( $fields[ $manceppo_field->get_name() ] ) ) {
						echo $manceppo_field->to_download_form_html( $prefix, $form_fields->get( 'input_text_color' ), $manceppo_cookie, $fields, $manceppo_label_position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$idx++;
						if ( 1 === $manceppo_column_count || ( $manceppo_column_count > 1 && ( 0 === $idx % $manceppo_column_count ) ) ) {
							echo '</tr><tr>';
						}
					}
				}
				echo '</tr>';

				if ( $show_message_box ) {
					echo '<tr>', $manceppo_html_utils->get_message_field_html( $prefix, $manceppo_label_position, $manceppo_colspan ), '</tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				list($newsletter_html, $newsletter_hidden_html) = $manceppo_html_utils->get_newsletter_html( $form_fields->get_newsletters(), $prefix );
				if ( ! empty( $newsletter_html ) ) {
					echo '<tr><td colspan="' . esc_attr( $manceppo_colspan ) . '">', $newsletter_html, '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				if ( ! empty( $form_fields->get( 'submit_button_intro' ) ) ) {
					echo '<tr><td colspan="' . esc_attr( $manceppo_colspan ) . '">';
					echo $manceppo_html_utils->filter_html( $form_fields->get( 'submit_button_intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '</td></tr>';
				}

				echo '<tr><td class="manceppo_submit_botton_col" colspan="' . esc_attr( $manceppo_colspan ) . '">', $manceppo_html_utils->get_submit_button_html(), '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				echo '</table>';

				if ( ! empty( $newsletter_hidden_html ) ) {
					echo $newsletter_hidden_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				?>
				<input type="hidden" class="manceppo_init_form" id="<?php echo esc_attr( $prefix ); ?>"
					data-nonce="<?php echo esc_attr( $nonce ); ?>"
					data-token="<?php echo esc_attr( Manceppo_Options::get_recaptcha_key() ); ?>">
			</form>
		</div>
		<div id="<?php echo esc_attr( $prefix . 'manceppo_api_form_thankyou' ); ?>"
			class="manceppo_api_form_thankyou manceppo_hidden">
			<p>
				<?php echo $manceppo_html_utils->filter_html( $form_fields->get( 'thanks_body' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		</div>
	</div>

	<?php echo $manceppo_html_utils->get_inline_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php
	$additional_css = $form_fields->get( 'additional_css' );
	if ( '' !== $additional_css ) :
		?>
		<style type="text/css">
			<?php echo esc_html( $additional_css ); ?>
		</style>
		<?php
	endif;

	$data = ob_get_contents();

	ob_end_clean();

	return $data;
}
