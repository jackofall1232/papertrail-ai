<?php
/**
 * Admin-side controller.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Admin
 *
 * Handles admin scripts/styles, meta boxes, list-table columns,
 * and plugin action links.
 */
class PTAI_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Wire hooks via PTAI_Loader::define_admin_hooks().
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_styles( $hook ) {
		// @todo Enqueue admin/css/admin.css on relevant screens only.
		unset( $hook );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		// @todo Enqueue admin/js/admin.js, localize nonces.
		unset( $hook );
	}

	/**
	 * Register meta boxes for the ptai_file CPT.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		// @todo add_meta_box() for file attachment, embedding status, etc.
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta_box( $post_id ) {
		// @todo MUST verify nonce via wp_verify_nonce() and capability via current_user_can( 'edit_post', $post_id ).
		// @todo Skip on DOING_AUTOSAVE / revisions before any write.
		unset( $post_id );
	}

	/**
	 * Add custom columns to the CPT list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		// @todo Insert "File", "Type", "Embedding" columns.
		return $columns;
	}

	/**
	 * Populate custom admin columns.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function populate_admin_column( $column, $post_id ) {
		// @todo Render column content based on $column.
		unset( $column, $post_id );
	}

	/**
	 * Add action links on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		// @todo Prepend a Settings link.
		return $links;
	}

	/**
	 * Render the plugin settings page.
	 *
	 * Wraps PTAI_Settings::settings_page_html() and renders the
	 * passive upgrade sidebar from PTAI_Pro alongside it.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		// @todo Render two-column layout: settings form + PTAI_Pro::render_upgrade_sidebar().
	}
}
