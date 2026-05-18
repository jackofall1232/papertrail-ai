<?php
/**
 * Gutenberg block registration.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Block
 *
 * Registers the PaperTrail AI dynamic Gutenberg block.
 */
class PTAI_Block {

	/**
	 * Block name.
	 *
	 * @var string
	 */
	const BLOCK_NAME = 'papertrail-ai/library';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Hook $this->register() to `init`.
	}

	/**
	 * Register the block type.
	 *
	 * @return void
	 */
	public function register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		// @todo register_block_type( self::BLOCK_NAME, $this->get_block_args() ).
	}

	/**
	 * Dynamic render callback.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Inner content.
	 * @return string
	 */
	public function render_callback( $attributes, $content = '' ) {
		// @todo Delegate to PTAI_Shortcode->render() with mapped atts.
		return '';
	}

	/**
	 * Arguments passed to register_block_type().
	 *
	 * @return array
	 */
	public function get_block_args() {
		return array(
			'render_callback' => array( $this, 'render_callback' ),
			'attributes'      => array(
				'limit'    => array(
					'type'    => 'number',
					'default' => 10,
				),
				'category' => array(
					'type'    => 'string',
					'default' => '',
				),
				'showSearch' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
		);
	}
}
