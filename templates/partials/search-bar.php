<?php
/**
 * Search bar partial.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

$ptai_search_action = esc_url( home_url( '/' ) );
$ptai_search_value  = isset( $_GET['ptai_q'] ) ? sanitize_text_field( wp_unslash( $_GET['ptai_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<form role="search" method="get" class="ptai-search" action="<?php echo $ptai_search_action; ?>">
	<label for="ptai-search-input" class="screen-reader-text">
		<?php esc_html_e( 'Search documents', 'papertrail-ai' ); ?>
	</label>

	<input
		type="search"
		id="ptai-search-input"
		class="ptai-search__input"
		name="ptai_q"
		value="<?php echo esc_attr( $ptai_search_value ); ?>"
		placeholder="<?php esc_attr_e( 'Search documents&hellip;', 'papertrail-ai' ); ?>"
	/>

	<input type="hidden" name="post_type" value="<?php echo esc_attr( PTAI_CPT ); ?>" />

	<button type="submit" class="ptai-search__submit">
		<?php esc_html_e( 'Search', 'papertrail-ai' ); ?>
	</button>

	<?php
	// @todo Render category filter, AI toggle if enabled.
	?>
</form>
