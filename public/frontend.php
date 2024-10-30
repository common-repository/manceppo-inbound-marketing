<?php
/**
 * Enqueues styling and content for public download form.
 *
 * @package manceppo
 */

namespace manceppo;

/**
 * Enqueue scripts and stylesheets for public download form.
 */
function frontend_enqueue() {
	global $wp;
	$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	$url         = Manceppo_Options::get_api_url() . '/v1/tracking?ref=' . base64_encode( $current_url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

	wp_enqueue_script( 'manceppo_plugin_tracker', $url, array( 'jquery' ), MANCEPPO_VERSION, true );

	wp_enqueue_script( 'manceppo_download_form_js', MANCEPPO_PLUGIN_URL . 'public/js/download_form.js', array( 'jquery' ), MANCEPPO_VERSION, true );

	wp_enqueue_style( 'manceppo_download_form_generic_css', MANCEPPO_PLUGIN_URL . 'public/css/frontend-generic.css', array(), MANCEPPO_VERSION );

	if ( ! Manceppo_Options::use_custom_css() ) {
		wp_enqueue_style( 'manceppo_download_form_css', MANCEPPO_PLUGIN_URL . 'public/css/frontend.css', array(), MANCEPPO_VERSION );
	}
}

/**
 * Injects the Manceppo download form if needed.
 *
 * @param array $args short code array.
 *
 * @return mixed the contents of <pre>public/partials/download-form.php</pre>.
 */
function frontend_add_shortcode( $args ) {
	$args = shortcode_atts( array( 'id' => null ), $args, 'manceppo_download' );

	if ( isset( $args['id'] ) && 'manceppo_download' === get_post_type( $args['id'] ) ) {
		define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

		require_once MANCEPPO_PLUGIN_PATH . 'public/partials/download-form.php';

		return manceppo_download_form( $args );
	}
}

add_action( 'wp_enqueue_scripts', 'manceppo\frontend_enqueue' );
add_shortcode( 'manceppo_download', 'manceppo\frontend_add_shortcode' );
