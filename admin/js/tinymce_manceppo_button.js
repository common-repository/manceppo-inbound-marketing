/* globals tinymce, downloadFormsData jQuery */

( function () {
	/*
	 * Register the Manceppo icon (button) on the visual editor and triggers the download popup.
	 */
	jQuery( document ).ready( function () {
		if ( typeof downloadFormsData === 'undefined' ) {
			return;
		}

		tinymce.PluginManager.add( 'manceppo_tiny_button', function ( editor ) {
			editor.addButton( 'manceppo_tiny_button', {
				icon: true,
				image: downloadFormsData.button_image,
				classes: 'manceppo_shortcode',
				onclick() {
					editor.windowManager.open( {
						title: downloadFormsData.button_title,
						body: [
							{
								type: 'listbox',
								name: 'listbox',
								label: 'Download',
								values: downloadFormsData.download_forms,
								value: '',
								classes: 'ca_drop_down',
							},
						],
						onsubmit( e ) {
							editor.insertContent(
								'[manceppo_download id=' + e.data.listbox + ']'
							);
						},
					} );
				},
			} );
		} );
	} );
} )();
