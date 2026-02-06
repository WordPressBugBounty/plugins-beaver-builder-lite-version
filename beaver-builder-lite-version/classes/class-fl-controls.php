<?php
class FLControls {

	static public function init() {
		add_action( 'rest_api_init', __CLASS__ . '::register_rest_endpoints' );
	}

	static public function register_rest_endpoints() {

		register_rest_route( 'fl-controls/v1', '/state/', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => __CLASS__ . '::get_state',
			'permission_callback' => __CLASS__ . '::check_permission',
		) );

		register_rest_route( 'fl-controls/v1', '/color_presets/', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => __CLASS__ . '::set_color_presets',
			'permission_callback' => __CLASS__ . '::check_write_permission',
		) );

		register_rest_route( 'fl-controls/v1', '/color_presets/', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => __CLASS__ . '::delete_color_presets',
			'permission_callback' => __CLASS__ . '::check_write_permission',
		) );

		register_rest_route( 'fl-controls/v1', '/background_presets/', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => __CLASS__ . '::set_background_presets',
			'permission_callback' => __CLASS__ . '::check_write_permission',
		) );

		register_rest_route( 'fl-controls/v1', '/attachment_sizes/', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => __CLASS__ . '::get_attachment_sizes',
			'permission_callback' => __CLASS__ . '::check_permission',
		) );
	}

	/**
	 * Get the full state of the FL.Controls redux store
	 */
	static public function get_state( $request ) {
		return new WP_REST_Response( [
			'color'       => [
				'presets' => FLBuilderModel::get_color_presets(),
				'sets'    => self::get_color_sets(),
			],
			'backgrounds' => [
				'presets' => self::get_background_presets(),
			],
		], 200 );
	}

	static public function get_color_sets() {
		$bb_global_colors = FLBuilderGlobalStyles::get_settings()->colors;
		$theme            = FLBuilderGlobalStyles::get_theme_json_js_config()['color']['palette'];

		$sets = [
			'bb_global' => [
				'slug'   => 'bb_global',
				'name'   => __( 'Global Colors', 'fl-builder' ),
				'colors' => self::format_colors( $bb_global_colors ),
			],
		];

		foreach ( $theme as $slug => $colors ) {
			$sets[ $slug ] = [
				'slug'   => $slug,
				'name'   => $slug,
				'colors' => self::format_colors( $colors ),
			];
		}
		return $sets;
	}

	static public function format_colors( $data = [] ) {
		$colors = [];

		foreach ( $data as $color ) {
			if ( ! isset( $color['color'] ) ) {
				continue;
			}

			$id       = isset( $color['uid'] ) ? $color['uid'] : $color['slug'];
			$label    = isset( $color['label'] ) ? $color['label'] : $color['name'];
			$colors[] = [
				'uid'           => $id,
				'label'         => $label,
				'color'         => self::normalize_color_value( $color['color'] ),
				'isGlobalColor' => isset( $color['uid'] ),
			];
		}
		return $colors;
	}

	static public function normalize_color_value( $value ) {

		if ( FLBuilderUtils::ctype_xdigit( ltrim( trim( $value ), '#' ) ) ) {
			return '#' . ltrim( trim( $value ), '#' );
		}

		return $value;
	}

	/**
	 * Add Color presets to the saved array
	 */
	static public function set_color_presets( $request ) {
		$color_presets = (array) get_option( '_fl_builder_color_presets', [] );
		$params        = $request->get_params();

		if ( isset( $params['clearPresets'] ) && true === $params['clearPresets'] ) {
			if ( update_option( '_fl_builder_color_presets', [] ) ) {
				return new WP_REST_Response( [
					'presets' => [],
				], 200 );
			}
		}

		// Dedupe the merged arrays
		$new_presets = array_unique( array_merge( $color_presets, $params['addPresets'] ) );

		update_option( '_fl_builder_color_presets', $new_presets );

		return new WP_REST_Response( [
			'presets' => $new_presets,
		], 200 );
	}

	/**
	 * Delete one or more presets from the saved array
	 */
	static public function delete_color_presets( $request ) {
		$color_presets = (array) get_option( '_fl_builder_color_presets', [] );
		$params        = $request->get_params();

		$new_presets = array_values( array_filter( $color_presets, function ( $color ) use ( $params ) {

			// Check for exact match and value w/ # prepended
			return ! in_array( $color, $params['deletePresets'] ) && ! in_array( '#' . $color, $params['deletePresets'] );
		} ) );

		if ( update_option( '_fl_builder_color_presets', $new_presets ) ) {
			$color_presets = $new_presets;
		}

		return new WP_REST_Response( [
			'presets' => $color_presets,
		], 200 );
	}

	/**
	 * Get saved backgrounds
	 */
	static public function get_background_presets() {
		$presets = (array) get_option( '_fl_builder_color_presets', [] );
		return $presets;
	}

	/**
	 * Set saved backgrounds
	 */
	static public function set_background_presets( $request ) {
		$presets     = (array) get_option( '_fl_builder_color_presets', [] );
		$params      = $request->get_params();
		$new_presets = array_merge( $presets, $params['addPresets'] );

		if ( update_option( '_fl_builder_background_presets', $new_presets ) ) {
			$presets = $new_presets;
		}

		return new WP_REST_Response( [
			'presets' => $presets,
		], 200 );
	}

	static public function get_attachment_sizes( $request ) {
		$id       = $request->get_params()['id'];
		$meta     = wp_get_attachment_metadata( $id );
		$url      = wp_get_attachment_url( $id );
		$filename = wp_basename( $url );
		$sizes    = [];

		if ( ! current_user_can( 'read_private_posts' ) ) {
			$post    = get_post( $id );
			$user_id = get_current_user_id();
			if ( $post->post_author !== $user_id ) {
				return new WP_REST_Response( null, 403, [] );
			}
		}

		if ( $meta ) {
			$sizes    = $meta['sizes'];
			$basename = dirname( wp_get_attachment_url( $id ) );

			foreach ( $sizes as $key => $image ) {
				$sizes[ $key ]['url'] = $basename . '/' . $image['file'];
			}
		}

		if ( ! isset( $sizes['full'] ) ) {
			$sizes['full'] = array(
				'url'      => $url,
				'filename' => isset( $meta['file'] ) ? $meta['file'] : $filename,
				'width'    => isset( $meta['width'] ) ? $meta['width'] : '',
				'height'   => isset( $meta['height'] ) ? $meta['height'] : '',
			);
		}

		return new WP_REST_Response( [
			'id'    => $id,
			'sizes' => $sizes,
		], 200 );
	}

	/**
	 * Checks permission for read access.
	 *
	 * @return boolean
	 */
	static public function check_permission() {
		return FLBuilderUserAccess::current_user_can( 'builder_access' );
	}

	/**
	 * Checks permission for write access.
	 *
	 * @return boolean
	 */
	static public function check_write_permission() {
		return ( FLBuilderUserAccess::current_user_can( 'unrestricted_editing' ) && FLBuilderUserAccess::current_user_can( 'builder_access' ) );
	}
}

FLControls::init();
