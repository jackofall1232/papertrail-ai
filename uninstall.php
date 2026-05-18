<?php
/**
 * PaperTrail AI uninstall routine.
 *
 * Runs when the plugin is deleted from the WordPress admin.
 * Respects the "delete data on uninstall" setting — if disabled,
 * the plugin will leave its data in place.
 *
 * @package PaperTrail_AI
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Whether the user opted in to full data deletion.
 *
 * @return bool
 */
function ptai_should_delete_data() {
	$settings = get_option( 'ptai_settings', array() );

	if ( is_array( $settings ) && ! empty( $settings['delete_data_on_uninstall'] ) ) {
		return true;
	}

	return false;
}

if ( ! ptai_should_delete_data() ) {
	return;
}

global $wpdb;

/*
 * Delete all ptai_file posts and their postmeta.
 */
$post_ids = get_posts(
	array(
		'post_type'      => 'ptai_file',
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'suppress_filters' => true,
	)
);

if ( ! empty( $post_ids ) ) {
	foreach ( $post_ids as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}

/*
 * Delete plugin options.
 */
$options_to_delete = array(
	'ptai_settings',
	'ptai_db_version',
	'ptai_activated_at',
	'ptai_flush_rewrite',
);

foreach ( $options_to_delete as $option ) {
	delete_option( $option );
	delete_site_option( $option );
}

/*
 * Delete any leftover embedding postmeta (defensive cleanup).
 */
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ptai_embedding%'" );
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ptai_%'" );

/*
 * Drop custom DB tables if any were added in future versions.
 *
 * @todo If a custom table is ever introduced (e.g. wp_ptai_embeddings)
 *       drop it here with $wpdb->query( "DROP TABLE IF EXISTS ..." ).
 */
$custom_tables = array(
	// $wpdb->prefix . 'ptai_embeddings',
);

foreach ( $custom_tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/*
 * Clear scheduled cron events.
 */
wp_clear_scheduled_hook( 'ptai_daily_maintenance' );
wp_clear_scheduled_hook( 'ptai_regenerate_embeddings' );

/*
 * Flush rewrite rules.
 */
flush_rewrite_rules();
