<?php

/**
 * Atomically saves cropped photos produced by FLPhotoModule.
 *
 * @since 2.11
 */
final class FLBuilderPhotoCrop {

	/**
	 * Save a cropped photo to a temp file in the cache directory, then move it
	 * into place. A concurrent reader of the cache directory (CDN/object-storage
	 * offload, rsync between nodes) can then never observe a partially written
	 * file under the final cache filename.
	 *
	 * @since 2.11
	 * @param WP_Image_Editor $editor
	 * @param string $final_path
	 * @return array|WP_Error The editor's save result pointing at $final_path, or the error that kept the file from landing there.
	 */
	public static function save( $editor, $final_path ) {
		$tmp_path = dirname( $final_path ) . '/tmp-' . uniqid() . '-' . basename( $final_path );
		$saved    = $editor->save( $tmp_path );

		if ( is_wp_error( $saved ) || empty( $saved['path'] ) || ! fl_builder_filesystem()->file_exists( $saved['path'] ) ) {
			// Editor rejected the temp destination. Preserve the original behavior.
			return $editor->save( $final_path );
		}

		$tmp_saved_path = $saved['path'];
		$saved['path']  = $final_path;
		$saved['file']  = basename( $final_path );

		if ( fl_builder_filesystem()->move( $tmp_saved_path, $final_path ) ) {
			self::restore_editor_file( $editor, $final_path );
			return $saved;
		}

		// Cross-device or permission edge: fall back to a copy, never leave the temp behind.
		$copied = fl_builder_filesystem()->copy( $tmp_saved_path, $final_path );
		fl_builder_filesystem()->unlink( $tmp_saved_path );

		if ( ! $copied ) {
			return new WP_Error( 'fl_photo_crop_move_failed', 'Could not move the cropped photo into the cache directory.' );
		}

		self::restore_editor_file( $editor, $final_path );
		return $saved;
	}

	/**
	 * WP_Image_Editor::save() sets its protected $file property to whatever path
	 * it was told to save to. Since crop() hands $editor to third party code via
	 * fl_builder_photo_cropped after the temp file has been renamed away, correct
	 * $file back to the final path so that hook sees the same state it always has.
	 *
	 * @since 2.11
	 * @param WP_Image_Editor $editor
	 * @param string $final_path
	 * @return void
	 */
	private static function restore_editor_file( $editor, $final_path ) {
		$property = new ReflectionProperty( $editor, 'file' );
		$property->setAccessible( true );
		$property->setValue( $editor, $final_path );
	}
}
