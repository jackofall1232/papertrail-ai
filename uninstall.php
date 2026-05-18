<?php
/**
 * PaperTrail AI uninstall routine.
 *
 * Triggered when the plugin is deleted via the WordPress admin.
 * Respects the standalone `ptai_delete_data_on_uninstall` option —
 * if it is not set to a truthy value, this file exits without
 * touching any data.
 *
 * @package PaperTrail_AI
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Opt-in cleanup: leave user data alone unless explicitly enabled.
if ( ! get_option( 'ptai_delete_data_on_uninstall' ) ) {
	return;
}

global $wpdb;

/*
 * 1. Delete all ptai_file posts (and their postmeta).
 */
$ptai_post_ids = get_posts(
	array(
		'post_type'        => 'ptai_file',
		'post_status'      => 'any',
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => true,
	)
);

if ( ! empty( $ptai_post_ids ) ) {
	foreach ( $ptai_post_ids as $ptai_post_id ) {
		wp_delete_post( $ptai_post_id, true );
	}
}

/*
 * 2. Delete all ptai_category taxonomy terms.
 */
$ptai_term_ids = get_terms(
	array(
		'taxonomy'   => 'ptai_category',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);

if ( ! is_wp_error( $ptai_term_ids ) && ! empty( $ptai_term_ids ) ) {
	foreach ( $ptai_term_ids as $ptai_term_id ) {
		wp_delete_term( $ptai_term_id, 'ptai_category' );
	}
}

/*
 * 3. Defensive sweep: remove embedding postmeta from any post type
 *    (in case files were converted or attached elsewhere).
 */
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ptai_embedding%'" );
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ptai_%'" );

/*
 * 4. Delete plugin options.
 */
$ptai_options = array(
	'ptai_settings',
	'ptai_db_version',
	'ptai_activated_at',
	'ptai_flush_rewrite',
	'ptai_delete_data_on_uninstall',
);

foreach ( $ptai_options as $ptai_option ) {
	delete_option( $ptai_option );
	delete_site_option( $ptai_option );
}

/*
 * 5. Custom DB tables.
 *
 * @todo If a future version introduces a custom table
 *       (e.g. {$wpdb->prefix}ptai_embeddings), drop it here
 *       with $wpdb->query( "DROP TABLE IF EXISTS ..." ).
 */

/*
 * 6. Clear any scheduled cron events.
 */
wp_clear_scheduled_hook( 'ptai_daily_maintenance' );
wp_clear_scheduled_hook( 'ptai_regenerate_embeddings' );

/*
 * 7. Flush rewrite rules.
 */
flush_rewrite_rules();
