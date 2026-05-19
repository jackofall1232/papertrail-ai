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
 * Generates, stores, and retrieves OpenAI embeddings for ptai_file
 * posts. The source text is assembled entirely from WordPress data
 * (title, excerpt, doc summary, category names) — no file parsing.
 */
class PTAI_Embeddings {

	const META_KEY      = '_ptai_embedding';
	const META_KEY_INFO = '_ptai_embedding_info';

	/**
	 * Constructor. Wires save/delete hooks.
	 */
	public function __construct() {
		add_action( 'save_post_' . PTAI_CPT, array( $this, 'on_save_post' ) );
		add_action( 'before_delete_post', array( $this, 'on_delete_post' ) );
	}

	/**
	 * Hook callback: regenerate the embedding when a ptai_file is saved.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_save_post( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$settings = new PTAI_Settings();
		if ( ! $settings->is_ai_enabled() ) {
			return;
		}

		$text = $this->build_source_text( $post_id );
		if ( '' === $text ) {
			return;
		}

		$key       = (string) PTAI_Settings::get_option( 'openai_api_key', '' );
		$openai    = new PTAI_OpenAI( $key );
		$embedding = $openai->get_embedding( $text );

		if ( false === $embedding ) {
			return;
		}

		$this->store_embedding( $post_id, $embedding );

		update_post_meta(
			$post_id,
			self::META_KEY_INFO,
			wp_json_encode(
				array(
					'model'         => PTAI_OpenAI::EMBEDDING_MODEL,
					'dims'          => count( $embedding ),
					'generated_at'  => current_time( 'mysql' ),
					'source_length' => strlen( $text ),
					'source_fields' => $this->get_source_fields( $post_id ),
				)
			)
		);
	}

	/**
	 * Build the text used as the embedding source.
	 *
	 * @param int $post_id Post ID.
	 * @return string Assembled text, or '' if no usable content.
	 */
	private function build_source_text( $post_id ) {
		$pieces = array();

		$title = wp_strip_all_tags( (string) get_the_title( $post_id ) );
		$title = trim( $title );
		if ( '' !== $title ) {
			$pieces[] = $title;
		}

		$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $post_id ) );
		$excerpt = trim( $excerpt );
		if ( '' !== $excerpt ) {
			$pieces[] = $excerpt;
		}

		$summary = wp_strip_all_tags( (string) get_post_meta( $post_id, '_ptai_doc_summary', true ) );
		$summary = trim( $summary );
		if ( '' !== $summary ) {
			$pieces[] = $summary;
		}

		$terms = get_the_terms( $post_id, PTAI_TAXONOMY );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$names = array();
			foreach ( $terms as $term ) {
				if ( isset( $term->name ) ) {
					$name = trim( wp_strip_all_tags( (string) $term->name ) );
					if ( '' !== $name ) {
						$names[] = $name;
					}
				}
			}
			if ( ! empty( $names ) ) {
				$pieces[] = implode( ', ', $names );
			}
		}

		if ( empty( $pieces ) ) {
			return '';
		}

		$text = implode( "\n", $pieces );
		$text = preg_replace( '/\s+/', ' ', $text );
		$text = (string) $text;
		$text = substr( $text, 0, PTAI_OpenAI::MAX_EMBEDDING_CHARS );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'PTAI_DEBUG' ) && PTAI_DEBUG ) {
			error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				'PaperTrail AI [embeddings]: source fields = '
				. implode( ',', $this->get_source_fields( $post_id ) )
			);
		}

		return $text;
	}

	/**
	 * List the source fields that contributed non-empty content.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,string>
	 */
	private function get_source_fields( $post_id ) {
		$fields = array();

		if ( '' !== trim( wp_strip_all_tags( (string) get_the_title( $post_id ) ) ) ) {
			$fields[] = 'title';
		}
		if ( '' !== trim( wp_strip_all_tags( (string) get_post_field( 'post_excerpt', $post_id ) ) ) ) {
			$fields[] = 'excerpt';
		}
		if ( '' !== trim( wp_strip_all_tags( (string) get_post_meta( $post_id, '_ptai_doc_summary', true ) ) ) ) {
			$fields[] = 'doc_summary';
		}
		$terms = get_the_terms( $post_id, PTAI_TAXONOMY );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$fields[] = 'categories';
		}

		return $fields;
	}

	/**
	 * Public entry point for explicit regeneration.
	 *
	 * Skips post-status / autosave / revision checks — admins may force a
	 * regen on draft or non-current posts.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True on success.
	 */
	public function generate_embedding( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}
		$settings = new PTAI_Settings();
		if ( ! $settings->is_ai_enabled() ) {
			return false;
		}

		$text = $this->build_source_text( $post_id );
		if ( '' === $text ) {
			return false;
		}

		$key       = (string) PTAI_Settings::get_option( 'openai_api_key', '' );
		$openai    = new PTAI_OpenAI( $key );
		$embedding = $openai->get_embedding( $text );

		if ( false === $embedding ) {
			return false;
		}

		$stored = $this->store_embedding( $post_id, $embedding );
		if ( ! $stored ) {
			return false;
		}

		update_post_meta(
			$post_id,
			self::META_KEY_INFO,
			wp_json_encode(
				array(
					'model'         => PTAI_OpenAI::EMBEDDING_MODEL,
					'dims'          => count( $embedding ),
					'generated_at'  => current_time( 'mysql' ),
					'source_length' => strlen( $text ),
					'source_fields' => $this->get_source_fields( $post_id ),
				)
			)
		);

		return true;
	}

	/**
	 * Persist an embedding vector to post meta.
	 *
	 * @param int               $post_id   Post ID.
	 * @param array<int,float>  $embedding Vector.
	 * @return bool
	 */
	public function store_embedding( $post_id, array $embedding ) {
		$encoded = wp_json_encode( $embedding );
		if ( ! $encoded || '' === $encoded ) {
			return false;
		}
		return false !== update_post_meta( $post_id, self::META_KEY, $encoded );
	}

	/**
	 * Retrieve a stored embedding vector.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,float>|false
	 */
	public function get_embedding( $post_id ) {
		$raw = get_post_meta( $post_id, self::META_KEY, true );
		if ( empty( $raw ) ) {
			return false;
		}
		$embedding = json_decode( (string) $raw, true );
		if ( ! is_array( $embedding ) ) {
			return false;
		}
		if ( count( $embedding ) !== PTAI_OpenAI::EMBEDDING_DIMS ) {
			return false;
		}
		return $embedding;
	}

	/**
	 * Delete both the vector and its info blob.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function delete_embedding( $post_id ) {
		delete_post_meta( $post_id, self::META_KEY );
		delete_post_meta( $post_id, self::META_KEY_INFO );
	}

	/**
	 * Whether a valid embedding is stored.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public function embedding_exists( $post_id ) {
		return false !== $this->get_embedding( $post_id );
	}

	/**
	 * Hook callback: clean up embeddings when a post is deleted.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_delete_post( $post_id ) {
		if ( PTAI_CPT !== get_post_type( $post_id ) ) {
			return;
		}
		$this->delete_embedding( $post_id );
	}

	/**
	 * High-level status used by row badges and notices.
	 *
	 * @param int $post_id Post ID.
	 * @return string One of 'disabled' | 'missing' | 'stale' | 'current'.
	 */
	public function get_embedding_status( $post_id ) {
		$settings = new PTAI_Settings();
		if ( ! $settings->is_ai_enabled() ) {
			return 'disabled';
		}
		if ( ! $this->embedding_exists( $post_id ) ) {
			return 'missing';
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return 'missing';
		}

		$raw  = get_post_meta( $post_id, self::META_KEY_INFO, true );
		$info = json_decode( (string) $raw, true );

		if ( is_array( $info ) && isset( $info['generated_at'] ) ) {
			$generated = strtotime( (string) $info['generated_at'] );
			$modified  = strtotime( (string) $post->post_modified );
			if ( is_int( $generated ) && is_int( $modified ) && $generated < $modified ) {
				return 'stale';
			}
		}

		return 'current';
	}
}
