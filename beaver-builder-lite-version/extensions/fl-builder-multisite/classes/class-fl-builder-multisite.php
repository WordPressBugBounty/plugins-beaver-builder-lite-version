<?php

/**
 * Multisite helper for the page builder.
 *
 * @since 1.0
 */
final class FLBuilderMultisite {

	/**
	 * Initializes builder multisite support.
	 *
	 * @since 1.0
	 * @return void
	 */
	static public function init() {
		// Priority 20 so this runs after core's own wp_initialize_site() callback at 10,
		// which is what creates the new site's tables.
		add_action( 'wp_initialize_site', __CLASS__ . '::install_for_initialized_site', 20, 1 );
		add_filter( 'wpmu_drop_tables', __CLASS__ . '::uninstall_on_delete_blog' );
		add_filter( 'fl_builder_activate', __CLASS__ . '::activate' );
		add_filter( 'fl_builder_uninstall', __CLASS__ . '::uninstall' );
	}

	/**
	 * Short circuit activation in favor of multisite activation.
	 *
	 * @since 1.8
	 * @return void
	 */
	static public function activate( $activate ) {
		if ( is_network_admin() ) {
			FLBuilderMultisite::install();
		} else {
			FLBuilderAdmin::install();
		}

		FLBuilderAdmin::trigger_activate_notice();

		return false;
	}

	/**
	 * Runs the install method for each site on the network.
	 *
	 * @since 1.0
	 * @return void
	 */
	static public function install() {
		global $blog_id;
		global $wpdb;

		$original_blog_id = $blog_id;
		$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		foreach ( $blog_ids as $id ) {
			switch_to_blog( $id );
			FLBuilderAdmin::install();
		}

		switch_to_blog( $original_blog_id );
	}

	/**
	 * Runs the install for a site that core has just initialized.
	 *
	 * Replaces a listener on wpmu_new_blog, deprecated in WP 5.1.
	 *
	 * @since 2.11
	 * @param WP_Site $new_site The site being initialized.
	 * @return void
	 */
	static public function install_for_initialized_site( $new_site ) {
		self::install_for_new_blog( $new_site->id );
	}

	/**
	 * Runs the install for a newly created site.
	 *
	 * Only $blog_id is used. The remaining parameters are the signature of the
	 * deprecated wpmu_new_blog hook this used to be attached to, kept — now
	 * optional — so existing callers keep working.
	 *
	 * @since 1.0
	 * @param int $blog_id
	 * @param int $user_id
	 * @param string $domain
	 * @param string $path
	 * @param int $site_id
	 * @param array $meta
	 * @return void
	 */
	static public function install_for_new_blog( $blog_id, $user_id = 0, $domain = '', $path = '', $site_id = 0, $meta = array() ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active_for_network( FLBuilderModel::plugin_basename() ) ) {
			switch_to_blog( $blog_id );
			FLBuilderAdmin::install();
			restore_current_blog();
		}
	}

	/**
	 * Short circuit the default uninstall and run
	 * the uninstall for each site on the network.
	 *
	 * @since 1.0
	 * @return void
	 */
	static public function uninstall( $uninstall ) {
		global $blog_id;
		global $wpdb;

		$original_blog_id = $blog_id;
		$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		foreach ( $blog_ids as $id ) {
			switch_to_blog( $id );
			FLBuilderAdmin::uninstall();
		}

		switch_to_blog( $original_blog_id );

		return false;
	}

	/**
	 * Runs the uninstall method when a site is deleted.
	 *
	 * @since 1.0
	 * @return array
	 */
	static public function uninstall_on_delete_blog( $tables ) {
		return $tables;
	}

	/**
	 * Checks if a blog on the network exists.
	 *
	 * @since 1.5.7
	 * @param $blog_id The blog ID to check.
	 * @return bool
	 */
	static public function blog_exists( $blog_id ) {
		global $wpdb;

		$like = esc_sql( $wpdb->esc_like( $blog_id ) );

		return $wpdb->get_row( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE blog_id = '%s'", $like ) ); // @codingStandardsIgnoreLine
	}
}

FLBuilderMultisite::init();
