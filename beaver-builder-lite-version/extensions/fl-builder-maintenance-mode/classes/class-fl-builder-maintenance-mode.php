<?php

/**
 * Maintenance mode for Beaver Builder.
 *
 * @since 2.11
 */
final class FLBuilderMaintenanceMode {

	/**
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_fl_maintenance_save', __CLASS__ . '::ajax_save' );

		if ( is_admin() ) {
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'fl-builder-settings', 'fl-builder-multisite-settings' ) ) ) {
				add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_settings_scripts' );
				add_filter( 'fl_builder_admin_settings_nav_items', __CLASS__ . '::admin_settings_nav_items' );
				add_action( 'fl_builder_admin_settings_render_forms', __CLASS__ . '::admin_settings_render_forms' );
			}
		}

		add_action( 'template_redirect', __CLASS__ . '::maybe_maintenance_mode', 1 );
		add_action( 'admin_bar_menu', __CLASS__ . '::admin_bar_node', 999 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_admin_bar_styles' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_admin_bar_styles' );
	}

	/**
	 * Enqueue scripts and styles for the settings page.
	 *
	 * @since 2.11
	 * @return void
	 */
	public static function enqueue_settings_scripts() {
		wp_enqueue_style(
			'fl-builder-maintenance-mode',
			FL_BUILDER_MAINTENANCE_MODE_URL . 'css/fl-builder-maintenance-mode.css',
			array(),
			FL_BUILDER_VERSION
		);
		wp_enqueue_script(
			'fl-builder-maintenance-mode-settings',
			FL_BUILDER_MAINTENANCE_MODE_URL . 'js/fl-builder-maintenance-mode-settings.js',
			array( 'jquery' ),
			FL_BUILDER_VERSION
		);
	}

