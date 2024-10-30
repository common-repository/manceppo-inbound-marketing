<?php
/**
 * Manceppo Inbound Marketing plugin
 *
 * @package manceppo
 */

/**
 * Plugin Name: Manceppo B2B Marketing Hub
 * Plugin URI: https://www.manceppo.com
 * Description: With the Manceppo B2B Marketing Hub plugin you can connect your WordPress website to your Manceppo account and start to build your custom small business B2B marketing stack, based on the tools you love. This plugin will enable you to build landing pages with our form builder, track visitor behavior and add lead scores. By connecting MailChimp you unleash a complete marketing stack as alternative to expensive Marketing Automation solutions.
 * Version: 0.1.0
 * Requires at least: 4.9
 * Requires PHP:  5.6
 * Author: Manceppo
 * Author URI: https://profiles.wordpress.org/manceppo/
 * Text Domain: manceppo
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * {Plugin Name} is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * {Plugin Name} is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with {Plugin Name}. If not, see {License URI}.
 */

define( 'MANCEPPO_VERSION', '0.1.0' );
define( 'MANCEPPO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MANCEPPO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-request-utils.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-options.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-api.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-api-response.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-field.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-fields.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-form-fields.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-cookie.php';
require_once MANCEPPO_PLUGIN_PATH . 'includes/class-manceppo-logger.php';

if ( is_admin() ) {
	require_once MANCEPPO_PLUGIN_PATH . 'admin/class-manceppo-admin-initializer.php';
	manceppo\Manceppo_Admin_Initializer::init();
	require_once MANCEPPO_PLUGIN_PATH . 'admin/class-manceppo-admin.php';
	require_once MANCEPPO_PLUGIN_PATH . 'admin/class-manceppo-admin-page-editor.php';
} else {
	require_once MANCEPPO_PLUGIN_PATH . 'public/frontend.php';
	require_once MANCEPPO_PLUGIN_PATH . 'public/class-manceppo-rest-api.php';
	require_once MANCEPPO_PLUGIN_PATH . 'public/class-manceppo-html-utils.php';
	require_once MANCEPPO_PLUGIN_PATH . 'public/class-manceppo-html-input-builder.php';
	add_action( 'rest_api_init', array( 'manceppo\Manceppo_Rest_Api', 'init' ) );
}
