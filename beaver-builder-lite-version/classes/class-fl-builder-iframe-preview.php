<?php

/**
 * Handles rendering layouts in an iframe preview.
 *
 * @since 2.0.6
 */
final class FLBuilderIframePreview {

	/**
	 * Initialize on plugins loaded.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	static public function init() {
		add_action( 'plugins_loaded', __CLASS__ . '::hook' );
	}

	/**
	 * Setup hooks.
	 *
	 * @since 2.1
	 * @return void
	 */
	static public function hook() {
		// The request half of is_builder_draft_preview(); its capability check needs the main query, so it runs in filter_node_status().
		if ( ! isset( $_GET['fl_builder_preview'] ) || ! is_user_logged_in() ) {
			return;
		}

		add_filter( 'show_admin_bar', '__return_false' );
		add_filter( 'fl_builder_node_status', __CLASS__ . '::filter_node_status' );
	}

	/**
	 * Forces draft node status for layout previews the current user can edit.
	 *
	 * @since 2.0.6
	 * @param string $status
	 * @return string
	 */
	static public function filter_node_status( $status ) {
		return FLBuilderModel::is_builder_draft_preview() ? 'draft' : $status;
	}
}

FLBuilderIframePreview::init();
