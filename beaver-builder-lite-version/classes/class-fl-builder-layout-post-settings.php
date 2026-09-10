<?php
/**
 * Post Settings tab for the Layout Settings dialog.
 *
 * @package FLBuilder
 * @since 2.10
 */

/**
 * Handles Post Settings tab behavior in the Layout Settings form:
 * visibility gating, settings hydration, and saving post fields
 * directly to wp_posts rather than BB meta.
 *
 * @since 2.10
 */
final class FLBuilderLayoutPostSettings {

	/**
	 * Post Settings field names — used to strip them from BB meta on save.
	 *
	 * @since 2.10
	 * @var array
	 */
	private static $post_fields = array(
		'title',
		'excerpt',
		'slug',
		'status',
		'featured_image',
		'featured_image_src',
		'parent',
		'page_template',
		'menu_order',
		'comment_status',
		'ping_status',
	);

	/**
	 * Returns the builder edit URL for the current post after a slug change.
	 * Called via FLBuilder.ajax to let the client navigate to the new permalink.
	 *
	 * @since 2.10
	 * @return array|null
	 */
	public static function ajax_get_permalink() {
		$post_id = FLBuilderModel::get_post_id();
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			return null;
		}
		return array( 'url' => FLBuilderModel::get_edit_url( $post_id ) );
	}

	/**
	 * Removes the Post Settings tab for unsupported post types and strips
	 * individual sections/fields based on what the post type declares it supports.
	 * Fires on wp action priority 1, before render_settings_config.
	 *
	 * @since 2.10
	 * @param array  $form The form config array.
	 * @param string $id   The form ID.
	 * @return array
	 */
	public static function gate_tab( $form, $id ) {
		if ( 'layout' !== $id || ! isset( $form['tabs']['post'] ) ) {
			return $form;
		}

		$post_id = FLBuilderModel::get_post_id();

		if ( ! $post_id ) {
			unset( $form['tabs']['post'] );
			return $form;
		}

		$post_type = get_post_type( $post_id );

		$post_type_obj                 = get_post_type_object( $post_type );
		$label                         = $post_type_obj ? $post_type_obj->labels->singular_name : __( 'Post', 'fl-builder' );
		$form['tabs']['post']['title'] = sprintf(
			/* translators: %s: post type singular name, e.g. "Page" or "Product" */
			__( '%s Settings', 'fl-builder' ),
			$label
		);

		if ( ! post_type_supports( $post_type, 'excerpt' ) ) {
			unset( $form['tabs']['post']['sections']['general']['fields']['excerpt'] );
		}

		if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
			unset( $form['tabs']['post']['sections']['featured'] );
		}

		if ( ! post_type_supports( $post_type, 'page-attributes' ) ) {
			unset( $form['tabs']['post']['sections']['attributes'] );
		}

		if ( ! post_type_supports( $post_type, 'comments' ) ) {
			unset( $form['tabs']['post']['sections']['discussion'] );
		}

		// Themer layouts: title, slug, featured image only — no status.
		if ( 'fl-theme-layout' === $post_type ) {
			unset( $form['tabs']['post']['sections']['general']['fields']['status'] );
		}

		// User templates: title, slug, status, featured image only — no excerpt or attributes.
		if ( 'fl-builder-template' === $post_type ) {
			unset( $form['tabs']['post']['sections']['general']['fields']['excerpt'] );
			unset( $form['tabs']['post']['sections']['attributes'] );
		}

		return $form;
	}

	/**
	 * Injects live post values into FLBuilderSettingsConfig.settings.layout so
	 * the Layout Settings form opens with current post data pre-populated.
	 * Post fields are stripped before saving to BB meta, so they are never
	 * present in the stored layout settings — this filter re-adds them at load time.
	 *
	 * @since 2.10
	 * @param array $settings The settings forms config array.
	 * @return array
	 */
	public static function inject_settings( $settings ) {
		$post_id = FLBuilderModel::get_post_id();
		if ( ! $post_id ) {
			return $settings;
		}

		$data = self::build_post_data( $post_id );
		if ( empty( $data ) ) {
			return $settings;
		}

		if ( ! isset( $settings['layout'] ) ) {
			$settings['layout'] = new stdClass();
		}

		$layout = (object) (array) $settings['layout'];
		foreach ( $data as $key => $val ) {
			$layout->$key = $val;
		}
		$settings['layout'] = $layout;

		return $settings;
	}

	/**
	 * Hydrates Post Settings form defaults so slug/template comparisons work
	 * in the client-side reload detection after save.
	 *
	 * @since 2.10
	 * @param object $defaults  The form defaults.
	 * @param string $form_type The settings form ID being processed.
	 * @return object
	 */
	public static function set_defaults( $defaults, $form_type ) {
		if ( 'layout' !== $form_type ) {
			return $defaults;
		}

		$post_id = FLBuilderModel::get_post_id();
		if ( ! $post_id ) {
			return $defaults;
		}

		$data = self::build_post_data( $post_id );
		if ( empty( $data ) ) {
			return $defaults;
		}

		$defaults = is_object( $defaults ) ? $defaults : (object) (array) $defaults;
		foreach ( $data as $key => $val ) {
			$defaults->$key = $val;
		}

		return $defaults;
	}

	/**
	 * Builds the post field data array used by inject_settings and set_defaults.
	 *
	 * @since 2.10
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private static function build_post_data( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		$tpl      = get_post_meta( $post_id, '_wp_page_template', true );
		$thumb_id = (int) get_post_thumbnail_id( $post_id );

		return array(
			'title'              => $post->post_title,
			'excerpt'            => $post->post_excerpt,
			'slug'               => $post->post_name,
			'status'             => $post->post_status,
			'comment_status'     => $post->comment_status,
			'ping_status'        => $post->ping_status,
			'parent'             => $post->post_parent ? (string) $post->post_parent : '',
			'menu_order'         => (string) (int) $post->menu_order,
			'page_template'      => $tpl ? $tpl : 'default',
			'featured_image'     => $thumb_id ? (string) $thumb_id : '',
			// featured_image_src lets the photo field template resolve the filename
			// without needing the attachment in FLBuilderSettingsConfig.attachments.
			'featured_image_src' => $thumb_id ? (string) wp_get_attachment_url( $thumb_id ) : '',
		);
	}

	/**
	 * Strips post fields from the settings array before BB meta storage
	 * and saves them directly to wp_posts.
	 *
	 * @since 2.10
	 * @param array  $settings The settings array from the form.
	 * @param string $status   'published' or 'draft'.
	 * @param int    $post_id  The post being saved.
	 * @return array
	 */
	public static function save_post_settings( $settings, $status, $post_id ) {
		$settings = (array) $settings;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			return self::strip_post_fields( $settings );
		}

		$post   = get_post( $post_id );
		$update = self::build_post_update( $settings, $post, $post_id );

		if ( count( $update ) > 1 ) {
			wp_update_post( $update );
		}

		self::save_page_template( $settings, $post_id );
		self::save_featured_image( $settings, $post_id );

		return self::strip_post_fields( $settings );
	}

	/**
	 * Builds the wp_update_post() array from submitted settings.
	 *
	 * @since 2.10
	 * @param array   $settings Submitted form values.
	 * @param WP_Post $post     Current post object.
	 * @param int     $post_id  Post ID.
	 * @return array
	 */
	private static function build_post_update( $settings, $post, $post_id ) {
		$update = array( 'ID' => $post_id );

		if ( isset( $settings['title'] ) ) {
			$val = sanitize_text_field( $settings['title'] );
			if ( $val !== $post->post_title ) {
				$update['post_title'] = $val;
			}
		}
		if ( isset( $settings['excerpt'] ) ) {
			$val = wp_kses_post( $settings['excerpt'] );
			if ( $val !== $post->post_excerpt ) {
				$update['post_excerpt'] = $val;
			}
		}
		if ( isset( $settings['slug'] ) && '' !== $settings['slug'] ) {
			$val = sanitize_title( $settings['slug'] );
			if ( $val && $val !== $post->post_name ) {
				$update['post_name'] = $val;
			}
		}
		if ( isset( $settings['status'] ) ) {
			$val   = sanitize_key( $settings['status'] );
			$valid = array_merge( array_keys( get_post_statuses() ), array( 'private' ) );
			if ( 'publish' === $val && ! current_user_can( 'publish_post', $post_id ) ) {
				$val = 'pending';
			}
			if ( in_array( $val, $valid, true ) && $val !== $post->post_status ) {
				$update['post_status'] = $val;
			}
		}
		if ( isset( $settings['comment_status'] ) ) {
			$val = sanitize_key( $settings['comment_status'] );
			if ( in_array( $val, array( 'open', 'closed' ), true ) && $val !== $post->comment_status ) {
				$update['comment_status'] = $val;
			}
		}
		if ( isset( $settings['ping_status'] ) ) {
			$val = sanitize_key( $settings['ping_status'] );
			if ( in_array( $val, array( 'open', 'closed' ), true ) && $val !== $post->ping_status ) {
				$update['ping_status'] = $val;
			}
		}
		if ( isset( $settings['parent'] ) ) {
			$val = absint( $settings['parent'] );
			if ( $val !== (int) $post->post_parent ) {
				$update['post_parent'] = $val;
			}
		}
		if ( isset( $settings['menu_order'] ) ) {
			$val = (int) $settings['menu_order'];
			if ( $val !== (int) $post->menu_order ) {
				$update['menu_order'] = $val;
			}
		}

		return $update;
	}

	/**
	 * Saves the page template meta if it changed.
	 *
	 * @since 2.10
	 * @param array $settings Submitted form values.
	 * @param int   $post_id  Post ID.
	 * @return void
	 */
	private static function save_page_template( $settings, $post_id ) {
		if ( ! isset( $settings['page_template'] ) ) {
			return;
		}
		$tpl     = sanitize_text_field( $settings['page_template'] );
		$old     = get_post_meta( $post_id, '_wp_page_template', true );
		$old_tpl = $old ? $old : 'default';
		if ( $tpl === $old_tpl ) {
			return;
		}
		if ( '' === $tpl || 'default' === $tpl ) {
			delete_post_meta( $post_id, '_wp_page_template' );
		} else {
			update_post_meta( $post_id, '_wp_page_template', $tpl );
		}
	}

	/**
	 * Sets or removes the post thumbnail.
	 *
	 * @since 2.10
	 * @param array $settings Submitted form values.
	 * @param int   $post_id  Post ID.
	 * @return void
	 */
	private static function save_featured_image( $settings, $post_id ) {
		if ( ! isset( $settings['featured_image'] ) ) {
			return;
		}
		$thumb_id = self::resolve_attachment_id( $settings['featured_image'] );
		if ( $thumb_id ) {
			set_post_thumbnail( $post_id, $thumb_id );
		} else {
			delete_post_thumbnail( $post_id );
		}
	}

	/**
	 * Removes post fields from the settings array so they don't enter BB meta.
	 *
	 * @since 2.10
	 * @param array $settings
	 * @return array
	 */
	private static function strip_post_fields( $settings ) {
		foreach ( self::$post_fields as $key ) {
			unset( $settings[ $key ] );
		}
		return $settings;
	}

	/**
	 * Returns page template options for the active theme.
	 *
	 * @since 2.10
	 * @return array
	 */
	public static function get_page_templates() {
		$options = array( 'default' => __( 'Default Template', 'fl-builder' ) );
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return $options;
		}
		$theme = wp_get_theme();
		foreach ( (array) $theme->get_page_templates( null, 'page' ) as $file => $name ) {
			$options[ $file ] = $name;
		}
		return $options;
	}

	/**
	 * Resolves a BB photo-field value to an attachment ID.
	 *
	 * @since 2.10
	 * @param mixed $value Raw photo-field value (ID, URL string, or array with id/url keys).
	 * @return int
	 */
	private static function resolve_attachment_id( $value ) {
		if ( empty( $value ) ) {
			return 0;
		}
		if ( is_object( $value ) ) {
			$value = (array) $value;
		}
		if ( is_array( $value ) ) {
			if ( ! empty( $value['id'] ) ) {
				return (int) $value['id'];
			}
			if ( ! empty( $value['url'] ) ) {
				return (int) attachment_url_to_postid( $value['url'] );
			}
			return 0;
		}
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}
		$id = (int) attachment_url_to_postid( $value );
		return $id > 0 ? $id : 0;
	}
}
