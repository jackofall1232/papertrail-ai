<?php
/**
 * Single template for a PaperTrail AI document.
 *
 * Themes may override this by providing their own single-ptai_file.php.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main ptai-single">

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ptai-single__article' ); ?>>

			<header class="ptai-single__header">
				<h1 class="ptai-single__title"><?php the_title(); ?></h1>
			</header>

			<div class="ptai-single__content">
				<?php the_content(); ?>
			</div>

			<?php
			// @todo Render file download / preview area.
			// @todo Render categories and tags.
			?>

		</article>
		<?php
	endwhile;
	?>

</main>

<?php
get_footer();
