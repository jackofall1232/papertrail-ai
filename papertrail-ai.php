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

	// Self-register subsystems whose loader wiring is not yet implemented.
	// These classes self-wire their hooks in their own constructors.
	if ( class_exists( 'PTAI_CPT' ) ) {
		new PTAI_CPT();
	}
	if ( class_exists( 'PTAI_Embeddings' ) ) {
		new PTAI_Embeddings();
	}
	if ( is_admin() ) {
		if ( class_exists( 'PTAI_Settings' ) ) {
			new PTAI_Settings();
		}
		if ( class_exists( 'PTAI_Admin' ) ) {
			new PTAI_Admin();
		}
	}
}
add_action( 'plugins_loaded', 'ptai_bootstrap' );

/**
 * Register the file download REST endpoint.
 *
 * GET /wp-json/papertrail-ai/v1/download/{id}
 */
add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'papertrail-ai/v1',
			'/download/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'ptai_handle_download',
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => static function ( $param ) {
							return is_numeric( $param ) && (int) $param > 0;
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}
);

/**
 * Handle a file download request.
 *
 * Verifies the post, increments the download counter, and 302-redirects
 * to the underlying attachment URL. Does not log IP, user ID, or any
 * other PII — only the per-post counter and timestamp are updated.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error
 */
function ptai_handle_download( WP_REST_Request $request ) {
	$post_id = (int) $request->get_param( 'id' );

	$post = get_post( $post_id );
	if ( ! $post ) {
		return new WP_Error( 'ptai_not_found', __( 'Document not found.', 'papertrail-ai' ), array( 'status' => 404 ) );
	}

	if ( PTAI_CPT !== $post->post_type ) {
		return new WP_Error( 'ptai_invalid_type', __( 'Requested item is not a PaperTrail document.', 'papertrail-ai' ), array( 'status' => 400 ) );
	}

	if ( 'publish' !== $post->post_status ) {
		return new WP_Error( 'ptai_not_published', __( 'Document is not published.', 'papertrail-ai' ), array( 'status' => 403 ) );
	}

	$file_id = (int) get_post_meta( $post_id, '_ptai_file_id', true );
	if ( $file_id <= 0 ) {
		return new WP_Error( 'ptai_no_file', __( 'No file is attached to this document.', 'papertrail-ai' ), array( 'status' => 404 ) );
	}

	$attached = get_attached_file( $file_id );
	if ( ! $attached ) {
		return new WP_Error( 'ptai_no_file', __( 'Attached file is missing.', 'papertrail-ai' ), array( 'status' => 404 ) );
	}

	$url = wp_get_attachment_url( $file_id );
	if ( empty( $url ) ) {
		return new WP_Error( 'ptai_file_missing', __( 'Attached file URL is unavailable.', 'papertrail-ai' ), array( 'status' => 404 ) );
	}

	// Rate limiting — prevent counter inflation.
	// Token is hashed from post ID + hour window + server salt.
	// No IP addresses or user identifiers stored. GDPR friendly.
	$window        = current_time( 'Y-m-d-H' );
	$salt          = wp_salt( 'auth' );
	$token         = hash( 'sha256', (string) $post_id . $window . $salt );
	$transient_key = 'ptai_dl_' . substr( $token, 0, 40 );

	if ( ! get_transient( $transient_key ) ) {
		// First hit in this hour window — count it.
		set_transient( $transient_key, 1, HOUR_IN_SECONDS );
		$count = absint( get_post_meta( $post_id, '_ptai_download_count', true ) );
		update_post_meta( $post_id, '_ptai_download_count', $count + 1 );
		update_post_meta( $post_id, '_ptai_last_downloaded', current_time( 'mysql' ) );
	}
	// File is always served regardless of rate limit state.
	// Counter accuracy is best-effort. Users always get their download.

	// Bypass the REST JSON envelope: emit a true HTTP redirect.
	// wp_safe_redirect() restricts to allowed hosts; the attachment URL is
	// always on the same site, so it is safe by definition.
	nocache_headers();
	wp_safe_redirect( $url, 302 );
	exit;
}
