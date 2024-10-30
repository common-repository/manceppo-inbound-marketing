<?php
/**
 * Adds the Manceppo form button to the WordPress page editor.
 *
 * @package manceppo
 */

namespace manceppo;

?>

<button type="button" id="manceppo-button" class="button" data-editor="content">
	<img src="<?php echo esc_url( MANCEPPO_PLUGIN_URL . 'admin/images/manceppo.png' ); ?>" alt="Manceppo">Add Download
</button>
<style type="text/css">

	#manceppo-button {
		padding: 0 5px;
	}

	#manceppo-button img {
		height: 20px;
		width: 20px;
		display: inline-block;
		padding: 0;
		vertical-align: middle;
		margin-top: -3px;
		margin-right: 0.5em;
	}
</style>
<script>
	jQuery( '#manceppo-button' ).click( function( e ) {
		//trigger the tiny Manceppo icon button
		jQuery( '.mce-manceppo_shortcode button' ).trigger( 'click' );
		e.preventDefault();
	} )
</script>
