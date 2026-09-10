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
		register_rest_route( 'fl-controls/v1', '/export/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => __CLASS__ . '::export_template',
			'permission_callback' => __CLASS__ . '::check_permission',
		) );

		register_rest_route( 'fl-controls/v1', '/color_presets_view/', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => __CLASS__ . '::set_color_presets_view',
			'permission_callback' => __CLASS__ . '::check_permission',
		) );
	}

	/**
	 * Get the full state of the FL.Controls redux store
	 */
	static public function get_state( $request ) {
		$color_picker_shows_presets_tab = get_option( '_fl_builder_default_presets_tab' );
		$user_settings                  = FLBuilderUserSettings::get();

		return new WP_REST_Response( [
			'color'                         => [
				'presets' => FLBuilderModel::get_color_presets(),
				'sets'    => self::get_color_sets(),
			],
			'backgrounds'                   => [
				'presets' => self::get_background_presets(),
			],

			// User Preference
			'currentPresetView'             => $user_settings['current_preset_colors_view'],

			// Site-level Default
			'defaultPresetTabInColorPicker' => ( '1' === $color_picker_shows_presets_tab || 1 === $color_picker_shows_presets_tab ),
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
		$prefix = FLBuilderGlobalStyles::get_settings()->prefix;
		$prefix = ! empty( $prefix ) ? FLBuilderGlobalStyles::label_to_key( $prefix ) : 'fl-global';

		foreach ( $data as $color ) {
			// Skip empty placeholder rows (e.g. the blank row left after a reset) so
			// they don't render as a transparent "ghost" swatch in the color picker.
			if ( ! isset( $color['color'] ) || '' === trim( $color['color'] ) ) {
				continue;
			}

			$id    = isset( $color['uid'] ) ? $color['uid'] : $color['slug'];
			$label = isset( $color['label'] ) ? $color['label'] : $color['name'];
			$value = self::normalize_color_value( $color['color'] );

			// CSS var the color resolves to on the page. Global colors map to the
			// global-styles :root var; theme/WP palette colors to the wp preset var.
			// Lets non-connectable pickers (e.g. background layers) reference a
			// global color live without a Theme Builder field connection.
			//
			// The resolved color is baked in as the var fallback: the builder UI
			// (picker / settings preview) renders in a context without the
			// :root vars, so a bare var() resolves to nothing there and the
			// preview goes blank — the fallback shows the color while the live
			// var still wins on the rendered page.
			if ( isset( $color['uid'] ) ) {
				$css_var = 'var(--' . $prefix . '-' . FLBuilderGlobalStyles::label_to_key( $label ) . ', ' . $value . ')';
			} else {
				$css_var = 'var(--wp--preset--color--' . $color['slug'] . ', ' . $value . ')';
			}

			$colors[] = [
				'uid'           => $id,
				'label'         => $label,
				'color'         => $value,
				'isGlobalColor' => isset( $color['uid'] ),
				'cssVar'        => $css_var,
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

		if ( isset( $params['replacePresets'] ) ) {
			$presets = $params['replacePresets'];

			if ( update_option( '_fl_builder_color_presets', $presets ) ) {
				return new WP_REST_Response( [
					'presets' => $presets,
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

	static public function set_color_presets_view( $request ) {
		$view = $request->get_params()['view'];

		if ( false === FLBuilderUserSettings::save_current_preset_colors_view( $view ) ) {
			$settings = FLBuilderUserSettings::get();
			$view     = $settings['current_preset_colors_view'];
		}
		return new WP_REST_Response( $view, 200 );
	}

	public static function export_template( WP_REST_Request $request ) {
		$post_id = (int) $request['id'];
		$post    = get_post( $post_id );

		if ( ! $post || ! $post_id ) {
			return new WP_Error( 'not_found', 'Template not found', array( 'status' => 404 ) );
		}

		global $wp_version;

		$author_id   = $post->post_author;
		$author_name = get_the_author_meta( 'display_name', $author_id );

		$xml  = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
		$xml .= sprintf(
			'<!-- generator="WordPress/%s" created="%s" -->' . "\n",
			$wp_version,
			gmdate( 'Y-m-d H:i:s' )
		);

		$xml .= '<rss version="2.0"
			xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:wp="http://wordpress.org/export/1.2/">' . "\n";

		$xml .= '<channel>' . "\n";

		$xml .= '<title>' . esc_xml( get_bloginfo( 'name' ) ) . '</title>' . "\n";
		$xml .= '<link>' . esc_xml( home_url() ) . '</link>' . "\n";
		$xml .= '<description>' . esc_xml( get_bloginfo( 'description' ) ) . '</description>' . "\n";
		$xml .= '<pubDate>' . esc_xml( get_gmt_from_date( $post->post_date ) ) . '</pubDate>' . "\n";
		$xml .= '<language>' . esc_xml( get_locale() ) . '</language>' . "\n";
		$xml .= '<wp:wxr_version>1.2</wp:wxr_version>' . "\n";
		$xml .= '<wp:base_site_url>' . esc_xml( home_url() ) . '</wp:base_site_url>' . "\n";
		$xml .= '<wp:base_blog_url>' . esc_xml( home_url() ) . '</wp:base_blog_url>' . "\n";

		$xml .= '<wp:author>' . "\n";
		$xml .= '<wp:author_id>' . esc_xml( $author_id ) . '</wp:author_id>' . "\n";
		$xml .= '<wp:author_login><![CDATA[' . get_the_author_meta( 'user_login', $author_id ) . ']]></wp:author_login>' . "\n";
		$xml .= '<wp:author_email><![CDATA[' . get_the_author_meta( 'user_email', $author_id ) . ']]></wp:author_email>' . "\n";
		$xml .= '<wp:author_display_name><![CDATA[' . $author_name . ']]></wp:author_display_name>' . "\n";
		$xml .= '<wp:author_first_name><![CDATA[' . get_the_author_meta( 'first_name', $author_id ) . ']]></wp:author_first_name>' . "\n";
		$xml .= '<wp:author_last_name><![CDATA[' . get_the_author_meta( 'last_name', $author_id ) . ']]></wp:author_last_name>' . "\n";
		$xml .= '</wp:author>' . "\n";

		$xml .= '<generator>https://wordpress.org</generator>' . "\n";

		// Include taxonomy terms so WP_Import assigns them on import.
		$template_type       = FLBuilderModel::get_user_template_type( $post_id );
		$template_type_term  = get_term_by( 'slug', $template_type, 'fl-builder-template-type' );
		$template_categories = wp_get_post_terms( $post_id, 'fl-builder-template-category' );

		$xml .= '<item>' . "\n";
		$xml .= '<title>' . esc_xml( $post->post_title ) . '</title>' . "\n";
		$xml .= '<pubDate>' . esc_xml( $post->post_date ) . '</pubDate>' . "\n";
		$xml .= '<dc:creator><![CDATA[' . $author_name . ']]></dc:creator>' . "\n";
		$xml .= '<guid isPermaLink="false">' . esc_xml( $post->guid ) . '</guid>' . "\n";
		$xml .= '<description></description>' . "\n";
		$xml .= '<content:encoded><![CDATA[' . $post->post_content . ']]></content:encoded>' . "\n";
		$xml .= '<excerpt:encoded><![CDATA[' . $post->post_excerpt . ']]></excerpt:encoded>' . "\n";

		if ( $template_type_term && ! is_wp_error( $template_type_term ) ) {
			$xml .= '<category domain="fl-builder-template-type" nicename="' . esc_attr( $template_type_term->slug ) . '"><![CDATA[' . $template_type_term->name . ']]></category>' . "\n";
		}

		if ( ! is_wp_error( $template_categories ) ) {
			foreach ( $template_categories as $cat ) {
				$xml .= '<category domain="fl-builder-template-category" nicename="' . esc_attr( $cat->slug ) . '"><![CDATA[' . $cat->name . ']]></category>' . "\n";
			}
		}

		$xml .= '<wp:post_id>' . esc_xml( $post_id ) . '</wp:post_id>' . "\n";
		$xml .= '<wp:post_date>' . esc_xml( $post->post_date ) . '</wp:post_date>' . "\n";
		$xml .= '<wp:post_date_gmt>' . esc_xml( get_gmt_from_date( $post->post_date ) ) . '</wp:post_date_gmt>' . "\n";
		$xml .= '<wp:comment_status>' . esc_xml( $post->comment_status ) . '</wp:comment_status>' . "\n";
		$xml .= '<wp:ping_status>' . esc_xml( $post->ping_status ) . '</wp:ping_status>' . "\n";
		$xml .= '<wp:post_name>' . esc_xml( $post->post_name ) . '</wp:post_name>' . "\n";
		$xml .= '<wp:status>' . esc_xml( $post->post_status ) . '</wp:status>' . "\n";
		$xml .= '<wp:post_parent>' . esc_xml( $post->post_parent ) . '</wp:post_parent>' . "\n";
		$xml .= '<wp:menu_order>' . esc_xml( $post->menu_order ) . '</wp:menu_order>' . "\n";
		$xml .= '<wp:post_type>' . esc_xml( $post->post_type ) . '</wp:post_type>' . "\n";
		$xml .= '<wp:post_password></wp:post_password>' . "\n";
		$xml .= '<wp:is_sticky>0</wp:is_sticky>' . "\n";

		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			foreach ( $values as $value ) {
				$xml .= '<wp:postmeta>' . "\n";
				$xml .= '<wp:meta_key>' . esc_xml( $key ) . '</wp:meta_key>' . "\n";
				$xml .= '<wp:meta_value><![CDATA[' . $value . ']]></wp:meta_value>' . "\n";
				$xml .= '</wp:postmeta>' . "\n";
			}
		}

		$xml .= '</item>' . "\n";
		$xml .= '</channel>' . "\n";
		$xml .= '</rss>';

		return new WP_REST_Response( $xml, 200, array(
			'Content-Type'        => 'application/xml; charset=' . get_option( 'blog_charset' ),
			'Content-Disposition' => 'attachment; filename="bb-template-export-' . $post_id . '.xml"',
		));
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
