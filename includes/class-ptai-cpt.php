<?php
/**
 * Custom post type and taxonomy registration.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_CPT
 *
 * Registers the `ptai_file` custom post type and `ptai_category` taxonomy.
 */
class PTAI_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Hook register_post_type/register_taxonomy/register_meta_fields to `init`.
	}

	/**
	 * Register the ptai_file custom post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		// @todo Call register_post_type( PTAI_CPT, $args ) with proper labels and args.
	}

	/**
	 * Register the ptai_category taxonomy.
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		// @todo Call register_taxonomy( PTAI_TAXONOMY, PTAI_CPT, $args ).
	}

	/**
	 * Register custom meta fields for the CPT.
	 *
	 * @return void
	 */
	public function register_meta_fields() {
		// @todo Call register_post_meta() for file URL, file type, embedding, etc.
	}

	/**
	 * Flush rewrite rules if a flag is set.
	 *
	 * @return void
	 */
	public function flush_rewrite_rules_if_needed() {
		// @todo Check ptai_flush_rewrite option, flush, then delete it.
	}
}
