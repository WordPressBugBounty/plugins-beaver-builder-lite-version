<?php
namespace FLCacheClear;
class Plugin {

	private $classes = array();

	private $filters = array();

	private static $plugins = array();

	private $allowed_plugins = array(
		'acf',
		'autooptimize',
		'breeze',
		'cacheenabler',
		'cloudflare',
		'defines',
		'fastest',
		'godaddy',
		'hummingbird',
		'kinsta',
		'nginxhelper',
		'pagely',
		'pantheon',
		'pressidium',
		'siteground',
		'spinupwp',
		'supercache',
		'swift',
		'varnish',
		'w3cache',
		'wordpress',
		'wpengine',
	);

	private $actions = array(
		'fl_builder_cache_cleared',
		'fl_builder_after_save_layout',
		'fl_builder_after_save_user_template',
	);

	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'unload_helper_plugin' ) );
		add_action( 'plugins_loaded', array( $this, 'load_files' ) );
		add_action( 'admin_init', array( $this, 'check_urls' ) );
		add_action( 'wp_ajax_fl_cache_plugins_toggle', array( $this, 'ajax_toggle' ) );
	}

	/**
	 * If the base url has changed clear bb cached css/js
	 * @since 2.4.1
	 */
	public function check_urls() {
		$replace = array( 'https://', 'http://', 'www' );
		$current = str_replace( $replace, '', untrailingslashit( get_option( 'siteurl' ) ) );
		$saved   = str_replace( $replace, '', untrailingslashit( base64_decode( get_option( 'fl_site_url' ), true ) ) );

		/**
		 * @see fl_builder_check_urls_enabled
		 */
		if ( \FLBuilderAJAX::doing_ajax() || true !== apply_filters( 'fl_builder_check_urls_enabled', true ) ) {
			return false;
		}

		if ( $current !== $saved ) {
			\FLBuilderUtils::update_option( 'fl_site_url', base64_encode( $current ) );
			if ( '' !== $saved ) {
				\FLBuilderModel::delete_asset_cache_for_all_posts();
				if ( class_exists( '\FLCustomizer' ) && method_exists( '\FLCustomizer', 'clear_all_css_cache' ) ) {
					\FLCustomizer::clear_all_css_cache();
				}

				\FLBuilder::log( 'Beaver Builder: URL change detected, cache cleared.' );
				/**
				 * Fires when the site URL is detected as having changed, triggering a cache rebuild.
				 */
				do_action( 'fl_site_url_changed', $current, $saved );
			}
		}
	}

	/**
	 * AJAX handler for the Cache Clearing Tool toggles on the Tools page.
	 *
	 * @since 2.11
	 */
	public function ajax_toggle() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'cache-plugins' ) ) {
			wp_send_json_error();
		}

		if ( ! \FLBuilderAdmin::current_user_can_access_settings() ) {
			wp_send_json_error();
		}

		$setting = isset( $_POST['setting'] ) ? sanitize_text_field( $_POST['setting'] ) : '';
		if ( ! in_array( $setting, array( 'enabled', 'varnish' ), true ) ) {
			wp_send_json_error();
		}

		$enabled              = 'true' === $_POST['enabled'];
		$settings             = self::get_settings();
		$settings[ $setting ] = $enabled ? 1 : 0;

		\FLBuilderModel::update_admin_settings_option( '_fl_builder_cache_plugins', $settings, false, true );

		wp_send_json_success();
	}

	/**
	 * Get settings.
	 * @since 2.1.5
	 */
	public static function get_settings() {

		$defaults = array(
			'enabled' => true,
			'varnish' => false,
		);

		$settings = \FLBuilderModel::get_admin_settings_option( '_fl_builder_cache_plugins', false );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Remove actions added by the cache helper plugin.
	 * @since 2.1.5
	 */
	public function unload_helper_plugin() {
		if ( class_exists( 'FL_Cache_Buster' ) ) {
			$settings = self::get_settings();
			if ( $settings['enabled'] ) {
				remove_action( 'upgrader_process_complete', array( 'FL_Cache_Buster', 'clear_caches' ) );
				remove_action( 'fl_builder_after_save_layout', array( 'FL_Cache_Buster', 'clear_caches' ) );
				remove_action( 'fl_builder_after_save_user_template', array( 'FL_Cache_Buster', 'clear_caches' ) );
				remove_action( 'fl_builder_cache_cleared', array( 'FL_Cache_Buster', 'clear_caches' ) );
				remove_action( 'template_redirect', array( 'FL_Cache_Buster', 'donotcache' ) );
			}
		}
	}

	/**
	 * Load the cache plugin files.
	 */
	public function load_files() {

		foreach ( glob( FL_BUILDER_CACHE_HELPER_DIR . 'plugins/*.php' ) as $file ) {

			$classname = 'FLCacheClear\\' . ucfirst( str_replace( '.php', '', basename( $file ) ) );

			if ( ! in_array( pathinfo( $file, PATHINFO_FILENAME ), $this->allowed_plugins ) ) {
				$this->triggererror( $file );
				return false;
			} else {
				include_once $file;
			}

			$class = new $classname();

			$actions = isset( $class->actions ) ? $class->actions : $this->actions;
			$filters = isset( $class->filters ) ? $class->filters : $this->filters;

			if ( isset( $class->name ) ) {
				self::$plugins[ $classname ]['name'] = $class->name;
			}

			if ( isset( $class->url ) ) {
				self::$plugins[ $classname ]['url'] = $class->url;
			}

			$settings = self::get_settings();
			if ( ! $settings['enabled'] ) {
				continue;
			}

			if ( ! empty( $actions ) ) {
				$this->add_actions( $class, $actions );
			}
			if ( ! empty( $filters ) ) {
				$this->add_filters( $class, $filters );
			}
		}
	}

	/**
	 * @since 2.6.1
	 */
	private function triggererror( $file ) {
		$filename = basename( $file );
		$message  = "An unexpected file ($filename) was found, possible malware! File: $file";

		( ! \FLBuilderAJAX::doing_ajax() && ! isset( $_GET['fl_builder'] ) ) ? trigger_error( $message, E_USER_WARNING ) : false;
		$args = array(
			'content' => $message,
			'id'      => pathinfo( $file, PATHINFO_FILENAME ),
			'only'    => false,
			'class'   => 'notice-error',
		);
		\FLBuilderAdminNotices::register_notice( $args );
	}

	/**
	 * Return list of plugins to be used on admin page.
	 */
	public static function get_plugins() {
		$plugins = self::$plugins;
		$output  = '';
		foreach ( $plugins as $plugin ) {
			if ( isset( $plugin['url'] ) ) {
				$output .= sprintf( '<li><a target="_blank" href="%s">%s</a>', $plugin['url'], $plugin['name'] );
			} else {
				$output .= sprintf( '<li>%s</li>', $plugin['name'] );
			}
		}
		return '<ul>' . $output . '</ul>';
	}

	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
	public function add_actions( $class, $actions ) {
		foreach ( $actions as $action ) {
			add_action( $action, array( $class, 'run' ) );
		}
	}
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
	public function add_filters( $class, $filters ) {
		foreach ( $filters as $filter ) {
			add_action( $filter, array( $class, 'filters' ) );
		}
	}

	public static function define( $define, $setting = true ) {
		if ( ! defined( $define ) ) {
			define( $define, $setting );
		}
	}
}
new Plugin();
