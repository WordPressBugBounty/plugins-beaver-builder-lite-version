<?php

defined( 'ABSPATH' ) || exit;

/**
 * Serves bb-plugin translations from translate.wpbeaverbuilder.com by:
 *  1. Injecting available translations into the update_plugins transient so
 *     WP's update system discovers them (Dashboard > Updates / auto-updates).
 *  2. Providing the download URL when WP's Language_Pack_Upgrader asks for it.
 */
class FLBuilderTranslations {

	const API_URL = 'https://translate.wpbeaverbuilder.com/wp-json/bb-translations/v1/translations';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function init() {
		// Lite is on WP.org which serves its own translations — don't interfere.
		if ( defined( 'FL_BUILDER_LITE' ) && true === FL_BUILDER_LITE ) {
			return;
		}

		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'inject_translation_updates' ) );
		add_filter( 'translations_api', array( __CLASS__, 'filter_translations_api' ), 10, 3 );
		add_action( 'fl_builder_cache_cleared', array( __CLASS__, 'clear_cache' ) );
	}

	/**
	 * Locales this site actually wants translations for: its active locale
	 * plus every language pack already installed for anything else on the
	 * site. Mirrors the signal WordPress.org's own update-check API uses
	 * (get_available_languages()) so BB behaves like every other plugin
	 * instead of offering every locale it has ever built.
	 *
	 * @return string[]
	 */
	private static function wanted_locales() {
		return array_unique( array_merge( array( get_locale() ), array_values( get_available_languages() ) ) );
	}

	/**
	 * Inject our translation updates into the update_plugins transient so they
	 * appear in Dashboard > Updates alongside wordpress.org translations.
	 *
	 * @param object $transient The update_plugins transient value.
	 * @return object
	 */
	public static function inject_translation_updates( $transient ) {
		if ( empty( $transient->checked ) || ! isset( $transient->checked['bb-plugin/fl-builder.php'] ) ) {
			return $transient;
		}

		$version   = $transient->checked['bb-plugin/fl-builder.php'];
		$installed = wp_get_installed_translations( 'plugins' );
		$wanted    = self::wanted_locales();
		$available = self::fetch_translations( $version );

		if ( empty( $available ) ) {
			return $transient;
		}

		if ( ! isset( $transient->translations ) ) {
			$transient->translations = array();
		}

		// Index already-queued translation updates to avoid duplicates.
		$queued = array();
		foreach ( $transient->translations as $t ) {
			if ( 'bb-plugin' === $t['slug'] ) {
				$queued[ $t['language'] ] = true;
			}
		}

		foreach ( $available as $translation ) {
			$locale = $translation['language'];

			// Skip if already queued.
			if ( isset( $queued[ $locale ] ) ) {
				continue;
			}

			// Skip locales the site hasn't asked for.
			if ( ! in_array( $locale, $wanted, true ) ) {
				continue;
			}

			// Skip if the installed translation is already up-to-date.
			if ( isset( $installed['fl-builder'][ $locale ] ) ) {
				$installed_updated = $installed['fl-builder'][ $locale ]['PO-Revision-Date'] ?? '';
				if ( strtotime( $installed_updated ) >= strtotime( $translation['updated'] ) ) {
					continue;
				}
			}

			$transient->translations[] = array(
				'type'       => 'plugin',
				'slug'       => 'bb-plugin',
				'language'   => $locale,
				'version'    => $translation['version'],
				'updated'    => $translation['updated'],
				'package'    => $translation['package'],
				'autoupdate' => true,
			);
		}

		return $transient;
	}

	/**
	 * Provide translation package info when WP's Language_Pack_Upgrader asks
	 * for it (e.g. during a language pack install or auto-update).
	 *
	 * @param false|object $res  Current API response.
	 * @param string       $type Type of request ('plugins' or 'themes').
	 * @param object       $args Request args including slug and version.
	 * @return false|object
	 */
	public static function filter_translations_api( $res, $type, $args ) {
		$slug    = is_object( $args ) ? $args->slug : ( $args['slug'] ?? '' );
		$version = is_object( $args ) ? ( $args->version ?? '' ) : ( $args['version'] ?? '' );

		if ( 'plugins' !== $type || 'bb-plugin' !== $slug ) {
			return $res;
		}

		$available = self::fetch_translations( $version );

		if ( false === $available ) {
			return $res;
		}

		$wanted    = self::wanted_locales();
		$available = array_values( array_filter( $available, function ( $translation ) use ( $wanted ) {
			return in_array( $translation['language'], $wanted, true );
		} ) );

		return array( 'translations' => $available );
	}

	/**
	 * Fetch available translations from our API for a given plugin version.
	 * Returns false on a network/API error, or an array (possibly empty).
	 *
	 * Cached as a site transient keyed by version + wanted locales, for
	 * 12 hours on success or 1 hour on a failed/errored fetch (shorter so an
	 * API outage recovers faster once it's back, without retrying on every
	 * call in the meantime). Without this, pre_set_site_transient_update_plugins
	 * firing repeatedly per request (or per site, across a fleet) turns into a
	 * live HTTP call on every wp_update_plugins() run, far more often than the
	 * API needs to actually be hit. Cleared early via clear_cache() on fl_builder_cache_cleared.
	 *
	 * @param string $version Plugin version.
	 * @return array|false
	 */
	private static function fetch_translations( $version ) {
		$locales = self::wanted_locales();
		sort( $locales ); // Deterministic cache key regardless of filesystem enumeration order.

		$cache_key = 'fl_builder_translations_' . md5( 'bb-plugin|' . $version . '|' . implode( ',', $locales ) );
		$cached    = get_site_transient( $cache_key );

		if ( 'error' === $cached ) {
			return false;
		}
		if ( false !== $cached ) {
			return $cached;
		}

		self::remember_cache_key( $cache_key );

		$url      = add_query_arg( array(
			'slug'    => 'bb-plugin',
			'version' => $version,
		), self::API_URL );
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_site_transient( $cache_key, 'error', HOUR_IN_SECONDS );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $body['translations'] ) || ! is_array( $body['translations'] ) ) {
			set_site_transient( $cache_key, 'error', HOUR_IN_SECONDS );
			return false;
		}

		set_site_transient( $cache_key, $body['translations'], 12 * HOUR_IN_SECONDS );

		return $body['translations'];
	}

	/**
	 * Track a cache key so clear_cache() can find it later without having to
	 * reconstruct it (the key varies by version + locale-set, so there's
	 * no single fixed key to delete the way e.g. FLBuilderFonts's Google Fonts
	 * cache does).
	 *
	 * @param string $key
	 * @return void
	 */
	private static function remember_cache_key( $key ) {
		$keys = get_site_option( 'fl_builder_translations_cache_keys', array() );

		if ( ! in_array( $key, $keys, true ) ) {
			$keys[] = $key;
			update_site_option( 'fl_builder_translations_cache_keys', $keys );
		}
	}

	/**
	 * Clear every cached translation fetch. Hooked to fl_builder_cache_cleared
	 * so Tools > Clear Cache and WP-CLI's cache-clear command also refresh
	 * translation data instead of waiting out the transient TTL.
	 *
	 * @return void
	 */
	public static function clear_cache() {
		foreach ( get_site_option( 'fl_builder_translations_cache_keys', array() ) as $key ) {
			delete_site_transient( $key );
		}

		delete_site_option( 'fl_builder_translations_cache_keys' );
	}
}
