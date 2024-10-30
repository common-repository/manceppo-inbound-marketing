<?php
/**
 * Form to synchronize the projects/campaigns from Manceppo.
 *
 * @package manceppo
 */

namespace manceppo;

?>

<div class="wrap">

	<h1>Synchronization</h1>

	<div class="manceppo_small_text">
		<p>Did you update one or more campaigns in Manceppo? Make sure to synchronize
			the changes for the published download forms.</p>
	</div>

	<?php

	if ( isset( $_POST['manceppo_admin_wpnonce'] )
		&& wp_verify_nonce( sanitize_key( $_POST['manceppo_admin_wpnonce'] ), 'manceppo_sync_settings' ) ) :
		$manceppo_form_count = Manceppo_Admin::update_download_forms();

		if ( $manceppo_form_count > 0 ) :
			?>

			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p>
					<strong>Synchronization complete, total forms
						updated: <?php echo esc_html( $manceppo_form_count ); ?></strong>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>

		<?php else : ?>

			<div id="setting-error-no_respond" class="error settings-error notice is-dismissible">
				<p>
					<strong>No forms found to update</strong>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>

		<?php endif; ?>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'manceppo_sync_settings', 'manceppo_admin_wpnonce' ); ?>
		<p class="submit">
			<input name="submit" id="submit" class="button button-primary" value="Sync campaigns" type="submit">
		</p>
	</form>

</div>

