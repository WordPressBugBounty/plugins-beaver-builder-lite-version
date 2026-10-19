<?php

/**
 * Secret-URL preview bypass for Beaver Builder maintenance mode.
 *
 * Lets a site owner share a preview link so a logged-out visitor can view the
 * live site during maintenance without a WordPress account. Visiting the site
 * with the secret token as a query param sets a bypass cookie, then redirects
 * to a clean URL; subsequent requests carry the cookie and skip the splash.
 *
 * @since 2.11.2
 */
final class FLBuilderMaintenanceBypass {

	/**
	 * Name of the bypass cookie.
	 */
	const COOKIE = 'fl_maintenance_bypass';

	/**
	 * Query param that carries the secret token in a shared preview link.
	 */
	const PARAM = 'fl_maintenance_preview';

	/**
	 * Option that stores the secret token.
	 */
	const OPTION = '_fl_builder_maintenance_bypass_key';

	/**
	 * @return void
	 */
	public static function init() {
		// Priority 0 so the cookie/redirect runs before maybe_maintenance_mode (priority 1).
		add_action( 'template_redirect', __CLASS__ . '::handle_bypass', 0 );
		add_action( 'wp_ajax_fl_maintenance_regenerate_key', __CLASS__ . '::ajax_regenerate_key' );
	}

	/**
	 * Returns the secret token, minting one on first use.
	 *
	 * Minting writes an option, so this is only ever called from admin/AJAX
	 * contexts (settings render, regenerate). Front-end gate checks use
	 * stored_key() instead, which never writes.
	 *
	 * @since 2.11.2
	 * @return string
	 */
	public static function get_bypass_key() {
		$key = self::stored_key();
		if ( '' === $key ) {
			$key = wp_generate_password( 32, false );
			update_option( self::OPTION, $key );
		}
		return $key;
	}

	/**
	 * Returns the stored secret token without minting.
	 *
	 * Safe on front-end requests: never writes. Returns '' when no token has
	 * been generated yet.
	 *
	 * @since 2.11.2
	 * @return string
	 */
	private static function stored_key() {
		return (string) get_option( self::OPTION, '' );
	}

	/**
	 * Returns the shareable preview URL carrying the secret token.
	 *
	 * Admin/AJAX context only (mints the token if needed).
	 *
	 * @since 2.11.2
	 * @return string
	 */
	public static function get_preview_url() {
		return add_query_arg( self::PARAM, self::get_bypass_key(), home_url( '/' ) );
	}

	/**
	 * Whether the current request carries a valid bypass cookie.
	 *
	 * @since 2.11.2
	 * @return bool
	 */
	public static function has_valid_bypass_cookie() {
		$key = self::stored_key();
		if ( '' === $key || empty( $_COOKIE[ self::COOKIE ] ) ) {
			return false;
		}
		return hash_equals( $key, sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) );
	}

	/**
	 * Whether the current request carries a valid preview token in the query string.
	 *
	 * Pure check with no side effects (no cookie, no redirect, no exit), so the
	 * token match/mismatch logic can be unit-tested in isolation from handle_bypass().
	 *
	 * The token in the URL is the secret itself, so no nonce applies — the link is
	 * meant to be shared with people who have no WordPress session.
	 *
	 * @since 2.11.2
	 * @return bool
	 */
	public static function is_valid_preview_request() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ self::PARAM ] ) ) {
			return false;
		}
		$key = self::stored_key();
		if ( '' === $key ) {
			return false;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = sanitize_text_field( wp_unslash( $_GET[ self::PARAM ] ) );
		return hash_equals( $key, $token );
	}

	/**
	 * Handles the preview token on the front end.
	 *
	 * On a valid token param, sets the bypass cookie and redirects to the same
	 * URL without the token, so the secret never lingers in the address bar,
	 * history, logs, or Referer headers. On any request that already carries a
	 * valid cookie, sends no-cache headers so a cookie-ignoring proxy/CDN can't
	 * cache the live page and serve it to real visitors.
	 *
	 * @since 2.11.2
	 * @return void
	 */
	public static function handle_bypass() {
		if ( ! FLBuilderMaintenanceMode::is_enabled() ) {
			return;
		}

		if ( self::is_valid_preview_request() ) {
			self::set_bypass_cookie( self::stored_key() );
			// No-cache so a proxy/CDN can't cache this Set-Cookie redirect and replay it.
			nocache_headers();
			wp_safe_redirect( remove_query_arg( self::PARAM ) );
			exit;
		}

		if ( self::has_valid_bypass_cookie() ) {
			nocache_headers();
		}
	}

	/**
	 * Sets the bypass cookie for 7 days.
	 *
	 * @since 2.11.2
	 * @param string $key
	 * @return void
	 */
	private static function set_bypass_cookie( $key ) {
		setcookie(
			self::COOKIE,
			$key,
			array(
				'expires'  => time() + 7 * DAY_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * AJAX handler: regenerates the secret token, invalidating every shared link
	 * and every live bypass cookie.
	 *
	 * @since 2.11.2
	 * @return void
	 */
	public static function ajax_regenerate_key() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'fl-maintenance-save' ) ) {
			wp_send_json_error();
		}

		if ( ! current_user_can( FLBuilderAdmin::admin_settings_capability() ) ) {
			wp_send_json_error();
		}

		$key = wp_generate_password( 32, false );
		update_option( self::OPTION, $key );

		wp_send_json_success(
			array(
				'url' => add_query_arg( self::PARAM, $key, home_url( '/' ) ),
			)
		);
	}
}

FLBuilderMaintenanceBypass::init();
