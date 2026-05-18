<?php
/**
 * OpenAI API client wrapper.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_OpenAI
 *
 * Thin wrapper around the OpenAI REST API for embeddings and chat completions.
 */
class PTAI_OpenAI {

	/**
	 * API base URL.
	 *
	 * @var string
	 */
	const API_BASE = 'https://api.openai.com/v1';

	/**
	 * Default embedding model.
	 *
	 * @var string
	 */
	const DEFAULT_EMBEDDING_MODEL = 'text-embedding-3-small';

	/**
	 * Default chat model.
	 *
	 * @var string
	 */
	const DEFAULT_CHAT_MODEL = 'gpt-4o-mini';

	/**
	 * Stored API key.
	 *
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * Constructor.
	 *
	 * @param string $api_key OpenAI API key.
	 */
	public function __construct( $api_key = '' ) {
		$this->api_key = is_string( $api_key ) ? trim( $api_key ) : '';
	}

	/**
	 * Generate an embedding vector for the given text.
	 *
	 * @param string $text Text to embed.
	 * @return array|WP_Error Embedding vector or error.
	 */
	public function get_embedding( $text ) {
		// @todo POST to /embeddings and return the vector array.
		return new WP_Error( 'ptai_not_implemented', __( 'Not implemented.', 'papertrail-ai' ) );
	}

	/**
	 * Run a chat completion request.
	 *
	 * @param array  $messages   Chat messages array.
	 * @param string $model      Model name.
	 * @param int    $max_tokens Max output tokens.
	 * @return array|WP_Error Response data or error.
	 */
	public function chat_completion( $messages, $model = 'gpt-4o-mini', $max_tokens = 500 ) {
		// @todo POST to /chat/completions and return the parsed response.
		return new WP_Error( 'ptai_not_implemented', __( 'Not implemented.', 'papertrail-ai' ) );
	}

	/**
	 * Parse a successful API response.
	 *
	 * @param array $response wp_remote_request response.
	 * @return array|WP_Error
	 */
	protected function handle_response( $response ) {
		// @todo Decode the JSON body and return the relevant data.
		return array();
	}

	/**
	 * Convert an API error into a WP_Error.
	 *
	 * @param array|WP_Error $response wp_remote_request response.
	 * @return WP_Error
	 */
	protected function handle_error( $response ) {
		// @todo Extract status code and OpenAI error message, return WP_Error.
		return new WP_Error( 'ptai_openai_error', __( 'OpenAI request failed.', 'papertrail-ai' ) );
	}

	/**
	 * Whether an API key is configured.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return '' !== $this->api_key;
	}
}
