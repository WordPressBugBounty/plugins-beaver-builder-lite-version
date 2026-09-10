<?php

/**
 * The WordPress importer plugin has a few issues that break
 * serialized data in certain cases. This class overrides the
 * WordPress importer with our own patched version that fixes
 * these issues.
 *
 * @since 1.8
 */
final class FLBuilderImport {

	/**
	 * @since 1.8
	 * @return void
	 */
	static public function init() {
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) || ! class_exists( 'WP_Import' ) || ! class_exists( 'WXR_Parser_Regex' ) ) {
			return;
		}

		if ( defined( 'FL_BUILDER_IMPORTER_FIX' ) && ! FL_BUILDER_IMPORTER_FIX ) {
			return;
		}

		require_once FL_BUILDER_DIR . 'classes/class-fl-builder-importer.php';

		// Add our importer.
		add_action( 'admin_init', 'FLBuilderImport::load' );
	}

	/**
	 * @since 1.8
	 * @return void
	 */
	static public function load() {

		$bb_import = new FLBuilderImporter();

		register_importer( 'bb_import', 'Beaver Builder', __( 'Import <strong>Beaver Builder layouts and content</strong> as well as normal posts, pages, comments, custom fields, categories, and tags from a WordPress export file.', 'fl-builder' ), array( $bb_import, 'dispatch' ) );
	}

	/**
	 * Returns 0 for fl-builder-template posts so WP_Import always creates
	 * a new post instead of skipping it as a duplicate.
	 *
	 * @param int   $post_exists Post ID of the existing match, or 0.
	 * @param array $post        The post data being imported.
	 * @return int
	 */
	static public function allow_template_reimport( $post_exists, $post ) {
		if ( isset( $post['post_type'] ) && 'fl-builder-template' === $post['post_type'] ) {
			return 0;
		}
		return $post_exists;
	}
}

add_action( 'plugins_loaded', 'FLBuilderImport::init' );
add_filter( 'wp_import_existing_post', 'FLBuilderImport::allow_template_reimport', 10, 2 );
