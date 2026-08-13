<?php

/**
 * Helper class for working with photos.
 *
 * @since 1.0
 */
final class FLBuilderPhoto {

	/**
	 * Returns an array of data for sizes that are
	 * defined for WordPress images.
	 *
	 * @since 1.0
	 * @return array
	 */
	static public function sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $size ) {

			$sizes[ $size ] = array( 0, 0 );

			if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
				$sizes[ $size ][0] = get_option( $size . '_size_w' );
				$sizes[ $size ][1] = get_option( $size . '_size_h' );

				if ( 0 === absint( $sizes[ $size ][1] ) ) {
					$sizes[ $size ][1] = $sizes[ $size ][0];
				}
			} elseif ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$sizes[ $size ] = array(
					$_wp_additional_image_sizes[ $size ]['width'],
					$_wp_additional_image_sizes[ $size ]['height'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Returns an object with data for an attachment using
	 * wp_prepare_attachment_for_js based on the provided id.
	 *
	 * @since 1.0
	 * @param string $id The attachment id.
	 * @return object
	 */
	static public function get_attachment_data( $id ) {
		$attachment = get_post( $id );

		// wp_prepare_attachment_for_js() checks current_user_can('read_post', $post_parent)
		// which triggers a PHP notice if the parent's post type is not registered (e.g. after
		// a plugin that registered the post type has been deactivated). Clear the parent
		// temporarily to avoid this.
		if ( $attachment && $attachment->post_parent ) {
			$parent = get_post( $attachment->post_parent );
			if ( $parent && ! get_post_type_object( $parent->post_type ) ) {
				$attachment              = clone $attachment;
				$attachment->post_parent = 0;
			}
		}

		$data = wp_prepare_attachment_for_js( $attachment ? $attachment : $id );

		if ( gettype( $data ) == 'array' ) {
			return json_decode( json_encode( $data ) );
		}

		return $data;
	}

	/**
	 * Renders the thumb URL for a photo object.
	 *
	 * @since 1.0
	 * @param object $photo An object with photo data.
	 * @return void
	 */
	static public function get_thumb( $photo ) {
		if ( empty( $photo ) ) {
			echo FLBuilder::plugin_url() . 'img/spacer.png';
		} elseif ( ! isset( $photo->sizes ) ) {
			echo $photo->url;
		} elseif ( ! empty( $photo->sizes->thumbnail ) ) {
			echo $photo->sizes->thumbnail->url;
		} else {
			echo $photo->sizes->full->url;
		}
	}

	/**
	 * Renders the options for a photo select field.
	 *
	 * @since 1.0
	 * @param string $selected The selected URL.
	 * @param object $photo An object with photo data.
	 * @return void
	 */
	static public function get_src_options( $selected, $photo ) {
		if ( ! isset( $photo->sizes ) ) {
			echo '<option value="' . $photo->url . '" selected="selected">' . _x( 'Full Size', 'Image size.', 'fl-builder' ) . '</option>';
		} else {

			$titles = array(
				'full'      => _x( 'Full Size', 'Image size.', 'fl-builder' ),
				'large'     => _x( 'Large', 'Image size.', 'fl-builder' ),
				'medium'    => _x( 'Medium', 'Image size.', 'fl-builder' ),
				'thumbnail' => _x( 'Thumbnail', 'Image size.', 'fl-builder' ),
			);

			foreach ( $photo->sizes as $key => $val ) {

				if ( ! isset( $titles[ $key ] ) ) {
					$titles[ $key ] = ucwords( str_replace( array( '_', '-' ), ' ', $key ) );
				}

				echo '<option value="' . $val->url . '" ' . selected( basename( $selected ), basename( $val->url ) ) . '>' . $titles[ $key ] . ' - ' . $val->width . ' x ' . $val->height . '</option>';
			}
		}
	}
}
