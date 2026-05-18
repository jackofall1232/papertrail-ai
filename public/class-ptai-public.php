<?php
/**
 * Public/frontend controller.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Public
 *
 * Handles public-facing scripts/styles, template loading, and AJAX search.
 */
class PTAI_Public {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Wire hooks via PTAI_Loader::define_public_hooks().
	}

	/**
	 * Enqueue public styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		// @todo Enqueue public/css/public.css on relevant templates only.
	}

	/**
	 * Enqueue public scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// @todo Enqueue public/js/public.js and localize REST/AJAX endpoint.
	}

	/**
	 * Override single/archive templates for the CPT.
	 *
	 * @param string $template Template path resolved by WordPress.
	 * @return string
	 */
	public function template_include( $template ) {
		// @todo Return plugin-bundled templates for archive-ptai_file/single-ptai_file when theme doesn't override.
		return $template;
	}

	/**
	 * Handle AJAX search request.
	 *
	 * @return void
	 */
	public function handle_search_ajax() {
		// @todo MUST verify nonce via check_ajax_referer() before any work.
		// @todo Rate-limit consideration: throttle per IP / user via a transient bucket
		//       to prevent abuse of the AI search path (which proxies to OpenAI).
		// @todo Call PTAI_Search->search( $query, $args ); wp_send_json_success( $results ).
	}
}
