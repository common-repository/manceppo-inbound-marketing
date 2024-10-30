<?php
/**
 * Download form editor.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Adds download form fields to the administrative interface.
 *
 * @param string $post_type name of the post type to check.
 */
function admin_add_download_fields( $post_type ) {
	if ( 'manceppo_download' === $post_type ) {
		add_meta_box(
			'manceppo_plugin-fields-meta-box', // HTML 'id' attribute of the edit screen section.
			__( 'Options', 'manceppo' ),              // Title of the edit screen section, visible to user.
			'manceppo\admin_download_fields_editor', // Function that prints out the HTML for the edit screen section.
			$post_type,          // The type of Write screen on which to show the edit screen section.
			'advanced',          // The part of the page where the edit screen section should be shown.  'normal', 'side', and 'advanced'.
			'high'               // The priority within the context where the boxes should show. 'high', 'low'.
		);
	}
}

/**
 * Print either campaign or downloadable selector.
 *
 * @param string $type    the type: campaign or downloadable.
 * @param string $type_id the id of the (previous) selected campaign or downloadable.
 *
 * @return bool indicates if retrieving the information was successful.
 */
function admin_print_selector( $type, $type_id ) {
	$response = 'campaign' === $type ? Manceppo_Api::get_instance()->get_campaigns() : Manceppo_Api::get_instance()
		->get_downloads();
	if ( $response->is_successful() ) : ?>
		<div>
			<select name="manceppo_<?php echo esc_attr( $type ); ?>">
				<?php

				if ( 'download' === $type ) {
					echo '<optgroup label="Downloads">';
					$match_found = false;
					foreach ( $response->get_content() as $element ) {
						if ( ! property_exists( $element, 'type' ) || empty( $element->type ) || 'DOCUMENT' === $element->type ) {
							echo '<option value="', esc_attr( $element->id ), '" ', ( strval( $element->id ) === $type_id ? 'selected' : '' ), '>', esc_html( $element->title ), '</option>';
							$match_found = $match_found || strval( $element->id ) === $type_id;
						}
					}
					echo '</optgroup><optgroup label="Links">';
					foreach ( $response->get_content() as $element ) {
						if ( property_exists( $element, 'type' ) && 'LINK' === $element->type ) {
							echo '<option value="', esc_attr( $element->id ), '" ', ( strval( $element->id ) === $type_id ? 'selected' : '' ), '>', esc_html( $element->title ), '</option>';
							$match_found = $match_found || strval( $element->id ) === $type_id;
						}
					}
					echo '</optgroup><optgroup label="Other"><option value="no-download" ', ( 'no-download' === $type_id || false === $match_found ? 'selected' : '' ), '>No Download or Link</option></optgroup>';
				} else {
					echo '<option value=""></option>';
					foreach ( $response->get_content() as $element ) {
						echo '<option value="', esc_attr( $element->id ), '" ', ( strval( $element->id ) === $type_id ? 'selected' : '' ), '>', esc_html( $element->title ), '</option>';
					}
				}
				?>
			</select>
			<?php
			if ( 'campaign' === $type ) {
				echo '<input type="button" class="button" id="manceppo_campaign_select" value="Select" disabled>';
			}
			?>
		</div>
		<?php
	elseif ( 404 === $response->get_code() ) :
		echo "<div><p>Sorry, but you don't have any ", esc_html( $type ), 's created. Please proceed to the Manceppo admin panel &amp; create at least one.</p></div>';
	elseif ( 401 === $response->get_code() ) :
		echo '<div><p>Sorry, but authorization has failed. Please, check the plugin options &amp; configure your Manceppo access.</p></div>';
	else :
		echo '<div><p>Communication with the Manceppo API failed. Please, check the plugin options &amp; configure your Manceppo access.</p></div>';
	endif;

	return $response->is_successful();
}

/**
 * Renders the admin download form.
 *
 * @param mixed $post the download form record.
 */
