/* globals jQuery */

( function () {
	function toggleSelect( selector ) {
		jQuery( `select[name=manceppo_${ selector }]` ).change( function () {
			jQuery( `input#manceppo_${ selector }_select` )
				.attr( 'disabled', false )
				.click( function () {
					if ( selector === 'account' ) {
						jQuery( 'select[name=manceppo_campaign]' ).val( '' );
						jQuery( 'select[name=manceppo_download]' ).val( '' );
						// prettier-ignore
						jQuery( 'input[type=radio].manceppo_newsletter_choice' ).prop( 'checked', false );
					}
					jQuery( 'input#manceppo_publish_cmd' ).click();
				} );
		} );
	}

	jQuery( document ).ready( function () {
		toggleSelect( 'campaign' );
		toggleSelect( 'account' );
	} );
} )();
