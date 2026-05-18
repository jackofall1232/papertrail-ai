<?php
/**
 * Admin-side controller.
 *
 * @package PaperTrail_AI
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTAI_Admin
 *
 * Handles admin scripts/styles, meta boxes, list-table columns,
 * the settings submenu, and plugin action links.
 */
class PTAI_Admin {

	/**
	 * Page slug for the settings screen (sits under the CPT menu).
	 *
	 * @var string
	 */
	const SETTINGS_PAGE_SLUG = 'ptai-settings';

	/**
	 * Settings controller, used to render the form on the settings screen.
	 *
	 * @var PTAI_Settings|null
	 */
	private $settings;

	/**
	 * Constructor. Wires admin hooks directly so the class is self-contained
	 * regardless of how PTAI_Loader is wired.
	 */
	public function __construct() {
		if ( class_exists( 'PTAI_Settings' ) ) {
			$this->settings = new PTAI_Settings();
		}

		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'add_meta_boxes_' . PTAI_CPT, array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . PTAI_CPT, array( $this, 'save_meta_box' ) );

		add_filter( 'manage_' . PTAI_CPT . '_posts_columns', array( $this, 'add_admin_columns' ) );
		add_action( 'manage_' . PTAI_CPT . '_posts_custom_column', array( $this, 'populate_admin_column' ), 10, 2 );
		add_filter( 'manage_edit-' . PTAI_CPT . '_sortable_columns', array( $this, 'make_admin_columns_sortable' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_sortable_columns_query' ) );

		add_filter( 'plugin_action_links_' . PTAI_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
	}

	/**
	 * Register the settings submenu under the Documents CPT.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		add_submenu_page(
			'edit.php?post_type=' . PTAI_CPT,
			__( 'PaperTrail AI Settings', 'papertrail-ai' ),
			__( 'Settings', 'papertrail-ai' ),
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Determine whether the current screen is a PaperTrail AI screen.
	 *
	 * @param string $hook Current admin page hook.
	 * @return bool
	 */
	private function is_plugin_screen( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen instanceof WP_Screen ) {
			if ( PTAI_CPT === $screen->post_type ) {
				return true;
			}
			if ( false !== strpos( (string) $screen->id, self::SETTINGS_PAGE_SLUG ) ) {
				return true;
			}
		}

		return false !== strpos( (string) $hook, self::SETTINGS_PAGE_SLUG );
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_styles( $hook ) {
		if ( ! $this->is_plugin_screen( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'ptai-admin',
			PTAI_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			PTAI_VERSION
		);
	}

	/**
	 * Enqueue admin scripts and the media library.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! $this->is_plugin_screen( $hook ) ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'ptai-admin',
			PTAI_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			PTAI_VERSION,
			true
		);

		wp_localize_script(
			'ptai-admin',
			'ptaiAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ptai_admin_nonce' ),
				'strings'  => array(
					'select_file'       => __( 'Select or upload a file', 'papertrail-ai' ),
					'use_this_file'     => __( 'Use this file', 'papertrail-ai' ),
					'replace_file'      => __( 'Replace file', 'papertrail-ai' ),
					'remove_file'       => __( 'Remove file', 'papertrail-ai' ),
					'no_file_attached'  => __( 'No file attached.', 'papertrail-ai' ),
					'confirm_remove'    => __( 'Remove the attached file? This will not delete it from the media library.', 'papertrail-ai' ),
				),
			)
		);
	}

	/**
	 * Register meta boxes for the ptai_file CPT.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'ptai_file_details',
			__( 'File Details', 'papertrail-ai' ),
			array( $this, 'render_file_meta_box' ),
			PTAI_CPT,
			'side',
			'high'
		);

		add_meta_box(
			'ptai_upgrade_sidebar',
			__( 'Ask Adam Pro', 'papertrail-ai' ),
			array( $this, 'render_upgrade_meta_box' ),
			PTAI_CPT,
			'side',
			'low'
		);
	}

	/**
	 * Render the file details meta box.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public function render_file_meta_box( $post ) {
		wp_nonce_field( 'ptai_save_file_meta', 'ptai_file_meta_nonce' );

		$file_id         = (int) get_post_meta( $post->ID, '_ptai_file_id', true );
		$file_type       = (string) get_post_meta( $post->ID, '_ptai_file_type', true );
		$file_size       = (int) get_post_meta( $post->ID, '_ptai_file_size', true );
		$file_ext        = (string) get_post_meta( $post->ID, '_ptai_file_ext', true );
		$download_count  = (int) get_post_meta( $post->ID, '_ptai_download_count', true );
		$last_downloaded = (string) get_post_meta( $post->ID, '_ptai_last_downloaded', true );

		$attachment_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
		$file_name      = $file_id ? get_the_title( $file_id ) : '';

		echo '<div class="ptai-file-meta">';
		printf(
			'<input type="hidden" id="ptai_file_id" name="ptai_file_id" value="%d" />',
			(int) $file_id
		);

		echo '<div class="ptai-file-meta__current" id="ptai-file-meta-current">';
		if ( $file_id && $attachment_url ) {
			$icon = $this->icon_for_mime( $file_type );
			echo '<p><span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true"></span> ';
			echo '<strong>' . esc_html( $file_name ) . '</strong></p>';

			echo '<ul style="margin:0 0 0.75em 0;">';
			if ( $file_ext ) {
				echo '<li>' . esc_html(
					sprintf(
						/* translators: %s: file extension. */
						__( 'Extension: %s', 'papertrail-ai' ),
						strtoupper( $file_ext )
					)
				) . '</li>';
			}
			if ( $file_type ) {
				echo '<li>' . esc_html(
					sprintf(
						/* translators: %s: MIME type. */
						__( 'Type: %s', 'papertrail-ai' ),
						$file_type
					)
				) . '</li>';
			}
			if ( $file_size ) {
				echo '<li>' . esc_html(
					sprintf(
						/* translators: %s: human-readable file size. */
						__( 'Size: %s', 'papertrail-ai' ),
						size_format( $file_size )
					)
				) . '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p><em>' . esc_html__( 'No file attached.', 'papertrail-ai' ) . '</em></p>';
		}
		echo '</div>';

