<?php
/**
 * Public/frontend controller.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Public
 *
 * Handles public-facing scripts/styles, template loading, and AJAX search.
 */
class PTAI_Public {

	/**
	 * Site-wide AI search rate limit per hour. Free version cap.
	 * Prevents the plugin from becoming an open OpenAI proxy.
	 */
	const RATE_LIMIT_PER_HOUR = 60;

	/**
	 * Constructor — wires hooks directly.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );

		add_action( 'wp_ajax_ptai_search', array( $this, 'handle_search_ajax' ) );
		add_action( 'wp_ajax_nopriv_ptai_search', array( $this, 'handle_search_ajax' ) );
	}

	/**
	 * Whether the current request should load the public assets.
	 *
	 * @return bool
	 */
	private function should_enqueue_assets() {
		if ( is_singular( PTAI_CPT ) ) {
			return true;
		}
		if ( is_post_type_archive( PTAI_CPT ) ) {
			return true;
		}
		if ( is_tax( PTAI_TAXONOMY ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();
			if ( $post && has_shortcode( (string) $post->post_content, PTAI_Shortcode::TAG ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue public styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( ! $this->should_enqueue_assets() ) {
			return;
		}
		wp_enqueue_style(
			'papertrail-ai-public',
			PTAI_PLUGIN_URL . 'public/css/public.css',
			array(),
			PTAI_VERSION
		);
	}

	/**
	 * Enqueue public scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->should_enqueue_assets() ) {
			return;
		}
		wp_enqueue_script(
			'papertrail-ai-public',
			PTAI_PLUGIN_URL . 'public/js/public.js',
			array( 'jquery' ),
			PTAI_VERSION,
			true
		);
		wp_localize_script(
			'papertrail-ai-public',
			'ptaiPublic',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ptai_public_search' ),
				'strings'  => array(
					'searching'  => __( 'Searching...', 'papertrail-ai' ),
					'no_results' => __( 'No documents found.', 'papertrail-ai' ),
					'error'      => __( 'Search error. Please try again.', 'papertrail-ai' ),
					'search'     => __( 'Search', 'papertrail-ai' ),
					'download'   => __( 'Download', 'papertrail-ai' ),
				),
			)
		);
	}

	/**
	 * Override single/archive templates for the CPT, respecting theme overrides.
	 *
	 * @param string $template Template path resolved by WordPress.
	 * @return string
	 */
	public function template_include( $template ) {
		// Single document.
		if ( is_singular( PTAI_CPT ) ) {
			$theme = get_stylesheet_directory() . '/papertrail-ai/single-ptai.php';
			if ( file_exists( $theme ) ) {
				return $theme;
			}
			$plugin = PTAI_PLUGIN_DIR . 'templates/single-ptai.php';
			if ( file_exists( $plugin ) ) {
				return $plugin;
			}
		}

		// Archive / taxonomy archive.
		if ( is_post_type_archive( PTAI_CPT ) || is_tax( PTAI_TAXONOMY ) ) {
			$theme = get_stylesheet_directory() . '/papertrail-ai/archive-ptai.php';
			if ( file_exists( $theme ) ) {
				return $theme;
			}
			$plugin = PTAI_PLUGIN_DIR . 'templates/archive-ptai.php';
			if ( file_exists( $plugin ) ) {
				return $plugin;
			}
		}

		return $template;
	}

	/**
	 * Apply the shared site-wide AI search rate limit.
	 *
	 * Returns the effective mode — may be downgraded to 'core' when the
	 * hourly bucket is exhausted. Prevents the free plugin from becoming
	 * an open OpenAI proxy. No PII, no IP — a single salted bucket per hour.
	 *
	 * @param string $mode Requested mode.
	 * @return string Effective mode after limiting.
	 */
	public static function apply_rate_limit( $mode ) {
		$mode = sanitize_key( (string) $mode );
		if ( 'core' === $mode ) {
			return 'core';
		}

		$window = current_time( 'Y-m-d-H' );
		$salt   = wp_salt( 'auth' );
		$token  = hash( 'sha256', 'search' . $window . $salt );
		$key    = 'ptai_srch_' . substr( $token, 0, 40 );
		$count  = (int) get_transient( $key );

		if ( $count >= self::RATE_LIMIT_PER_HOUR ) {
			return 'core';
		}

		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		return $mode;
	}

	/**
	 * Map a list of WP_Post objects to safe-for-JSON associative arrays.
	 *
	 * @param array $posts WP_Post[].
	 * @return array
	 */
	public static function format_posts_for_response( $posts ) {
		$out = array();
		if ( ! is_array( $posts ) ) {
			return $out;
		}
		foreach ( $posts as $post ) {
			if ( ! ( $post instanceof WP_Post ) ) {
				continue;
			}
			$out[] = array(
				'id'           => absint( $post->ID ),
				'title'        => esc_html( get_the_title( $post ) ),
				'permalink'    => esc_url( get_permalink( $post ) ),
				'excerpt'      => esc_html( wp_trim_words( get_the_excerpt( $post ), 20 ) ),
				'file_type'    => esc_html( (string) get_post_meta( $post->ID, '_ptai_file_type', true ) ),
				'file_size'    => esc_html( size_format( absint( get_post_meta( $post->ID, '_ptai_file_size', true ) ) ) ),
				'downloads'    => absint( get_post_meta( $post->ID, '_ptai_download_count', true ) ),
				'download_url' => esc_url( get_rest_url( null, 'papertrail-ai/v1/download/' . absint( $post->ID ) ) ),
			);
		}
		return $out;
	}

	/**
	 * Handle AJAX search request.
	 *
	 * @return void
	 */
	public function handle_search_ajax() {
		check_ajax_referer( 'ptai_public_search', 'nonce' );

		$query    = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		$category = isset( $_POST['category'] ) ? absint( wp_unslash( $_POST['category'] ) ) : 0;
		$page     = isset( $_POST['page'] ) ? max( 1, absint( wp_unslash( $_POST['page'] ) ) ) : 1;
		$mode     = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'auto';

		if ( ! in_array( $mode, array( 'auto', 'ai', 'core' ), true ) ) {
			$mode = 'auto';
		}

		// Apply rate limit only when AI is possible.
		$mode = self::apply_rate_limit( $mode );

		$search  = new PTAI_Search();
		$results = $search->search(
			$query,
			array(
				'per_page' => 10,
				'page'     => $page,
				'category' => $category,
				'mode'     => $mode,
			)
		);

		wp_send_json_success(
			array(
				'posts'     => self::format_posts_for_response( $results['posts'] ),
				'total'     => absint( $results['total'] ),
				'pages'     => absint( $results['pages'] ),
				'mode_used' => sanitize_key( $results['mode_used'] ),
			)
		);
		wp_die();
	}
}
