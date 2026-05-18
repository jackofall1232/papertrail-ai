<?php
/**
 * Central hook loader for PaperTrail AI.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Loader
 *
 * Wires together all subsystems (CPT, settings, search, admin, public).
 */
class PTAI_Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Instantiate subsystem objects here.
	}

	/**
	 * Run the plugin — register all hooks.
	 *
	 * @return void
	 */
	public function run() {
		// @todo Register CPT and taxonomy hooks.
		// @todo Call define_admin_hooks(), define_public_hooks(), define_ajax_hooks().
	}

	/**
	 * Register admin-only hooks.
	 *
	 * @return void
	 */
	public function define_admin_hooks() {
		// @todo Wire PTAI_Admin and PTAI_Settings hooks.
	}

	/**
	 * Register public/frontend hooks.
	 *
	 * @return void
	 */
	public function define_public_hooks() {
		// @todo Wire PTAI_Public, shortcode and block hooks.
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	public function define_ajax_hooks() {
		// @todo Register wp_ajax_* / wp_ajax_nopriv_* handlers.
	}
}
