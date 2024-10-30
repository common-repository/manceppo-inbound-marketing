<?php
/**
 * Manceppo account and connection form.
 *
 * @package manceppo
 */

namespace manceppo;

?>

<div class="wrap">

	<h1>Connection</h1>

	<?php

	if ( isset( $_POST['manceppo_admin_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['manceppo_admin_wpnonce'] ), 'manceppo_connection_settings' ) ) :
		$manceppo_api_key = Manceppo_Request_Utils::get_request_value( $_POST, Manceppo_Options::MANCEPPO_API_KEY );
		$manceppo_api_url = Manceppo_Request_Utils::get_request_value( $_POST, Manceppo_Options::MANCEPPO_API_URL_KEY );

		if ( null === $manceppo_api_key || null === $manceppo_api_url ) :
			?>

			<div id="setting-error-invalid_fields" class="error settings-error notice is-dismissible">
				<p>
					<strong>Not all required fields are filled in. Please try again</strong>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>

			<?php
		else :
			Manceppo_Options::set_api_url( $manceppo_api_url );
			Manceppo_Options::save_options( $_POST );

			if ( Manceppo_Options::SECRET_IS_SAVED !== $manceppo_api_key ) :
				$manceppo_key_check_response_code = Manceppo_Api::get_instance()->verify_api_key( $manceppo_api_key );
				if ( 200 === $manceppo_key_check_response_code ) :
					Manceppo_Options::set_api_key( $manceppo_api_key );
					?>

					<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
						<p>
							<strong>Connection successful!</strong>
						</p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text">Dismiss this notice.</span>
						</button>
					</div>

				<?php elseif ( ( 401 === $manceppo_key_check_response_code ) || ( 400 === $manceppo_key_check_response_code ) ) : ?>

					<div id="setting-error-invalid_admin_email" class="error settings-error notice is-dismissible">
						<p>
							<strong>The api key entered does not appear to be valid. Please try again.</strong>
						</p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text">Dismiss this notice.</span>
						</button>
					</div>

				<?php else : ?>

					<div id="setting-error-no_respond" class="error settings-error notice is-dismissible">
						<p>
							<strong>It appears that the Manceppo server is not responding. Please, check your connection url
								and try again.</strong>
						</p>
						<button type="button" class="notice-dismiss">
							<span class="screen-reader-text">Dismiss this notice.</span>
						</button>
					</div>

				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>

	<div class="manceppo_small_text">
		<p>Did you create a campaign? And have you added a download in your Manceppo account?</p>
		<p>Now you can start adding these downloads to any WordPress page using the rich text editor and start
			generating leads!</p>
		<p>To get started, add a download form. You can add this form to any page.</p>
	</div>

	<h2>Please connect your Manceppo plugin with your Manceppo account before adding 'download forms' to your site.</h2>

	<p>Use your Manceppo account credentials below to connect your WordPress website to your Manceppo account.</p>
	<p>Don't have a Manceppo account yet? Please register here: <a href="https://app.manceppo.com" target=_blank>https://app.manceppo.com</a></p>

	<form action="https://app.manceppo.com/registration/registerForm" method="get" target="_blank">
		<input name="submit_register" id="submit_register" class="button button-primary" value="Register" type="submit">
	</form>

	<form method="post" action="">
		<?php wp_nonce_field( 'manceppo_connection_settings', 'manceppo_admin_wpnonce' ); ?>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="manceppo_url">Manceppo API URL</label>*</th>
				<td>
					<input name="manceppo_url" id="manceppo_url" class="regular-text"
						required="required" type="url"
						value="<?php echo esc_attr( Manceppo_Options::get_api_url() ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="manceppo_api_key">Manceppo API Key</label>*
				</th>
				<td>
					<input name="manceppo_api_key" id="manceppo_api_key" class="regular-text" required="required"
						type="text"
						value="<?php echo esc_attr( Manceppo_Options::get_api_key( true ) ); ?>">

					<p><i>You can find your Manceppo API Key <a href="https://app.manceppo.com/wordpress/index" target="_blank">here</a></i></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="manceppo_css">Plugin CSS</label></th>
				<td>
					<input type="checkbox" name="manceppo_css"
						id="manceppo_css" <?php echo Manceppo_Options::use_custom_css() ? ' checked' : ''; ?> >
					<label for="manceppo_css">Disable built-in plugin styles</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="manceppo_recaptcha_key">reCaptcha site key</label></th>
				<td>
					<input name="manceppo_recaptcha_key" id="manceppo_recaptcha_key" class="regular-text" type="text"
						value="<?php echo esc_attr( Manceppo_Options::get_recaptcha_key() ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="manceppo_recaptcha_secret">reCaptcha secret key</label></th>
				<td>
					<input name="manceppo_recaptcha_secret"
						id="manceppo_recaptcha_secret" class="regular-text"
						type="text"
						value="<?php echo esc_attr( Manceppo_Options::get_recaptcha_secret( true ) ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="manceppo_enable_cookies">Use cookie for download forms</label></th>
				<td>
					<input name="manceppo_enable_cookies" id="manceppo_enable_cookies" type="checkbox"
						<?php echo Manceppo_Options::is_cookies_enabled() ? ' checked' : ''; ?> >
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="manceppo_enable_debug">(Development) Enable debug logging</label></th>
				<td>
					<input name="manceppo_enable_debug" id="manceppo_enable_debug" type="checkbox"
						<?php echo Manceppo_Options::is_debug_enabled() ? ' checked' : ''; ?> >
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit">
		</p>
	</form>
</div>
