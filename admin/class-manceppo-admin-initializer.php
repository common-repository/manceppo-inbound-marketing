<?php
/**
 * File class-manceppo-initializer.php
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Provides admin initialization functionality.
 */
class Manceppo_Admin_Initializer {

	/**
	 * Manceppo_Admin_Initializer constructor.
	 */
	private function __construct() {
	}

	/**
	 * Determines if we must include something in the page.
	 */
	public static function init_admin() {
		$initializer = new Manceppo_Admin_Initializer();

		$initializer->admin_register_post_type();

		$post_type = Manceppo_Request_Utils::get_post_type();
		if ( is_null( $post_type ) ) {
			return;
		}

		global $pagenow;
		if ( 'manceppo_download' === $post_type ) {
			if ( 'edit.php' === $pagenow ) {
				// show list of download forms.
				$initializer->include_downloads_columns();
			} else {
				// add or edit download form.
				$initializer->include_download_edit_form();
			}
		} elseif ( 'page' === $post_type || 'post' === $post_type ) {
			// page editor.
			if ( current_user_can( 'edit_pages' ) || current_user_can( 'edit_posts' ) ) {
				Manceppo_Admin_Page_Editor::init();
			}
		}
	}

	/**
	 * Prepare the different menu actions and register post type.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function admin_register_post_type() {
		$labels = array(
			'name'               => _x( 'Download forms', 'post type general name', 'manceppo' ),
			'singular_name'      => _x( 'Download form', 'post type singular name', 'manceppo' ),
			'menu_name'          => _x( 'Manceppo', 'admin menu', 'manceppo' ),
			'name_admin_bar'     => _x( 'Download form', 'add new on admin bar', 'manceppo' ),
			'add_new'            => _x( 'Add new form', 'download', 'manceppo' ),
			'add_new_item'       => __( 'Add new download form', 'manceppo' ),
			'new_item'           => __( 'New download form', 'manceppo' ),
			'edit_item'          => __( 'Edit download form', 'manceppo' ),
			'view_item'          => __( 'View download form', 'manceppo' ),
			'all_items'          => __( 'All download forms', 'manceppo' ),
			'search_items'       => __( 'Search download forms', 'manceppo' ),
			'parent_item_colon'  => __( 'Parent download forms:', 'manceppo' ),
			'not_found'          => __( 'Download forms not found.', 'manceppo' ),
			'not_found_in_trash' => __( 'Download forms not found in trash.', 'manceppo' ),
		);

		$args = array(
			'public'    => true,
			'label'     => 'Download forms',
			'supports'  => array( 'title' ),
			'labels'    => $labels,
			'menu_icon' => 'dashicons-format-aside',
		);

		register_post_type( 'manceppo_download', $args );
	}

	/**
	 * Filter for the downloads overview page.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function include_downloads_columns() {
		add_filter( 'manage_posts_columns', 'manceppo\Manceppo_Admin_Initializer::download_list_column_title' );
		add_action( 'manage_posts_custom_column', 'manceppo\Manceppo_Admin_Initializer::download_list_column_content', 10, 2 );
	}

	/**
	 * The admin page to edit a download form.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function include_download_edit_form() {
		require_once MANCEPPO_PLUGIN_PATH . 'admin/partials/download-edit-form.php';
	}

	/**
	 * Setup layout for admin pages.
	 */
	public static function enqueue_style() {
		wp_enqueue_style( 'manceppo-plugin-admin', MANCEPPO_PLUGIN_URL . 'admin/css/admin.css', array(), MANCEPPO_VERSION, 'screen' );
	}

	/**
	 * Add left hand side (sub) menu item in admin section for connection form.
	 */
	public static function add_connection_sub_menu() {
		add_submenu_page( 'edit.php?post_type=manceppo_download', 'Connect to Manceppo', 'Connect', 'manage_options', 'manceppo_connection_settings', 'manceppo\Manceppo_Admin_Initializer::include_connection_page' );
		add_submenu_page( 'edit.php?post_type=manceppo_download', 'Synchronize with Manceppo', 'Sync', 'manage_options', 'manceppo_sync_settings', 'manceppo\Manceppo_Admin_Initializer::include_settings_page' );
	}

	/**
	 * Registers all the admin page and functionality.
	 */
	public static function init() {
		add_action( 'init', 'manceppo\Manceppo_Admin_Initializer::init_admin' );
		add_action( 'admin_menu', 'manceppo\Manceppo_Admin_Initializer::add_connection_sub_menu' );
		add_action( 'admin_enqueue_scripts', 'manceppo\Manceppo_Admin_Initializer::enqueue_style' );
		add_action( 'save_post_manceppo_download', 'manceppo\Manceppo_Admin::save_download_fields', 10, 2 );
		add_action( 'save_post_page', 'manceppo\Manceppo_Admin::create_embedded_download', 10, 2 );
		add_action( 'save_post_post', 'manceppo\Manceppo_Admin::create_embedded_download', 10, 2 );
	}

	/**
	 * Form to edit and check connection with Manceppo.com
	 */
	public static function include_connection_page() {
		require_once MANCEPPO_PLUGIN_PATH . 'admin/partials/connection-settings.php';
	}

	/**
	 * Form to synchronize with Manceppo.com
	 */
	public static function include_settings_page() {
		require_once MANCEPPO_PLUGIN_PATH . 'admin/partials/sync-settings.php';
	}

	/**
	 * Provides layout for the downloads list (column <code>Shortcode</code>) which will be the active page if
	 * <i>Manceppo</i> menu item selected.
	 *
	 * @param array $defaults list of column titles.
	 *
	 * @return array the modified column list.
	 */
	public static function download_list_column_title( $defaults ) {
		$defaults['manceppo_downloadable_shortcode'] = 'Shortcode';

		return $defaults;
	}

	/**
	 * Returns the shortcode column value.
	 *
	 * @param string $column_name the name of the column.
	 * @param int    $post_id     the post id.
	 */
	public static function download_list_column_content( $column_name, $post_id ) {
		if ( 'manceppo_downloadable_shortcode' === $column_name ) {
			echo '[manceppo_download id=' , esc_html( $post_id ) , ']';
		}
	}
}