		printf(
			'<p><button type="button" class="button" id="ptai-attach-file">%s</button> ',
			esc_html( $file_id ? __( 'Replace File', 'papertrail-ai' ) : __( 'Attach File', 'papertrail-ai' ) )
		);
		if ( $file_id ) {
			printf(
				'<button type="button" class="button-link" id="ptai-remove-file">%s</button>',
				esc_html__( 'Remove', 'papertrail-ai' )
			);
		}
		echo '</p>';

		echo '<hr />';
		echo '<p><strong>' . esc_html__( 'Download statistics', 'papertrail-ai' ) . '</strong></p>';
		echo '<ul style="margin:0;">';
		echo '<li>' . esc_html(
			sprintf(
				/* translators: %d: number of downloads. */
				_n( '%d download', '%d downloads', max( 1, $download_count ), 'papertrail-ai' ),
				$download_count
			)
		) . '</li>';
		if ( '' !== $last_downloaded ) {
			$timestamp = strtotime( $last_downloaded );
			$display   = $timestamp ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) : $last_downloaded;
			echo '<li>' . esc_html(
				sprintf(
					/* translators: %s: formatted date/time. */
					__( 'Last download: %s', 'papertrail-ai' ),
					$display
				)
			) . '</li>';
		} else {
			echo '<li>' . esc_html__( 'Never downloaded.', 'papertrail-ai' ) . '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render the upgrade sidebar meta box.
	 *
	 * @return void
	 */
	public function render_upgrade_meta_box() {
		if ( class_exists( 'PTAI_Pro' ) ) {
			$pro = new PTAI_Pro();
			$pro->render_upgrade_sidebar();
		}
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_meta_box( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['ptai_file_meta_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['ptai_file_meta_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'ptai_save_file_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$file_id = isset( $_POST['ptai_file_id'] ) ? absint( wp_unslash( $_POST['ptai_file_id'] ) ) : 0;

		if ( 0 === $file_id ) {
			delete_post_meta( $post_id, '_ptai_file_id' );
			delete_post_meta( $post_id, '_ptai_file_type' );
			delete_post_meta( $post_id, '_ptai_file_size' );
			delete_post_meta( $post_id, '_ptai_file_ext' );
			return;
		}

		// Validate the attachment exists and is an attachment.
		$attachment = get_post( $file_id );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return;
		}

		$path = get_attached_file( $file_id );
		if ( ! $path ) {
			return;
		}

		$mime = (string) get_post_mime_type( $file_id );
		$ext  = strtolower( (string) pathinfo( $path, PATHINFO_EXTENSION ) );
		$size = file_exists( $path ) ? (int) filesize( $path ) : 0;

		update_post_meta( $post_id, '_ptai_file_id', $file_id );
		update_post_meta( $post_id, '_ptai_file_type', sanitize_text_field( $mime ) );
		update_post_meta( $post_id, '_ptai_file_size', $size );
		update_post_meta( $post_id, '_ptai_file_ext', sanitize_text_field( $ext ) );
	}

	/**
	 * Add custom columns to the CPT list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		$new_columns = array();

		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}

		$new_columns['title']           = isset( $columns['title'] ) ? $columns['title'] : __( 'Title', 'papertrail-ai' );
		$new_columns['file_type']       = __( 'File Type', 'papertrail-ai' );
		$new_columns['file_size']       = __( 'File Size', 'papertrail-ai' );
		$new_columns['download_count']  = __( 'Downloads', 'papertrail-ai' );
		$new_columns['last_downloaded'] = __( 'Last Downloaded', 'papertrail-ai' );

		// Preserve the taxonomy column if WP added it.
		$tax_col = 'taxonomy-' . PTAI_TAXONOMY;
		if ( isset( $columns[ $tax_col ] ) ) {
			$new_columns[ $tax_col ] = $columns[ $tax_col ];
		}

		$new_columns['date'] = isset( $columns['date'] ) ? $columns['date'] : __( 'Date', 'papertrail-ai' );

		return $new_columns;
	}

	/**
	 * Populate custom admin columns.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function populate_admin_column( $column, $post_id ) {
		switch ( $column ) {
			case 'file_type':
				$mime = (string) get_post_meta( $post_id, '_ptai_file_type', true );
				$ext  = (string) get_post_meta( $post_id, '_ptai_file_ext', true );
				if ( '' === $mime && '' === $ext ) {
					echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'No file', 'papertrail-ai' ) . '</span>';
					return;
				}
				$icon  = $this->icon_for_mime( $mime );
				$label = '' !== $ext ? strtoupper( $ext ) : $mime;
				printf(
					'<span class="dashicons %1$s" aria-hidden="true"></span> <span>%2$s</span>',
					esc_attr( $icon ),
					esc_html( $label )
				);
				break;

			case 'file_size':
				$size = (int) get_post_meta( $post_id, '_ptai_file_size', true );
				echo $size > 0 ? esc_html( size_format( $size ) ) : '<span aria-hidden="true">—</span>';
				break;

			case 'download_count':
				$count = (int) get_post_meta( $post_id, '_ptai_download_count', true );
				echo esc_html( number_format_i18n( $count ) );
				break;

			case 'last_downloaded':
				$last = (string) get_post_meta( $post_id, '_ptai_last_downloaded', true );
				if ( '' === $last ) {
					echo '<span aria-hidden="true">—</span>';
					return;
				}
				$timestamp = strtotime( $last );
				$format    = get_option( 'date_format' );
				echo $timestamp ? esc_html( date_i18n( $format, $timestamp ) ) : esc_html( $last );
				break;
		}
	}

	/**
	 * Mark custom columns sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function make_admin_columns_sortable( $columns ) {
		$columns['download_count']  = 'download_count';
		$columns['last_downloaded'] = 'last_downloaded';
		return $columns;
	}

	/**
	 * Translate sortable column requests into meta_query orderby.
	 *
	 * @param WP_Query $query Current query.
	 * @return void
	 */
	public function handle_sortable_columns_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( PTAI_CPT !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( 'download_count' === $orderby ) {
			$query->set( 'meta_key', '_ptai_download_count' );
			$query->set( 'orderby', 'meta_value_num' );
		} elseif ( 'last_downloaded' === $orderby ) {
			$query->set( 'meta_key', '_ptai_last_downloaded' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Add action links on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$settings_url = admin_url( 'edit.php?post_type=' . PTAI_CPT . '&page=' . self::SETTINGS_PAGE_SLUG );

		$prepend = array(
			'settings' => sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $settings_url ),
				esc_html__( 'Settings', 'papertrail-ai' )
			),
			'upgrade'  => sprintf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer" style="color:#2271b1;font-weight:600;">%2$s</a>',
				esc_url( 'https://askadamit.com/purchase' ),
				esc_html__( 'Upgrade to Pro', 'papertrail-ai' )
			),
		);

		return array_merge( $prepend, $links );
	}

	/**
	 * Render the plugin settings page (two-column layout).
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap ptai-settings-wrap">
			<h1><?php esc_html_e( 'PaperTrail AI Settings', 'papertrail-ai' ); ?></h1>
			<?php settings_errors(); ?>

			<div class="ptai-settings-layout" style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
				<div class="ptai-settings-main" style="flex:1 1 600px;min-width:320px;">
					<form method="post" action="options.php">
						<?php
						settings_fields( PTAI_Settings::SETTINGS_GROUP );
						do_settings_sections( PTAI_Settings::PAGE_SLUG );
						submit_button();
						?>
					</form>
				</div>
				<div class="ptai-settings-sidebar" style="flex:0 1 320px;min-width:280px;">
					<?php
					if ( class_exists( 'PTAI_Pro' ) ) {
						$pro = new PTAI_Pro();
						$pro->render_upgrade_sidebar();
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Choose a Dashicon for a MIME type.
	 *
	 * @param string $mime MIME type.
	 * @return string Dashicon class name.
	 */
	private function icon_for_mime( $mime ) {
		$mime = (string) $mime;
		if ( 0 === strpos( $mime, 'image/' ) ) {
			return 'dashicons-format-image';
		}
		if ( 0 === strpos( $mime, 'video/' ) ) {
			return 'dashicons-format-video';
		}
		if ( 0 === strpos( $mime, 'audio/' ) ) {
			return 'dashicons-format-audio';
		}
		if ( 'application/pdf' === $mime ) {
			return 'dashicons-pdf';
		}
		if ( false !== strpos( $mime, 'word' ) || false !== strpos( $mime, 'text/' ) ) {
			return 'dashicons-media-document';
		}
		if ( false !== strpos( $mime, 'sheet' ) || false !== strpos( $mime, 'excel' ) || 'text/csv' === $mime ) {
			return 'dashicons-media-spreadsheet';
		}
		if ( false !== strpos( $mime, 'presentation' ) || false !== strpos( $mime, 'powerpoint' ) ) {
			return 'dashicons-media-interactive';
		}
		return 'dashicons-media-default';
	}
}
