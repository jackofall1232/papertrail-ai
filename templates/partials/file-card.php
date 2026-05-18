<?php
/**
 * File card partial.
 *
 * Expected variables:
 *  - Global $post (in the loop).
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'ptai-file-card' ); ?>>
	<h2 class="ptai-file-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<?php
	// @todo Render file icon by mime type.
	// @todo Render excerpt.
	// @todo Render download/view link.
	?>

	<div class="ptai-file-card__excerpt">
		<?php the_excerpt(); ?>
	</div>
</article>
