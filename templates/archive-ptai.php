<?php
/**
 * Archive template for the PaperTrail AI document library.
 *
 * Loaded by:
 *   - PTAI_Public::template_include() for the CPT archive.
 *   - PTAI_Shortcode::render() for inline [papertrail] embeds.
 *
 * Themes may override this by providing their own
 * `papertrail-ai/archive-ptai.php` in the active stylesheet.
 *
 * Expected variables when included by the shortcode:
 *   - $ptai_results array  Search result array (see PTAI_Search::search()).
 *   - $ptai_columns int    1 or 2.
 *   - $ptai_in_shortcode bool
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

// Detect whether we're being included by the shortcode or as a full template.
$ptai_standalone = empty( $ptai_in_shortcode );

// When the theme runs us as the full archive template, build a results
// array from the main query so the markup below is identical either way.
if ( $ptai_standalone ) {
	get_header();

	global $wp_query;
	$ptai_results = array(
		'posts'     => is_array( $wp_query->posts ) ? $wp_query->posts : array(),
		'total'     => (int) $wp_query->found_posts,
		'pages'     => (int) $wp_query->max_num_pages,
		'mode_used' => 'core',
		'query'     => isset( $_GET['ptai_q'] ) ? sanitize_text_field( wp_unslash( $_GET['ptai_q'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	);
	$ptai_columns = 1;
}

$ptai_results = isset( $ptai_results ) && is_array( $ptai_results )
	? $ptai_results
	: array( 'posts' => array(), 'total' => 0, 'pages' => 0, 'mode_used' => '', 'query' => '' );
$ptai_columns = isset( $ptai_columns ) ? (int) $ptai_columns : 1;
?>

<div class="ptai-document-library">

	<?php if ( $ptai_standalone ) : ?>
		<header class="ptai-archive__header">
			<h1 class="ptai-archive__title">
				<?php esc_html_e( 'Document Library', 'papertrail-ai' ); ?>
			</h1>
		</header>

		<?php
		// Render the search bar partial for the archive page.
		$ptai_search_context = array(
			'category_id' => 0,
			'mode'        => 'auto',
		);
		include PTAI_PLUGIN_DIR . 'templates/partials/search-bar.php';
		?>
	<?php endif; ?>

	<?php if ( empty( $ptai_results['posts'] ) ) : ?>
		<p class="ptai-no-results">
			<?php esc_html_e( 'No documents found.', 'papertrail-ai' ); ?>
		</p>
	<?php else : ?>

		<div class="ptai-mode-indicator">
			<?php if ( 'ai' === $ptai_results['mode_used'] ) : ?>
				<span class="ptai-ai-badge">
					<?php esc_html_e( 'AI-powered results', 'papertrail-ai' ); ?>
				</span>
			<?php endif; ?>
		</div>

		<ul class="ptai-file-list ptai-columns-<?php echo esc_attr( $ptai_columns ); ?>">
			<?php
			foreach ( $ptai_results['posts'] as $post ) :
				if ( ! ( $post instanceof WP_Post ) ) {
					continue;
				}
				include PTAI_PLUGIN_DIR . 'templates/partials/file-card.php';
			endforeach;
			?>
		</ul>

		<?php
		if ( (int) $ptai_results['pages'] > 1 ) {
			$ptai_paged = max( 1, (int) get_query_var( 'paged', 1 ) );
			$ptai_links = paginate_links(
				array(
					'total'     => (int) $ptai_results['pages'],
					'current'   => $ptai_paged,
					'mid_size'  => 2,
					'prev_text' => esc_html__( '« Previous', 'papertrail-ai' ),
					'next_text' => esc_html__( 'Next »', 'papertrail-ai' ),
					'type'      => 'list',
				)
			);
			if ( $ptai_links ) {
				echo '<nav class="ptai-pagination" aria-label="' . esc_attr__( 'Document pagination', 'papertrail-ai' ) . '">';
				echo wp_kses_post( $ptai_links );
				echo '</nav>';
			}
		}
		?>

	<?php endif; ?>

</div>

<?php
if ( $ptai_standalone ) {
	get_footer();
}