	/**
	 * Enqueue admin bar styles when maintenance mode is active or scheduled.
	 *
	 * @since 2.11
	 * @return void
	 */
	public static function enqueue_admin_bar_styles() {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		if ( ! self::is_enabled() && ! self::is_scheduled() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		wp_enqueue_style(
			'fl-builder-maintenance-mode',
			FL_BUILDER_MAINTENANCE_MODE_URL . 'css/fl-builder-maintenance-mode.css',
			array(),
			FL_BUILDER_VERSION
		);
	}

	/**
	 * Adds the maintenance mode nav item to admin settings.
	 *
	 * @since 2.11
	 * @param array $nav_items
	 * @return array
	 */
	public static function admin_settings_nav_items( $nav_items ) {
		$nav_items['maintenance-mode'] = array(
			'title'    => __( 'Maintenance Mode', 'fl-builder' ),
			'show'     => is_network_admin() || ! FLBuilderAdminSettings::multisite_support(),
			'priority' => 700,
		);

		return $nav_items;
	}

	/**
	 * Renders the maintenance mode settings form.
	 *
	 * @since 2.11
	 * @return void
	 */
	public static function admin_settings_render_forms() {
		include FL_BUILDER_MAINTENANCE_MODE_DIR . 'includes/admin-settings-maintenance-mode.php';
	}

	/**
	 * Returns lightweight layout options (ID + title only) for the maintenance layout
	 * picker. Queries wp_posts directly instead of get_posts()/WP_Query, which by
	 * default primes the postmeta cache for every matched post — on sites with a large
	 * number of Beaver Builder pages/posts/templates that pulled every post's full
	 * _fl_builder_data into one query and could exhaust memory (issue #5506).
	 *
	 * @since 2.11.1
	 * @param string $post_type
	 * @return object[] Array of objects with ID and post_title properties.
	 */
	public static function get_layout_options( $post_type ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY post_title ASC",
				$post_type
			)
		);
	}

	/**
	 * AJAX handler for saving maintenance mode settings.
	 *
	 * @since 2.11
	 * @return void
	 */
	public static function ajax_save() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'fl-maintenance-save' ) ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( FLBuilderAdmin::admin_settings_capability() ) ) {
			wp_send_json_error();
		}

		update_option( '_fl_builder_maintenance_enabled', ! empty( $_POST['enabled'] ) ? '1' : '' );
		update_option( '_fl_builder_maintenance_layout_id', absint( $_POST['layout_id'] ?? 0 ) );
		update_option( '_fl_builder_maintenance_503_enabled', ! empty( $_POST['status_503'] ) ? '1' : '' );
		update_option( '_fl_builder_maintenance_schedule_enabled', ! empty( $_POST['schedule_enabled'] ) ? '1' : '' );

		$bypass_roles = array();
		if ( isset( $_POST['bypass_roles'] ) && is_array( $_POST['bypass_roles'] ) ) {
			$bypass_roles = array_map( 'sanitize_key', $_POST['bypass_roles'] );
		}
		update_option( '_fl_builder_maintenance_bypass_roles', $bypass_roles );

		$start = sanitize_text_field( wp_unslash( $_POST['start'] ?? '' ) );
		$end   = sanitize_text_field( wp_unslash( $_POST['end'] ?? '' ) );
		update_option( '_fl_builder_maintenance_start', $start );
		update_option( '_fl_builder_maintenance_end', $end );

		wp_send_json_success();
	}

	/**
	 * Whether the current front-end request should be replaced with the maintenance layout.
	 *
	 * Returns false for admin requests, the Beaver Builder editor/preview, bypass-role
	 * users, and when maintenance mode is off. The builder check matters because the
	 * editor loads on the front end via ?fl_builder (so is_admin() is false); without it
	 * an editing user who isn't in a bypass role would land on the maintenance layout
	 * instead of the builder UI. is_builder_active() already gates on edit capability.
	 *
	 * @since 2.11.1
	 * @return bool
	 */
	public static function should_intercept() {
		if ( is_admin() ) {
			return false;
		}
		// Cheap option read first so non-maintenance requests (the common case) bail early.
		if ( ! self::is_enabled() ) {
			return false;
		}
		if ( FLBuilderModel::is_builder_active() ) {
			return false;
		}
		if ( self::current_user_can_bypass() ) {
			return false;
		}
		return true;
	}

	/**
	 * Intercepts front-end requests when maintenance mode is active.
	 *
	 * Sends a 503 Service Unavailable response with a Retry-After header and
	 * renders the selected maintenance layout inline using the active theme.
	 *
	 * @since 2.11
	 * @return void
	 */
	public static function maybe_maintenance_mode() {
		if ( ! self::should_intercept() ) {
			return;
		}

		// Suppress the 503 only for staff previewing the layout (users who can edit
		// content). Everyone else — logged-out visitors and logged-in non-staff accounts
		// such as subscribers or customers — gets a real 503, so CDN/proxy layers don't
		// cache the maintenance page for them and uptime monitors still detect the outage.
		$send_503 = ! current_user_can( 'edit_posts' ) && get_option( '_fl_builder_maintenance_503_enabled', '1' );

		if ( $send_503 ) {
			status_header( 503 );
			nocache_headers();

			$end = self::get_end_time();
			if ( $end ) {
				$retry_after = max( 0, $end->getTimestamp() - time() );
				header( 'Retry-After: ' . $retry_after );
			} else {
				header( 'Retry-After: 3600' );
			}
		}

		$layout_post = self::get_maintenance_post();

		if ( ! $layout_post ) {
			wp_die(
				__( 'This site is currently undergoing maintenance. Please check back soon.', 'fl-builder' ),
				__( 'Maintenance', 'fl-builder' ),
				array( 'response' => $send_503 ? 503 : 200 )
			);
		}

		// Beaver Builder's admin-bar edit link should target the page the user actually
		// requested (so an editing user can jump straight into it), exactly as it would
		// without maintenance mode: an editable singular page links to itself, an archive
		// or the blog index gets no link. The query swap below flips is_singular() to true,
		// which would make is_post_editable() misreport on non-singular requests and link
		// to the wrong post. Capture the real editability of the requested page now, while
		// the original query is still in place, and pin it for the rest of the request.
		// $wp_the_query is deliberately left on the original request so the link's href
		// resolves to the requested page rather than the maintenance layout.
		$requested_editable = FLBuilderModel::is_post_editable();
		add_filter( 'fl_builder_is_post_editable', function () use ( $requested_editable ) {
			return $requested_editable;
		} );

		global $wp_query, $post;

		$maintenance_query = new WP_Query(
			array(
				'p'         => $layout_post->ID,
				'post_type' => $layout_post->post_type,
			)
		);

		// get_maintenance_post() validates the post via get_post() (object cache), but the
		// WP_Query above runs the full query filters — a plugin hooking pre_get_posts or
		// the_posts could suppress the post, leaving an empty query. Without this guard the
		// theme would render with no post data (a blank/not-found page) after the 503 header
		// was already sent. Fall back to the maintenance notice instead.
		if ( ! $maintenance_query->have_posts() ) {
			wp_die(
				__( 'This site is currently undergoing maintenance. Please check back soon.', 'fl-builder' ),
				__( 'Maintenance', 'fl-builder' ),
				array( 'response' => $send_503 ? 503 : 200 )
			);
		}

		// Override the main query so the theme renders the maintenance layout.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_query = $maintenance_query;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post = $wp_query->post;
		setup_postdata( $post );

		// Block/FSE themes use HTML block templates, not PHP files, so locate_template()
		// always returns empty for them. Return without exiting so WordPress's own block
		// template loader runs with the overridden $wp_query pointing to the maintenance layout.
		if ( wp_is_block_theme() ) {
			return;
		}

		$template = locate_template( array( 'page.php', 'index.php' ) );
		if ( $template ) {
			include $template;
		}

		wp_reset_postdata();
		exit;
	}

	/**
	 * Adds the maintenance mode node to the admin bar.
	 *
	 * Shows an amber "Active" node or blue "Scheduled" node to site administrators
	 * (manage_options), independent of bypass roles. Includes child nodes to edit the
	 * layout and visit settings.
	 *
	 * @since 2.11
	 * @param WP_Admin_Bar $wp_admin_bar
	 * @return void
	 */
	public static function admin_bar_node( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! self::is_enabled() && ! self::is_scheduled() ) {
			return;
		}

		if ( self::is_scheduled() ) {
			$label     = __( 'Maintenance Mode: Scheduled', 'fl-builder' );
			$css_class = 'fl-maintenance-scheduled';
		} else {
			$label     = __( 'Maintenance Mode: Active', 'fl-builder' );
			$css_class = 'fl-maintenance-active';
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'fl-builder-maintenance-mode',
				'title' => '<span class="ab-icon"></span>' . esc_html( $label ),
				'href'  => false,
				'meta'  => array( 'class' => $css_class ),
			)
		);

		$layout_id = self::get_layout_id();
		if ( $layout_id ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'fl-builder-maintenance-mode',
					'id'     => 'fl-builder-maintenance-mode-edit',
					'title'  => sprintf(
						/* translators: %s: layout post title */
						__( 'Layout: %s', 'fl-builder' ),
						esc_html( get_the_title( $layout_id ) )
					),
					'href'   => get_edit_post_link( $layout_id ),
				)
			);
		}

		$wp_admin_bar->add_node(
			array(
				'parent' => 'fl-builder-maintenance-mode',
				'id'     => 'fl-builder-maintenance-mode-settings',
				'title'  => __( 'Maintenance Settings', 'fl-builder' ),
				'href'   => admin_url( 'options-general.php?page=fl-builder-settings#maintenance-mode' ),
			)
		);
	}

	/**
	 * Returns true when maintenance mode is currently active, accounting for schedule.
	 *
	 * @since 2.11
	 * @return bool
	 */
	public static function is_enabled() {
		if ( ! get_option( '_fl_builder_maintenance_enabled' ) ) {
			return false;
		}
		if ( ! get_option( '_fl_builder_maintenance_schedule_enabled' ) ) {
			return true;
		}

		$now   = new DateTime( 'now', wp_timezone() );
		$start = self::get_start_time();
		$end   = self::get_end_time();

		if ( $start && $now < $start ) {
			return false;
		}
		if ( $end && $now > $end ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns true when maintenance mode is scheduled but hasn't started yet.
	 *
	 * @since 2.11
	 * @return bool
	 */
	public static function is_scheduled() {
		if ( ! get_option( '_fl_builder_maintenance_schedule_enabled' ) ) {
			return false;
		}
		$start = self::get_start_time();
		if ( ! $start ) {
			return false;
		}
		return new DateTime( 'now', wp_timezone() ) < $start;
	}

	/**
	 * Returns the post ID of the selected maintenance layout.
	 *
	 * @since 2.11
	 * @return int
	 */
	public static function get_layout_id() {
		return (int) get_option( '_fl_builder_maintenance_layout_id', 0 );
	}

	/**
	 * Returns the published post for the selected maintenance layout, or null.
	 *
	 * Resolved with get_post() using the post's own type rather than a WP_Query with
	 * post_type 'any'. 'any' omits post types registered with exclude_from_search
	 * (such as fl-builder-template), which made a selected Template unresolvable and
	 * fall through to the maintenance notice.
	 *
	 * @since 2.11.1
	 * @return WP_Post|null
	 */
	public static function get_maintenance_post() {
		$layout_id = self::get_layout_id();

		if ( ! $layout_id ) {
			return null;
		}

		$post = get_post( $layout_id );

		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			return null;
		}

		return $post;
	}

	/**
	 * Returns the array of role slugs that bypass maintenance mode.
	 *
	 * @since 2.11
	 * @return array
	 */
	public static function get_bypass_roles() {
		$roles = get_option( '_fl_builder_maintenance_bypass_roles', array() );
		return is_array( $roles ) ? $roles : array();
	}

	/**
	 * Returns the scheduled start time as a DateTime, or null if not set.
	 *
	 * @since 2.11
	 * @return DateTime|null
	 */
	public static function get_start_time() {
		$value = get_option( '_fl_builder_maintenance_start', '' );
		if ( empty( $value ) ) {
			return null;
		}
		$dt = DateTime::createFromFormat( 'Y-m-d\TH:i', $value, wp_timezone() );
		return $dt ? $dt : null;
	}

	/**
	 * Returns the scheduled end time as a DateTime, or null if not set.
	 *
	 * @since 2.11
	 * @return DateTime|null
	 */
	public static function get_end_time() {
		$value = get_option( '_fl_builder_maintenance_end', '' );
		if ( empty( $value ) ) {
			return null;
		}
		$dt = DateTime::createFromFormat( 'Y-m-d\TH:i', $value, wp_timezone() );
		return $dt ? $dt : null;
	}

	/**
	 * Returns true if the current user has a role that bypasses maintenance mode.
	 *
	 * @since 2.11
	 * @return bool
	 */
	public static function current_user_can_bypass() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$user         = wp_get_current_user();
		$bypass_roles = self::get_bypass_roles();

		// Bypass is governed entirely by the selected roles. Admins are NOT special-cased:
		// with no roles selected, everyone (including admins) sees the maintenance layout.
		// This can't lock anyone out of wp-admin, since maybe_maintenance_mode() returns
		// early for is_admin() requests.
		foreach ( $user->roles as $role ) {
			if ( in_array( $role, $bypass_roles, true ) ) {
				return true;
			}
		}

		return false;
	}
}

FLBuilderMaintenanceMode::init();
