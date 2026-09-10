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
	 * @param string $version Plugin version.
	 * @return array|false
	 */
	private static function fetch_translations( $version ) {
		$url      = add_query_arg( array(
			'slug'    => 'bb-plugin',
			'version' => $version,
		), self::API_URL );
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $body['translations'] ) || ! is_array( $body['translations'] ) ) {
			return false;
		}

		return $body['translations'];
	}
}
