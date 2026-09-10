<?php
class FLBuilderGlobalImportExport {

	public function __construct() {
		add_action( 'wp_ajax_export_global_settings', array( $this, 'export_data' ) );
		add_action( 'wp_ajax_import_global_settings', array( $this, 'import_data' ) );
		add_action( 'wp_ajax_reset_global_settings', array( $this, 'reset_data' ) );
		add_action( 'wp_ajax_save_settings_snapshot', array( $this, 'save_snapshot' ) );
		add_action( 'wp_ajax_restore_settings_snapshot', array( $this, 'restore_snapshot' ) );
		add_action( 'wp_ajax_delete_settings_snapshot', array( $this, 'delete_snapshot' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'allow_import' ), 10, 4 );

		add_action( 'admin_enqueue_scripts', function ( $hook ) {
			if ( 'settings_page_fl-builder-settings' === $hook ) {
				wp_enqueue_script( 'fl-builder-global-import-export', FLBuilder::plugin_url() . 'js/fl-builder-global-import-export.js', array( 'jquery' ), FL_BUILDER_VERSION );
				wp_localize_script( 'fl-builder-global-import-export', 'FLBuilderAdminImportExportConfig', array(
					'select'       => __( 'Import Settings', 'fl-builder' ),
					'snapshots'    => self::get_snapshot_meta( self::get_snapshots() ),
					'maxSnapshots' => 5,
				));
			}
		});
	}

	/**
	 * Collects all current settings into a single array.
	 * Reused by export_data() and snapshot saving.
	 *
	 * @since 2.11
	 * @return array
	 */
	static public function collect_all_settings() {
		$settings       = array();
		$admin_settings = array();

		$settings['builder_global_settings'] = FLBuilderModel::get_global_settings();

		foreach ( FLBuilderAdminSettings::registered_settings() as $setting ) {
			$admin_settings[ $setting ] = get_option( $setting );
		}

		$settings['admin_settings'] = $admin_settings;

		if ( class_exists( 'FLBuilderGlobalStyles' ) ) {
			$globals = FLBuilderGlobalStyles::get_settings( false );
			$colors  = $globals->colors;
			unset( $globals->colors );
			$settings['global_styles'] = $globals;
			$settings['global_colors'] = $colors;
		}

		return $settings;
	}

	/**
	 * Applies a settings array to the database.
	 * Reused by import_data() and snapshot restoring.
	 *
	 * Callers are responsible for capability checks before calling this method.
	 * Only admin_settings keys registered via FLBuilderAdminSettings::registered_settings()
	 * are written; any other keys in the data are silently ignored.
	 *
	 * @since 2.11
	 * @param array $data Settings array with keys: builder_global_settings, admin_settings, global_styles, global_colors.
	 */
	static public function apply_settings( $data ) {
		do_action( 'fl_builder_before_apply_settings', $data );

		if ( isset( $data['builder_global_settings'] ) ) {
			update_option( '_fl_builder_settings', $data['builder_global_settings'], true );
		}

		if ( isset( $data['admin_settings'] ) ) {
			$allowed_keys = FLBuilderAdminSettings::registered_settings();
			foreach ( $data['admin_settings'] as $key => $setting ) {
				if ( in_array( $key, $allowed_keys, true ) ) {
					update_option( $key, $setting, true );
				}
			}
		}

		$globals = get_option( '_fl_builder_styles' );
		if ( isset( $data['global_styles'] ) ) {
			$backup_colors        = isset( $globals->colors ) ? $globals->colors : array();
			$new_settings         = (object) $data['global_styles'];
			$new_settings->colors = $backup_colors;
			$globals              = $new_settings;
			FLBuilderUtils::update_option( '_fl_builder_styles', $globals, true );
		}

		if ( isset( $data['global_colors'] ) ) {
			$globals = $globals ? $globals : FLBuilderGlobalStyles::get_settings( false );
			$current = $globals->colors;

			$new = array_merge( (array) $current, (array) $data['global_colors'] );

			$serialized      = array_map( 'serialize', $new );
			$unique          = array_unique( $serialized );
			$globals->colors = array_intersect_key( $new, $unique );

			foreach ( $globals->colors as $k => $color ) {
				if ( empty( $color ) || ! $color['color'] ) {
					unset( $globals->colors[ $k ] );
				}
			}

			if ( isset( $data['global_colors_prefix'] ) ) {
				$globals->prefix = $data['global_colors_prefix'];
			}

			if ( ! isset( $globals->colors[0] ) && ! empty( $globals->colors ) ) {
				$fixed_globals    = array();
				$fixed_globals[0] = array_shift( $globals->colors );
				$globals->colors  = array_merge( $fixed_globals, $globals->colors );
			}
			FLBuilderUtils::update_option( '_fl_builder_styles', $globals, true );
		}

		FLBuilderModel::delete_asset_cache_for_all_posts();

		do_action( 'fl_builder_after_apply_settings', $data );
	}

	/**
	 * @since 2.6
	 */
	static public function export_data() {

		check_admin_referer( 'fl_builder_import_export' );

		if ( current_user_can( 'manage_options' ) ) {

			$data     = $_REQUEST['data'];
			$settings = self::collect_all_settings();

			// filter data based on checkbox selections
			if ( 'false' === $data['global_all'] ) {
				if ( 'false' === $data['global'] ) {
					unset( $settings['builder_global_settings'] );
				}
				if ( 'false' === $data['admin'] ) {
					unset( $settings['admin_settings'] );
				}
				if ( 'false' === $data['colors'] ) {
					unset( $settings['global_colors'] );
				}
				if ( 'false' === $data['styles'] ) {
					unset( $settings['global_styles'] );
				}

				// prefix
				if ( isset( $settings['global_colors'] ) && class_exists( 'FLBuilderGlobalStyles' ) ) {
					$globals = FLBuilderGlobalStyles::get_settings( false );
					if ( isset( $globals->prefix ) ) {
						$settings['global_colors_prefix'] = $globals->prefix;
					}
				}
			}

			if ( ! $settings ) {
				wp_send_json_error( 'No settings found' );
			}
			wp_send_json_success( array(
				'selected' => $data,
				'settings' => serialize( $settings ),
			) );
		} else {
			wp_send_json_error();
		}
	}

