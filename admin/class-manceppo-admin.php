<?php
/**
 * File class-manceppo-admin.php
 *
 * @package manceppo
 */

namespace manceppo;

use WP_Post;
use WP_Query;

/**
 * Provides admin specific functionality.
 */
class Manceppo_Admin {


	/**
	 * Action performed after saving a page or post in the editor.
	 *
	 * @param int     $post_id the id of the page.
	 * @param WP_Post $post    Post object.
	 *
	 * @return array|null with the download id and the HTTP response code from manceppo.
	 */
	public static function create_embedded_download( $post_id, $post ) {
		if ( ! self::is_valid_submit( $post_id, $post, array( 'post', 'page' ) ) ) {
			return null;
		}

		Manceppo_Logger::log( 'DEBUG manceppo::admin - create embedded called' );

		// parse content and see if we find a manceppo short code..
		$content = $post->post_content;
		$matches = null;
		preg_match_all( '/(\[manceppo_download id=[0-9]*\])/', $content, $matches );

		$results = array();
		foreach ( $matches[0] as $match ) {
			$match = explode( '=', $match );
			$match = mb_substr( $match[1], 0, -1 );

			Manceppo_Logger::log( 'DEBUG manceppo::admin - embedding form %s', $match );

			$campaign     = get_post_meta( $match, '_manceppo_campaign', true );
			$downloadable = get_post_meta( $match, '_manceppo_download', true );
			$slug         = get_the_permalink( $post_id );
			$response     = Manceppo_Api::get_instance()
				->create_embedded_download( $downloadable, $campaign, $slug, $match );

			Manceppo_Logger::log( 'DEBUG manceppo::admin - created embedded download for form: %s and slug: %s, server response: %s', $match, $slug, $response->get_code() );

			$results[ $match ] = $response->get_code();
		}

		return $results;
	}

	/**
	 * Verifies if the submit can be saved.
	 *
	 * @param int     $post_id             the id of the WP post.
	 * @param WP_Post $post                the WP post object.
	 * @param array   $expected_post_types list of permitted post types.
	 *
	 * @return bool
	 */
	private static function is_valid_submit( $post_id, $post, $expected_post_types ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		if ( 'page' === $post->post_type ) {
			if ( ! current_user_can( 'edit_pages', $post_id ) ) {
				return false;
			}
		} elseif ( ! current_user_can( 'edit_posts', $post_id ) ) {
			return false;
		}

		return in_array( $post->post_type, $expected_post_types, true );
	}

	/**
	 * Saves the submit of page <i>admin/partials/download-edit-form.php</i>.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_download_fields( $post_id, $post ) {
		if ( ! self::is_valid_submit( $post_id, $post, array( 'manceppo_download' ) ) ) {
			return;
		}

		if ( isset( $_POST['manceppo_admin_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['manceppo_admin_wpnonce'] ), 'manceppo_edit_download' ) ) {
			Manceppo_Logger::log( 'DEBUG manceppo::admin - save download fields called' );

			self::save_post_meta( $_POST, $post_id );
			self::save_newsletters( $_POST, $post_id );
		}
	}

	/**
	 * Saves all the download form fields and settings.
	 *
	 * @param object $request the Http post.
	 * @param int    $post_id the post id.
	 */
	private static function save_post_meta( $request, $post_id ) {
		$form_fields = new Manceppo_Form_Fields( $post_id, Manceppo_Options::is_debug_enabled() );

		$current_download = $form_fields->get( 'download' );
		$current_campaign = $form_fields->get( 'campaign' );

		foreach ( Manceppo_Form_Fields::ALL_FIELDS as $field_name ) {
			if ( 'fields' === $field_name ) {
				self::save_manceppo_fields( $request, $form_fields );
			} else {
				$value = Manceppo_Request_Utils::get_request_value( $request, 'manceppo_' . $field_name );
				$form_fields->update( $field_name, $value );
			}
		}

		$new_download = $form_fields->get( 'download' );
		$new_campaign = $form_fields->get( 'campaign' );

		if ( $current_campaign !== $new_campaign || $current_download !== $new_download ) {
			Manceppo_Logger::log(
				'DEBUG manceppo::admin - form settings have been changed [download new: %s, old: %s, campaign new: %s, old: %s], syncing form',
				$new_download,
				$current_download,
				$new_campaign,
				$current_campaign
			);
			Manceppo_Api::get_instance()->sync_form_settings( $new_download, $new_campaign, $post_id );
		}
	}

