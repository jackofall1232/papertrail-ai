<?php
/**
 * Settings page and option handling.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Settings
 *
 * Registers the admin settings screen and handles option access.
 */
class PTAI_Settings {

	/**
	 * Option name for the settings array.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'ptai_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @todo Hook register_settings to `admin_init`, add settings page to admin menu.
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		// @todo Use register_setting()/add_settings_section()/add_settings_field().
	}

	/**
	 * Render the settings page HTML.
	 *
	 * @return void
	 */
	public function settings_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// @todo Render the settings form with settings_fields() and do_settings_sections().
		echo '<div class="wrap"><h1>' . esc_html__( 'PaperTrail AI Settings', 'papertrail-ai' ) . '</h1></div>';
	}

	/**
	 * Sanitize the OpenAI API key.
	 *
	 * @param string $value Raw API key.
	 * @return string Sanitized API key.
	 */
	public function sanitize_api_key( $value ) {
		// @todo Trim and validate the API key format.
		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * Sanitize the full settings array.
	 *
	 * @param array $input Raw settings input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		// @todo Sanitize each field individually.
		return is_array( $input ) ? $input : array();
	}

	/**
	 * Get a single option value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default if not set.
	 * @return mixed
	 */
	public static function get_option( $key, $default = null ) {
		$settings = get_option( self::OPTION_NAME, array() );
		if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}
		return $default;
	}

	/**
	 * Whether AI features are available.
	 *
	 * The sole criterion is: an OpenAI API key has been saved.
	 * No Pro check. No license check. Key present = AI on.
	 *
	 * @return bool
	 */
	public static function is_ai_enabled() {
		// @todo Return true iff a non-empty `openai_api_key` is stored in PTAI settings.
		return false;
	}
}