	public function import_data() {

		check_admin_referer( 'fl_builder_import_export' );

		if ( current_user_can( 'manage_options' ) ) {

			$id   = $_POST['importid'];
			$path = get_attached_file( $id );

			if ( ! $path ) {
				wp_send_json_error( 'Could not find file!' );
			}

			$data = file_get_contents( $path );

			if ( is_object( json_decode( $data ) ) ) {
				wp_send_json_error( 'Exports completed with versions prior to 2.8.1 are not compatible due to a change in format of export data. Import aborted.' );
			}

			if ( ! is_serialized( $data ) ) {
				wp_send_json_error( 'Could not parse file!' );
			}

			$data = maybe_unserialize( $data );
			self::apply_settings( $data );
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	public function reset_data() {

		check_admin_referer( 'fl_builder_import_export' );

		if ( current_user_can( 'manage_options' ) ) {
			delete_option( '_fl_builder_styles' );
			delete_option( '_fl_builder_settings' );
			FLBuilderModel::delete_asset_cache_for_all_posts();
			foreach ( FLBuilderAdminSettings::registered_settings() as $setting ) {
				delete_option( $setting );
			}
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Returns all stored snapshots.
	 *
	 * @since 2.11
	 * @return array
	 */
	static public function get_snapshots() {
		return get_option( '_fl_builder_snapshots', array() );
	}

	/**
	 * Returns snapshot metadata without the full settings data.
	 * Used for AJAX responses and JS localization where only
	 * name and created_at are needed.
	 *
	 * @since 2.11
	 * @param array $snapshots Full snapshots array.
	 * @return array
	 */
	static public function get_snapshot_meta( $snapshots ) {
		return array_map( function ( $s ) {
			return array(
				'name'       => $s['name'],
				'created_at' => $s['created_at'],
			);
		}, $snapshots );
	}

	/**
	 * Saves a snapshot of the current settings.
	 *
	 * @since 2.11
	 */
	public function save_snapshot() {

		check_admin_referer( 'fl_builder_import_export' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$name = isset( $_POST['snapshot_name'] ) ? sanitize_text_field( $_POST['snapshot_name'] ) : '';
		if ( empty( $name ) ) {
			$name = 'Snapshot ' . gmdate( 'Y-m-d H:i' );
		}
		$name = substr( $name, 0, 100 );

		$snapshots = self::get_snapshots();

		// If at max, remove the oldest snapshot.
		if ( count( $snapshots ) >= 5 ) {
			$oldest_id   = null;
			$oldest_time = PHP_INT_MAX;
			foreach ( $snapshots as $id => $snapshot ) {
				if ( $snapshot['created_at'] < $oldest_time ) {
					$oldest_time = $snapshot['created_at'];
					$oldest_id   = $id;
				}
			}
			if ( $oldest_id ) {
				unset( $snapshots[ $oldest_id ] );
			}
		}

		$snapshot_id               = 'snap_' . time();
		$snapshots[ $snapshot_id ] = array(
			'name'       => $name,
			'created_at' => time(),
			'data'       => self::collect_all_settings(),
		);

		update_option( '_fl_builder_snapshots', $snapshots );

		wp_send_json_success( array(
			'snapshots' => self::get_snapshot_meta( $snapshots ),
		) );
	}

	/**
	 * Restores settings from a stored snapshot.
	 *
	 * @since 2.11
	 */
	public function restore_snapshot() {

		check_admin_referer( 'fl_builder_import_export' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$snapshot_id = isset( $_POST['snapshot_id'] ) ? sanitize_text_field( $_POST['snapshot_id'] ) : '';
		$snapshots   = self::get_snapshots();

		if ( ! isset( $snapshots[ $snapshot_id ] ) ) {
			wp_send_json_error( 'Snapshot not found.' );
		}

		self::apply_settings( $snapshots[ $snapshot_id ]['data'] );

		wp_send_json_success();
	}

	/**
	 * Deletes a stored snapshot.
	 *
	 * @since 2.11
	 */
	public function delete_snapshot() {

		check_admin_referer( 'fl_builder_import_export' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$snapshot_id = isset( $_POST['snapshot_id'] ) ? sanitize_text_field( $_POST['snapshot_id'] ) : '';
		$snapshots   = self::get_snapshots();

		if ( ! isset( $snapshots[ $snapshot_id ] ) ) {
			wp_send_json_error( 'Snapshot not found.' );
		}

		unset( $snapshots[ $snapshot_id ] );
		update_option( '_fl_builder_snapshots', $snapshots );

		wp_send_json_success( array(
			'snapshots' => self::get_snapshot_meta( $snapshots ),
		) );
	}

	public function allow_import( $data, $file, $filename, $mimes ) {
		if ( isset( $_POST['fl_global_import'] ) && current_user_can( 'manage_options' ) ) {
			$wp_filetype     = wp_check_filetype( $filename, $mimes );
			$ext             = $wp_filetype['ext'];
			$type            = $wp_filetype['type'];
			$proper_filename = $data['proper_filename'];
			return compact( 'ext', 'type', 'proper_filename' );
		}
		return $data;
	}
}
new FLBuilderGlobalImportExport();
