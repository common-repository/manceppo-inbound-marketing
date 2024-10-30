/* globals jQuery grecaptcha */

class ManceppoForm {
	static init( prefix, nonce, $ ) {
		const idPrefix = '#' + prefix;

		$( idPrefix + 'manceppo_download_button' ).click( function () {
			$( this ).addClass( 'manceppo_hidden' );
			$( idPrefix + 'manceppo_api_form' ).removeClass(
				'manceppo_hidden'
			);
			return false;
		} );

		$( idPrefix + 'manceppo_api_form form input[type="submit"]' ).click(
			function ( event ) {
				const elements = $( idPrefix + 'manceppo_api_form form input' );
				for ( let i = 0; i < elements.length; i++ ) {
					if ( ! elements[ i ].checkValidity() ) {
						event.preventDefault();
						return false;
					}
				}

				$( idPrefix + 'manceppo_api_form' ).addClass(
					'manceppo_hidden'
				);
				$( idPrefix + 'manceppo_api_form_thankyou' ).removeClass(
					'manceppo_hidden'
				);

				const newsletters = [];
				$( 'input.' + prefix + 'manceppo_news_letter' ).each( function (
					index,
					element
				) {
					if ( element.checked || element.type === 'hidden' ) {
						newsletters.push( element.value );
					}
				} );
				const vals = {
					formId: $( idPrefix + 'manceppo_form_id' ).val(),
					download: $( idPrefix + 'manceppo_download' ).val(),
					campaign: $( idPrefix + 'manceppo_campaign' ).val(),
					visitorId: $( idPrefix + 'visitorId' ).val(),
					postUrl: $( idPrefix + 'postUrl' ).val(),
					email: $( idPrefix + 'manceppo_fields_email' ).val(),
					firstName: $(
						idPrefix + 'manceppo_fields_first_name'
					).val(),
					lastName: $( idPrefix + 'manceppo_fields_last_name' ).val(),
					gender: $( idPrefix + 'manceppo_fields_gender' ).val(),
					company: $( idPrefix + 'manceppo_fields_company' ).val(),
					jobTitle: $( idPrefix + 'manceppo_fields_job_title' ).val(),
					jobFunction: $(
						idPrefix + 'manceppo_fields_job_function'
					).val(),
					numberOfEmployees: $(
						idPrefix + 'manceppo_fields_number_of_employees'
					).val(),
					industry: $( idPrefix + 'manceppo_fields_industry' ).val(),
					phone: $( idPrefix + 'manceppo_fields_phone' ).val(),
					address1: $( idPrefix + 'manceppo_fields_address1' ).val(),
					address2: $( idPrefix + 'manceppo_fields_address1' ).val(),
					postalCode: $(
						idPrefix + 'manceppo_fields_postal_code'
					).val(),
					city: $( idPrefix + 'manceppo_fields_city' ).val(),
					state: $(
						idPrefix + 'manceppo_fields_state_province'
					).val(),
					country: $( idPrefix + 'manceppo_fields_country' ).val(),
					message: $( idPrefix + 'manceppo_message' ).val(),
					_wpnonce: nonce,
					newsletters,
				};

				$.post( '?rest_route=/manceppo/v1/download', vals, function () {
					const aTag = $( idPrefix + 'manceppo_api_form_thankyou' );
					$( 'html,body' ).animate(
						{
							scrollTop:
								aTag.offset().top - $( window ).height() / 2,
						},
						'slow'
					);
				} ).fail( function ( response ) {
					// eslint-disable-next-line no-console
					console.error(
						`Error sending form: ${ response.responseText }`
					);
				} );

				return false;
			}
		);
	}

	static verify( formId, token, nonce, $ ) {
		$.ajax( {
			type: 'POST',
			url: '?rest_route=/manceppo/v1/captcha',
			data: { token, _wpnonce: nonce },
			error: () => {
				$( '#' + formId + 'manceppo_download_button' ).each( function (
					index,
					cmd
				) {
					$( cmd ).attr( 'disabled', true );
				} );
			},
		} );
	}
}

if ( typeof jQuery !== 'undefined' ) {
	jQuery( document ).ready( function () {
		jQuery( 'input[type=hidden].manceppo_init_form' ).each( function (
			index,
			element
		) {
			const id = jQuery( element ).attr( 'id' );
			const nonce = jQuery( element ).attr( 'data-nonce' );
			const dataToken = jQuery( element ).attr( 'data-token' );

			ManceppoForm.init( id, nonce, jQuery );

			if ( dataToken !== '' && typeof grecaptcha !== 'undefined' ) {
				grecaptcha.ready( function () {
					grecaptcha
						.execute( dataToken, { action: 'manceppo_download' } )
						.then( function ( token ) {
							ManceppoForm.verify( id, token, nonce, jQuery );
						} );
				} );
			}
		} );
	} );
}
