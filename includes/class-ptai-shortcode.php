<?php
/**
 * [papertrail_ai] shortcode handler.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Shortcode
 *
 * Registers and renders the front-end shortcode for the document library.
 */
class PTAI_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	const TAG = 'papertrail_ai';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Call $this->register() on init.
	}

	/**
	 * Register the shortcode with WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array       $atts    Shortcode attributes.
	 * @param string|null $content Inner content.
	 * @return string
	 */
	public function render( $atts = array(), $content = null ) {
		// @todo Parse atts, query files, render list and/or search bar.
		return '';
	}

	/**
	 * Render the file list portion.
	 *
	 * @param array $posts WP_Post objects.
	 * @param array $atts  Shortcode attributes.
	 * @return string
	 */
	public function render_list( $posts, $atts ) {
		// @todo Load templates/partials/file-card.php for each post.
		return '';
	}

	/**
	 * Render the optional search bar.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_search_bar( $atts ) {
		// @todo Load templates/partials/search-bar.php.
		return '';
	}

	/**
	 * Default shortcode attribute values.
	 *
	 * @return array
	 */
	public function get_default_atts() {
		return array(
			'limit'     => 10,
			'category'  => '',
			'search'    => 'yes',
			'orderby'   => 'date',
			'order'     => 'DESC',
		);
	}
}
