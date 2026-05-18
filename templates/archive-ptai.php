<?php
/**
 * Archive template for the PaperTrail AI file library.
 *
 * Themes may override this by providing their own archive-ptai_file.php.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main ptai-archive">

	<header class="ptai-archive__header">
		<h1 class="ptai-archive__title"><?php esc_html_e( 'Document Library', 'papertrail-ai' ); ?></h1>
	</header>

	<?php
	// @todo Render search bar partial.
	// @todo Render file-card partial in a loop.
	?>

	<?php if ( have_posts() ) : ?>
		<div class="ptai-archive__list">
			<?php
			while ( have_posts() ) :
				the_post();
				include PTAI_PLUGIN_DIR . 'templates/partials/file-card.php';
			endwhile;
			?>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="ptai-archive__empty">
			<?php esc_html_e( 'No documents found.', 'papertrail-ai' ); ?>
		</p>
	<?php endif; ?>

</main>

<?php
get_footer();
