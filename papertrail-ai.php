<?php
/**
 * Plugin Name:       PaperTrail AI — Smart Document Library
 * Plugin URI:        https://example.com/papertrail-ai
 * Description:       AI-powered document library for WordPress. Upload, organize, and semantically search files using OpenAI embeddings.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ask Adam
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       papertrail-ai
 * Domain Path:       /languages
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin constants.
 */
define( 'PTAI_VERSION', '1.0.0' );
define( 'PTAI_PLUGIN_FILE', __FILE__ );
define( 'PTAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PTAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PTAI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'PTAI_TEXT_DOMAIN', 'papertrail-ai' );
define( 'PTAI_CPT', 'ptai_file' );
define( 'PTAI_TAXONOMY', 'ptai_category' );
define( 'PTAI_DB_VERSION', '1.0' );
define( 'PTAI_MIN_PHP', '7.4' );
define( 'PTAI_MIN_WP', '6.0' );

/**
 * Environment compatibility check.
 *
 * @return bool
 */
function ptai_check_environment() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, PTAI_MIN_PHP, '<' ) ) {
		add_action(
			'admin_notices',
			static function () {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html(
						sprintf(
							/* translators: 1: required PHP version, 2: current PHP version */
							__( 'PaperTrail AI requires PHP %1$s or higher. You are running PHP %2$s.', 'papertrail-ai' ),
							PTAI_MIN_PHP,
							PHP_VERSION
						)
					)
				);
			}
		);
		return false;
	}

	if ( isset( $wp_version ) && version_compare( $wp_version, PTAI_MIN_WP, '<' ) ) {
		add_action(
			'admin_notices',
			static function () use ( $wp_version ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html(
						sprintf(
							/* translators: 1: required WP version, 2: current WP version */
							__( 'PaperTrail AI requires WordPress %1$s or higher. You are running WordPress %2$s.', 'papertrail-ai' ),
							PTAI_MIN_WP,
							$wp_version
						)
					)
				);
			}
		);
		return false;
	}

	return true;
}

/**
 * Load plugin files.
 */
function ptai_load_files() {
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-loader.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-cpt.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-settings.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-openai.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-embeddings.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-search.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-shortcode.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-block.php';
	require_once PTAI_PLUGIN_DIR . 'includes/class-ptai-pro.php';
	require_once PTAI_PLUGIN_DIR . 'admin/class-ptai-admin.php';
	require_once PTAI_PLUGIN_DIR . 'public/class-ptai-public.php';
}

/**
 * Activation hook.
 */
function ptai_activate() {
	if ( ! ptai_check_environment() ) {
		return;
	}

	ptai_load_files();

	if ( class_exists( 'PTAI_CPT' ) ) {
		$cpt = new PTAI_CPT();
		$cpt->register_post_type();
		$cpt->register_taxonomy();
	}

	flush_rewrite_rules();

	update_option( 'ptai_db_version', PTAI_DB_VERSION );
	update_option( 'ptai_activated_at', time() );
}
register_activation_hook( __FILE__, 'ptai_activate' );

/**
 * Deactivation hook.
 */
function ptai_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ptai_deactivate' );

/**
 * Bootstrap the plugin.
 */
function ptai_bootstrap() {
	if ( ! ptai_check_environment() ) {
		return;
	}

	ptai_load_files();

	load_plugin_textdomain(
		'papertrail-ai',
		false,
		dirname( PTAI_PLUGIN_BASENAME ) . '/languages'
	);

	if ( class_exists( 'PTAI_Loader' ) ) {
		$loader = new PTAI_Loader();
		$loader->run();
	}
}
add_action( 'plugins_loaded', 'ptai_bootstrap' );