	/**
	 * Saves the news letter fields fields.
	 *
	 * @param object $request the Http post.
	 * @param int    $post_id the post id.
	 */
	private static function save_newsletters( $request, $post_id ) {
		if ( isset( $request['manceppo_newsletters'] ) ) {
			$update      = array();
			$newsletters = wp_unslash( $request['manceppo_newsletters'] );

			foreach ( $newsletters as $newsletter ) {
				if ( isset( $request[ 'manceppo_newsletter_choice_' . $newsletter ] ) ) {
					$mode  = wp_unslash( $request[ 'manceppo_newsletter_choice_' . $newsletter ] );
					$title = sanitize_text_field( wp_unslash( $request[ 'manceppo_newsletter_' . $newsletter ] ) );
					$label = sanitize_text_field( wp_unslash( $request[ 'manceppo_newsletter_label_' . $newsletter ] ) );

					// backward compatibility.
					$required = 'required' === $mode;
					$auto     = 'auto' === $mode;

					$update[ $newsletter ] = array(
						'mode'     => $mode,
						'title'    => $title,
						'label'    => $label,
						'required' => $required,
						'auto'     => $auto,
					);
				}
			}

			if ( Manceppo_Logger::is_debug_enabled() ) {
				Manceppo_Logger::log( 'DEBUG manceppo::admin - saving field manceppo_newsletters with value: ' . wp_json_encode( $update ) );
			}

			add_post_meta( $post_id, '_manceppo_newsletters', $update, true ) || update_post_meta( $post_id, '_manceppo_newsletters', $update );
		} else {
			delete_post_meta( $post_id, '_manceppo_newsletters' );
		}
	}

	/**
	 * Gets the list of downloads that can be embedded in a page.
	 *
	 * @return string|false the download forms as json data to be used for the download form popup.
	 */
	public static function get_download_forms() {
		Manceppo_Logger::log( 'DEBUG manceppo::admin - get download forms called' );

		$download_forms = array();
		$qry            = new WP_Query(
			array(
				'post_type'      => 'manceppo_download',
				'posts_per_page' => -1,
			)
		);
		try {
			while ( $qry->have_posts() ) {
				$qry->the_post();
				$download_forms[] = array(
					'text'  => get_the_title(),
					'value' => get_the_ID(),
				);
			}
		} finally {
			wp_reset_postdata();
		}

		return wp_json_encode(
			array(
				'button_name'    => esc_html__( 'Manceppo download', 'manceppo' ),
				'button_title'   => esc_html__( 'Manceppo shortcode', 'manceppo' ),
				'button_image'   => MANCEPPO_PLUGIN_URL . 'admin/images/manceppo.png',
				'download_forms' => $download_forms,
			)
		);
	}

	/**
	 * Updates all posts with information of campaigns.
	 *
	 * @return int number of updated records.
	 */
	public static function update_download_forms() {
		Manceppo_Logger::log( 'DEBUG manceppo::admin - update downloads called' );

		$count = 0;

		$qry = new WP_Query(
			array(
				'post_type'      => 'manceppo_download',
				'posts_per_page' => -1,
			)
		);
		try {
			while ( $qry->have_posts() ) {
				$qry->the_post();
				$post_id = get_the_ID();

				$updated = self::update_download_form( $post_id );
				if ( $updated ) {
					++$count;
				}
			}

			return $count;
		} finally {
			wp_reset_postdata();
		}
	}

	/**
	 * Updates post with information of campaign.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool flag indicating if the record as updated.
	 */
	public static function update_download_form( $post_id ) {
		$form_fields = new Manceppo_Form_Fields( $post_id, Manceppo_Options::is_debug_enabled() );
		$campaign_id = $form_fields->get( 'campaign' );
		$updated     = false;

		if ( $campaign_id ) {
			$campaign_response = Manceppo_Api::get_instance()->get_campaign( $campaign_id );
			if ( $campaign_response->is_successful() ) {
				$content = $campaign_response->get_content();
				if ( ! empty( $content ) ) {
					if ( property_exists( $content, 'thankYouText' ) ) {
						Manceppo_Logger::log( 'DEBUG manceppo::admin - synchronize download form: %s with campaign: %s', $post_id, $campaign_id );

						$thanks_body = $content->{'thankYouText'};
						$form_fields->update( 'thanks_body', $thanks_body );

						$updated = true;
					}
				}
			}
		}

		return $updated;
	}

	/**
	 * Saves the Manceppo fields as used in the download form.
	 *
	 * @param object               $request     the Http post.
	 * @param Manceppo_Form_Fields $form_fields the form field class instance.
	 */
	private static function save_manceppo_fields( $request, $form_fields ) {
		$fields          = array();
		$manceppo_fields = Manceppo_Fields::get_instance()->get_fields();
		foreach ( $manceppo_fields as $manceppo_field ) {
			$name             = $manceppo_field->get_name();
			$field_name       = 'manceppo_fields_' . $name;
			$label_field_name = 'manceppo_fields_' . $name . '_label';
			if ( isset( $request[ $field_name ] ) ) {
				$fields[ $name ] = sanitize_text_field( wp_unslash( $request[ $field_name ] ) );
			}
			if ( isset( $request[ $label_field_name ] ) ) {
				$fields[ $name . '_label' ] = sanitize_text_field( wp_unslash( $request[ $label_field_name ] ) );
			}
		}

		$form_fields->update( 'fields', $fields );
	}
}
