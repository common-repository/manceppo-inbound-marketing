<?php
/**
 * File class-manceppo-page-editor.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Class Manceppo_Admin_Page_Editor
 *
 * Registers actions and styles for the page edit form related to Manceppo.
 */
class Manceppo_Admin_Page_Editor {

	/**
	 * Initializes the Manceppo specific functions (and button) to be used when edit-ing page.
	 */
	public static function init() {
		add_action( 'admin_init', 'manceppo\Manceppo_Admin_Page_Editor::edit_button_init' );
		add_action( 'after_wp_tiny_mce', 'manceppo\Manceppo_Admin_Page_Editor::download_selector_data' );
		add_action( 'media_buttons', 'manceppo\Manceppo_Admin_Page_Editor::include_manceppo_button', 999 );
	}

	/**
	 * Adds Manceppo button to javascript snippet.
	 *
	 * @param array $plugins Editor plugin array.
	 *
	 * @return array the list of plugins.
	 */
	public static function add_manceppo_button( $plugins ) {
		$plugins['manceppo_tiny_button'] = MANCEPPO_PLUGIN_URL . 'admin/js/tinymce_manceppo_button.js';

		return $plugins;
	}

	/**
	 * Register Manceppo button for display.
	 *
	 * @param array $buttons Editor button array.
	 *
	 * @return array the list of buttons.
	 */
	public static function register_manceppo_button( $buttons ) {
		array_push( $buttons, 'manceppo_tiny_button' );

		return $buttons;
	}

	/**
	 * Initialized the Manceppo plugin button in the post editor.
	 */
	public static function edit_button_init() {
		if ( ! user_can_richedit() ) {
			return;
		}

		Manceppo_Logger::log( 'TRACE manceppo::admin-page-editor - init edit buttons' );

		wp_enqueue_script( 'manceppo_admin_button_js', MANCEPPO_PLUGIN_URL . 'admin/js/tinymce_manceppo_button.js', array( 'jquery' ), MANCEPPO_VERSION, true );
		add_filter( 'mce_external_plugins', 'manceppo\Manceppo_Admin_Page_Editor::add_manceppo_button' );
		add_filter( 'mce_buttons', 'manceppo\Manceppo_Admin_Page_Editor::register_manceppo_button' );
	}

	/**
	 * Button json data, displayed in popup to select download item.
	 */
	public static function download_selector_data() {
		require_once MANCEPPO_PLUGIN_PATH . 'admin/partials/page-editor-download-select-data.php';
	}

	/**
	 * Button HTML.
	 */
	public static function include_manceppo_button() {
		require_once MANCEPPO_PLUGIN_PATH . 'admin/partials/page-editor-manceppo-button.php';
	}
}
