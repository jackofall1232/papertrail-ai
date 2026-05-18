<?php
/**
 * Search engine for the document library.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Search
 *
 * Provides both classic keyword search and AI-powered semantic search.
 */
class PTAI_Search {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Hook search filters and AJAX handlers if needed.
	}

	/**
	 * High-level search entry point.
	 *
	 * Decides between core_search() and ai_search() based on settings.
	 *
	 * @param string $query Search query.
	 * @param array  $args  Additional WP_Query-style args.
	 * @return array Formatted results.
	 */
	public function search( $query, $args = array() ) {
		// @todo Branch on PTAI_Settings::is_ai_enabled().
		return array();
	}

	/**
	 * Classic WP_Query-based keyword search.
	 *
	 * @param string $query Search query.
	 * @param array  $args  Additional WP_Query args.
	 * @return array
	 */
	public function core_search( $query, $args = array() ) {
		// @todo Build WP_Query with `s` and post_type=ptai_file.
		return array();
	}

	/**
	 * AI-powered semantic search using stored embeddings.
	 *
	 * @param string $query Search query.
	 * @param array  $args  Additional args.
	 * @return array
	 */
	public function ai_search( $query, $args = array() ) {
		// @todo Embed query, score against stored embeddings, sort, return top N.
		return array();
	}

	/**
	 * Compute cosine similarity between two vectors.
	 *
	 * @param array $vec_a Vector A.
	 * @param array $vec_b Vector B.
	 * @return float
	 */
	public function similarity_score( $vec_a, $vec_b ) {
		// @todo Implement cosine similarity.
		return 0.0;
	}

	/**
	 * Normalize a result set into a consistent shape.
	 *
	 * @param array $posts Array of WP_Post objects or IDs.
	 * @return array
	 */
	public function format_results( $posts ) {
		// @todo Return a uniform array (id, title, permalink, excerpt, score).
		return array();
	}
}