function admin_download_fields_editor( $post ) {
	wp_enqueue_script( 'manceppo_admin_download_edit_form_js', MANCEPPO_PLUGIN_URL . 'admin/js/download_edit_form.js', array( 'jquery' ), MANCEPPO_VERSION, true );

	$form_fields = new Manceppo_Form_Fields( $post->ID, Manceppo_Options::is_debug_enabled() );

	$campaign_id = $form_fields->get( 'campaign' );
	$download_id = $form_fields->get( 'download' );

	echo "<p>To create a form make sure at least one set of the 'Form Settings' is configured in your Manceppo account (located under CONVERSION), and make sure this plugin is connected.</p>";
	echo "<p>After hitting 'Publish' a shortcode will be created so you can add this form to any page.</p>";

	global $pagenow;
	if ( 'post-new.php' !== $pagenow ) {
		echo '<h3>The shortcode</h3><p>[manceppo_download id=', esc_html( $post->ID ), ']</p>';
	}

	echo '<h3><a href="https://app.manceppo.com/campaign/index" target="_blank">Form Settings</a></h3>', '<p>Select the \'Form Settings\' from your Manceppo account.</p>';
	$show_fields = admin_print_selector( 'campaign', $campaign_id );

	if ( $show_fields ) {
		echo '<h3><a href="https://app.manceppo.com/download/index" target="_blank">Downloads / Links</a></h3>';
		echo "<p>To add a 'Download / Link' from Manceppo to your 'Download / confirmation e-mail' select one here.</p>";
		$show_fields = admin_print_selector( 'download', $download_id );
	}

	if ( $show_fields ) :
		$campaign = null;
		if ( ! empty( $campaign_id ) ) {
			$response = Manceppo_Api::get_instance()->get_campaign( $campaign_id );
			if ( $response->is_successful() ) {
				$campaign = $response->get_content();
			}
		}

		$thanks_body       = $form_fields->get( 'thanks_body' );
		$thanks_body_saved = ! empty( $thanks_body );

		if ( empty( $thanks_body ) && ! empty( $campaign ) && property_exists( $campaign, 'thankYouText' ) ) {
			$thanks_body = $campaign->{'thankYouText'};
			$thanks_body = empty( $thanks_body ) ? '' : $thanks_body;
		}

		if ( ! empty( $thanks_body ) && ! $thanks_body_saved ) {
			// make sure we save an initial value (even if edit form is not saved yet).
			$form_fields->update( 'thanks_body', $thanks_body );
		}
		?>

		<h3>Thank you message</h3>
		<div class="left mcp_row">
			<span style="font-size: larger"><i><?php echo esc_html( $thanks_body ); ?></i></span>
			<p>(The 'Thank you message' used in your form is derived from the 'Form Settings' you selected above. You can always edit the 'Thank you message’ in the Form Settings you have selected)</p>
		</div>

		<div style="clear: both;"></div>

		<h3>Form fields</h3>
		<div class="left mcp_row">
			<table>
				<tr>
					<th class="manceppo_field_head">Manceppo field</th>
					<th></th>
					<th class="manceppo_field_head">Form label</th>
					<th></th>
					<th class="manceppo_field_head">Manceppo field</th>
					<th></th>
					<th class="manceppo_field_head">Form label</th>
					<th></th>
				</tr>
				<tr>
					<?php
					$fields = $form_fields->get( 'fields' );
					if ( ! is_array( $fields ) ) {
						$fields = array();
					}
					$manceppo_fields = Manceppo_Fields::get_instance()->get_fields();
					foreach ( $manceppo_fields as $index => $manceppo_field ) {
						echo '<td>' . $manceppo_field->to_checkbox_html( $fields ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '<td>&#160;&#160;</td>';
						echo '<td>' . $manceppo_field->to_label_input( $fields ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '<td>&#160;&#160;</td>';

						if ( 1 === ( $index % 2 ) ) {
							echo '</tr><tr>';
						}
					}
					?>
				</tr>
			</table>
		</div>

		<div style="clear: both;"></div>

		<h3>Show field labels</h3>
		<?php $position = $form_fields->get( 'label_position' ); ?>
		<div class="left mcp_row">
			<table>
				<tr>
					<td><label for="manceppo_label_position_above"><span>Example</span><br><input size="15" type="text" disabled="disabled"></label></td>
					<td>&#160;</td>
					<td><label for="manceppo_label_position_before"><span>Example</span>&#160;<input size="15" type="text" disabled="disabled"></label></td>
					<td>&#160;</td>
					<td><label for="manceppo_label_position_in"><input type="text" size="15" placeholder="Example" disabled="disabled"></label></td>
				</tr>
				<tr><td></td></tr>
				<tr>
					<td><input type="radio" id="manceppo_label_position_above" name="manceppo_label_position" value="top" <?php echo ( empty( $position ) || 'top' === $position ) ? 'checked=checked' : ''; ?>><label for="manceppo_label_position_above">Above form fields</label></td>
					<td>&#160;</td>
					<td><input type="radio" id="manceppo_label_position_before" name="manceppo_label_position" value="before" <?php echo ( 'before' === $position ) ? 'checked=checked' : ''; ?>><label for="manceppo_label_position_before">Left to form fields</label></td>
					<td>&#160;</td>
					<td><input type="radio" id="manceppo_label_position_in" name="manceppo_label_position" value="in" <?php echo ( 'in' === $position ) ? 'checked=checked' : ''; ?>><label for="manceppo_label_position_in">In form fields</label></td>
				</tr>
			</table>
		</div>

		<div style="clear: both;"></div>

		<h3>Add spacing to form fields</h3>
		<div class="left mcp_row">
			<table id="manceppo_field_space">
				<tr>
					<td><label for="manceppo_space_top">Above</label></td>
					<td>
						<?php $manceppo_space_top = $form_fields->get( 'space_top' ); ?>
						<input type="text" size="4" value="<?php echo esc_attr( $manceppo_space_top ); ?>" name="manceppo_space_top"
							id="manceppo_space_top">
					</td>
					<td>px&#160;&#160;</td>
					<td><label for="manceppo_space_bottom">Below</label></td>
					<td>
						<?php $manceppo_space_bottom = $form_fields->get( 'space_bottom' ); ?>
						<input type="text" size="4" value="<?php echo esc_attr( $manceppo_space_bottom ); ?>" name="manceppo_space_bottom"
							id="manceppo_space_bottom">
					</td>
					<td>px&#160;&#160;</td>
					<td><label for="manceppo_space_left">Left</label></td>
					<td>
						<?php $manceppo_space_left = $form_fields->get( 'space_left' ); ?>
						<input type="text" size="4" value="<?php echo esc_attr( $manceppo_space_left ); ?>" name="manceppo_space_left"
							id="manceppo_space_left">
					</td>
					<td>px&#160;&#160;</td>
					<td><label for="manceppo_space_right">Right</label></td>
					<td>
						<?php $manceppo_space_right = $form_fields->get( 'space_right' ); ?>
						<input type="text" size="4" value="<?php echo esc_attr( $manceppo_space_right ); ?>" name="manceppo_space_right"
							id="manceppo_space_right">
					</td>
					<td>px</td>
				</tr>
			</table>
		</div>

		<div style="clear: both;"></div>

		<h3>Form column style</h3>
		<div class="left mcp_row">
			<?php $column_count = $form_fields->get( 'column_count' ); ?>
			<table>
				<tr>
					<td><label for="manceppo_column_count">Number of columns</label></td>
					<td>&#160;</td>
					<td colspan="2">
						<select name="manceppo_column_count" id="manceppo_column_count">
							<option value="1"
							<?php
							if ( empty( $column_count ) || '1' === $column_count ) {
								echo 'selected="selected"'; }
							?>
							>1</option>
							<option value="2"
							<?php
							if ( '2' === $column_count ) {
								echo 'selected="selected"'; }
							?>
							>2</option>
						</select>
					</td>
				</tr>
			</table>
		</div>

		<div style="clear: both;"></div>

		<h3>Add message field</h3>
		<div class="left mcp_row">
			<table>
				<tr>
					<td><input type="checkbox" name="manceppo_show_message_field" id="manceppo_show_message_field"
							value="show" <?php echo empty( $form_fields->get( 'show_message_field' ) ) ? '' : 'checked=checked'; ?>>
					</td>
					<td><label for="manceppo_show_message_field">Message field</label></td>
					<td>&#160;&#160;</td>
					<td>&#160;&#160;</td>
					<td><label for="manceppo_message_field_label">Form label</label></td>
					<td><input type="text" placeholder="Comments (optional)" value="<?php echo esc_attr( $form_fields->get( 'message_field_label' ) ); ?>"
							name="manceppo_message_field_label" id="manceppo_message_field_label">
					</td>
				</tr>
				<tr>
					<td>&#160;&#160;</td>
					<td><label for="manceppo_message_field_height">Field height</label></td>
					<td><input type="text" style="text-align: right" size="5"
							value="<?php echo esc_attr( $form_fields->get( 'message_field_height' ) ); ?>"
							name="manceppo_message_field_height" id="manceppo_message_field_height">px
					</td>
					<td>&#160;&#160;</td>
					<td><label for="manceppo_message_field_width">Field width</label></td>
					<td><input type="text" style="text-align: right" size="5"
							value="<?php echo esc_attr( $form_fields->get( 'message_field_width' ) ); ?>"
							name="manceppo_message_field_width" id="manceppo_message_field_width">px
					</td>
				</tr>
			</table>
		</div>

		<div style="clear: both;"></div>

		<h3>Download form intro</h3>
		<div class="left mcp_row">
			<textarea
				cols="80" rows="5"
				placeholder="Enter some text to show on top of your form, you can use basic HTML markup"
				name="manceppo_form_intro"
				id="manceppo_form_intro"><?php echo esc_textarea( $form_fields->get( 'form_intro' ) ); ?></textarea>
		</div>

		<div style="clear: both;"></div>

		<h3>Input text color</h3>
		<p>This defines the color of the text users fill in you form</p>
		<div class="left mcp_row">
			<?php $input_text_color = $form_fields->get( 'input_text_color' ); ?>
			<input type="text" value="<?php echo esc_attr( $input_text_color ); ?>" name="manceppo_input_text_color"
				id="manceppo_input_text_color">
			<input value="<?php echo esc_attr( $input_text_color ); ?>" type="color"
				onchange="setColor('manceppo_input_text_color', this.value)">
		</div>

		<div class="manceppo_divider">&#160;</div>

		<h3>Submit button intro</h3>
		<div class="left mcp_row">
			<textarea
				cols="80" rows="5"
				placeholder="Enter some text to show above the submit button, you can use basic HTML markup"
				name="manceppo_submit_button_intro"
				id="manceppo_submit_button_intro"><?php echo esc_textarea( $form_fields->get( 'submit_button_intro' ) ); ?></textarea>
		</div>

		<div style="clear: both;"></div>

		<h3>Submit button text</h3>
		<p>This button shows on the bottom of your opened form, clicking this button submits your form.</p>
		<div class="left mcp_row">
			<input type="text" value="<?php echo esc_attr( $form_fields->get( 'submit_button_title' ) ); ?>"
				placeholder="Submit"
				name="manceppo_submit_button_title"
				id="manceppo_submit_button_title">
		</div>

		<div style="clear: both;"></div>

		<h3>Submit button color</h3>
		<div class="left mcp_row">
			<?php $submit_color = $form_fields->get( 'submit_button_color' ); ?>
			<input type="text" value="<?php echo esc_attr( $submit_color ); ?>"
				name="manceppo_submit_button_color"
				id="manceppo_submit_button_color"><input
				value="<?php echo esc_attr( $submit_color ); ?>" type="color"
				onchange="setColor('manceppo_submit_button_color', this.value)">
		</div>

		<div style="clear: both;"></div>

		<h3>Submit button text color</h3>
		<div class="left mcp_row">
			<?php $submit_text_color = $form_fields->get( 'submit_button_text_color' ); ?>
			<input type="text" value="<?php echo esc_attr( $submit_text_color ); ?>"
				name="manceppo_submit_button_text_color"
				id="manceppo_submit_button_text_color"><input
				value="<?php echo esc_attr( $submit_text_color ); ?>" type="color"
				onchange="setColor('manceppo_submit_button_text_color', this.value)">
		</div>

		<div class="manceppo_divider">&#160;</div>

		<h3>Show reCaptcha</h3>
		<div class="left mcp_row">
			<input type="checkbox"
				value="show" <?php echo empty( $form_fields->get( 'show_recaptcha' ) ) ? '' : 'checked=checked'; ?>
				name="manceppo_show_recaptcha" id="manceppo_show_recaptcha">
			<label for="manceppo_show_recaptcha"><i>Show reCaptcha</i></label>
		</div>

		<div style="clear: both;"></div>

		<h3>Newsletters</h3>
		<div class="left">

			<?php

			if ( $campaign && property_exists( $campaign, 'newsletters' ) ) {
				$campaign_newsletters = $campaign->newsletters;
				$saved_newsletters    = get_post_meta( $post->ID, '_manceppo_newsletters', true );

				if ( ! empty( $campaign_newsletters ) ) {
					echo '<table class="manceppo_news_letters"><thead><th>Name</th><th>Label</th><th class="selection">Unchecked (checkbox)</th><th class="selection">Pre checked (checkbox)</th><th class="selection">Hidden <i>(in form)</i></th><th class="selection">No newsletter</th></thead>';

					foreach ( $campaign_newsletters as $campaign_newsletter ) {
						$list_id = $campaign_newsletter->id;

						$title   = $campaign_newsletter->name;
						$html_id = 'list_' . $list_id;
						$label   = '';

						if ( $saved_newsletters && isset( $saved_newsletters[ $list_id ] ) ) {
							$list_value       = $saved_newsletters[ $list_id ];
							$checked          = isset( $list_value['mode'] ) ? ( 'box' === $list_value['mode'] ) ? 'checked="checked"' : '' : '';
							$required_checked = $list_value['required'] ? 'checked="checked"' : '';
							$auto_checked     = $list_value['auto'] ? 'checked="checked"' : '';
							$label            = $list_value['label'];
						} else {
							$checked          = '';
							$required_checked = '';
							$auto_checked     = '';
						}
						$label     = empty( $label ) ? $title : $label;
						$unchecked = empty( $checked ) && empty( $required_checked ) && empty( $auto_checked ) ? 'checked="checked"' : '';

						?>

						<tr>
							<td>
								<label
									for="<?php echo esc_attr( $html_id ); ?>"><?php echo esc_html( $title ); ?></label>
							</td>
							<td class="selection">
								<input type="text"
									name="manceppo_newsletter_label_<?php echo esc_attr( $list_id ); ?>"
									id="manceppo_newsletter_label_<?php echo esc_attr( $list_id ); ?>"
									value="<?php echo esc_attr( $label ); ?>">
							</td>
							<td class="selection">
								<input type="radio" id="<?php echo esc_attr( $html_id ); ?>"
									class="manceppo_newsletter_choice"
									name="manceppo_newsletter_choice_<?php echo esc_attr( $list_id ); ?>" <?php echo esc_html( $checked ); ?>
									value="box">
								<input type="hidden" name="manceppo_newsletter_<?php echo esc_attr( $list_id ); ?>" value="<?php echo esc_attr( $title ); ?>">
								<input type="hidden" id="<?php echo esc_attr( $html_id ); ?>"
									name="manceppo_newsletters[]" value="<?php echo esc_attr( $list_id ); ?>">
							</td>
							<td class="selection">
								<input type="radio"
									class="manceppo_newsletter_choice"
									name="manceppo_newsletter_choice_<?php echo esc_attr( $list_id ); ?>"
									id="manceppo_newsletter_choice_<?php echo esc_attr( $list_id ); ?>" <?php echo esc_html( $required_checked ); ?>
									value="required">
							</td>
							<td class="selection">
								<input type="radio"
									class="manceppo_newsletter_choice"
									name="manceppo_newsletter_choice_<?php echo esc_attr( $list_id ); ?>"
									id="manceppo_newsletter_choice_<?php echo esc_attr( $list_id ); ?>" <?php echo esc_html( $auto_checked ); ?>
									value="auto">
							</td>
							<td class="selection">
								<input type="radio"
									name="manceppo_newsletter_choice_<?php echo esc_attr( $list_id ); ?>"
									class="manceppo_newsletter_choice" <?php echo esc_html( $unchecked ); ?>
									value="">
							</td>
						</tr>

						<?php
					}

					echo '</table>';
				} else {
					echo '<p>No campaign newsletters found</p>';
				}
			} else {
				echo '<p>Save the form first to retrieve the campaign newsletters</p>';
			}
			?>

		</div>

		<div style="clear: both;"></div>
		<div class="manceppo_additional_css">
			<h3>Custom CSS</h3>
			<textarea name="manceppo_additional_css"
				style="width:100%; height: 250px;"><?php echo esc_textarea( $form_fields->get( 'additional_css' ) ); ?></textarea>
		</div>

		<script type="application/javascript">
			function setColor( target, value ) {
				document.getElementById( target ).value = value;
			}
		</script>

		<?php
	endif;

	echo '<input name="save" type="submit" id="manceppo_publish_cmd" style="display: none">';

	wp_nonce_field( 'manceppo_edit_download', 'manceppo_admin_wpnonce' );
}

add_action( 'add_meta_boxes', 'manceppo\admin_add_download_fields' );

/**
 * Move download fields editor after the title
 */
function admin_plugin_move_subtitle() {
	global $post, $wp_meta_boxes;

	do_meta_boxes( get_current_screen(), 'advanced', $post );

	unset( $wp_meta_boxes['manceppo_download']['advanced'] );
	unset( $wp_meta_boxes['post']['advanced'] );
	unset( $wp_meta_boxes['page']['advanced'] );
}

add_action( 'edit_form_after_title', 'manceppo\admin_plugin_move_subtitle' );
