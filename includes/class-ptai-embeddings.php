<?php
/**
 * Embedding storage and lifecycle management.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Embeddings
 *
 * Generates, stores, and retrieves OpenAI embeddings for ptai_file posts.
 */
class PTAI_Embeddings {

	/**
	 * Postmeta key used to store the embedding vector.
	 *
	 * @var string
	 */
	const META_KEY = '_ptai_embedding';

	/**
	 * Postmeta key used to store embedding metadata (model, hash, timestamp).
	 *
	 * @var string
	 */
	const META_KEY_INFO = '_ptai_embedding_info';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Hook on_save_post and on_delete_post to relevant WP hooks.
	}

	/**
	 * Generate (or regenerate) the embedding for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array|WP_Error Vector or error.
	 */
	public function generate_embedding( $post_id ) {
		// @todo Build text payload, call PTAI_OpenAI->get_embedding(), then store_embedding().
		return new WP_Error( 'ptai_not_implemented', __( 'Not implemented.', 'papertrail-ai' ) );
	}

	/**
	 * Store an embedding vector for a post.
	 *
	 * @param int   $post_id   Post ID.
	 * @param array $embedding Embedding vector.
	 * @return bool
	 */
	public function store_embedding( $post_id, $embedding ) {
		// @todo update_post_meta() with serialized vector and metadata.
		return false;
	}

	/**
	 * Retrieve a stored embedding vector.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null
	 */
	public function get_embedding( $post_id ) {
		// @todo get_post_meta() and unpack.
		return null;
	}

	/**
	 * Delete a stored embedding.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function delete_embedding( $post_id ) {
		// @todo delete_post_meta() for both META_KEY and META_KEY_INFO.
		return false;
	}

	/**
	 * Whether an embedding exists for the given post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function embedding_exists( $post_id ) {
		// @todo metadata_exists() check.
		return false;
	}

	/**
	 * Hook callback: regenerate embedding when a post is saved.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_save_post( $post_id ) {
		// @todo Skip autosaves/revisions, check post type, then regenerate.
	}

	/**
	 * Hook callback: clean up embeddings when a post is deleted.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_delete_post( $post_id ) {
		// @todo delete_embedding( $post_id ).
	}
}
