<?php
final class FLBuilderEnqueue {

	/**
	 * Whether layout scripts and styles have been registered.
	 *
	 * @since 2.9
	 * @var bool $registered
	 */
	static private $registered = false;

	static public function init() {
		// Fire on priority 1, so scripts/styles are registered before they are enqueued.
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::on_enqueue', 1 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::on_enqueue', 1 );
		add_action( 'enqueue_block_editor_assets', __CLASS__ . '::on_enqueue', 1 );

		// Fire late, after most things are enqueued
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::after_enqueue', 9999 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::after_enqueue', 9999 );
		add_action( 'enqueue_block_editor_assets', __CLASS__ . '::after_enqueue', 9999 );
	}

	/**
	 * Central orchestration function.
	 * Fires for both wp_enqueue_scripts and admin_enqueue_scripts hooks.
	 * This is intended to make it easier to see at a glance what groups of libraries are enqueued where.
	 */
	static public function on_enqueue() {

		// Frontend
		if ( ! is_admin() ) {
			self::register_layout_libraries();
		}

		// After this everything should only register for logged in users
		if ( ! is_user_logged_in() ) {
			return;
		}

		// General UI
		self::register_ui_libraries();
	}

	/**
	 * Register the styles and scripts for builder layouts.
	 *
	 * @since 1.7.4
	 * @return void
	 */
	static public function register_layout_libraries() {
		$ver     = FL_BUILDER_VERSION;
		$url     = FLBuilder::plugin_url();
		$css_url = $url . 'css/';
		$js_url  = $url . 'js/';
		$min     = FLBuilder::is_debug() ? '' : '.min';

		if ( self::$registered ) {
			return; // Already registered.
		}
		self::$registered = true;

		// Register additional CSS
		wp_register_style( 'fl-slideshow', $css_url . 'fl-slideshow' . $min . '.css', array( 'yui3' ), $ver );
		wp_register_style( 'jquery-bxslider', $css_url . 'jquery.bxslider.css', array(), $ver );
		wp_register_style( 'jquery-magnificpopup', $css_url . 'jquery.magnificpopup' . $min . '.css', array(), $ver );
		wp_register_style( 'yui3', $css_url . 'yui3.css', array(), $ver );

		// Register icon CDN CSS
		wp_register_style( 'font-awesome-5', FLBuilder::get_fa5_url(), array(), $ver );
		wp_register_style( 'font-awesome', FLBuilder::plugin_url() . 'fonts/fontawesome/' . FLBuilder::get_fa5_version() . '/css/v4-shims.min.css', array( 'font-awesome-5' ), $ver );

		wp_register_style( 'foundation-icons', 'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.css', array(), $ver );

		// Register additional JS
		wp_register_script( 'fl-slideshow', $js_url . 'fl-slideshow' . $min . '.js', array( 'yui3' ), $ver, true );
		wp_register_script( 'fl-gallery-grid', $js_url . 'fl-gallery-grid.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'jquery-bxslider', $js_url . 'libs/jquery.bxslider' . $min . '.js', array( 'jquery-easing', 'jquery-fitvids' ), $ver, true );
		wp_register_script( 'jquery-easing', $js_url . 'libs/jquery.easing.min.js', array( 'jquery' ), '1.4', true );
		wp_register_script( 'jquery-fitvids', $js_url . 'libs/jquery.fitvids.min.js', array( 'jquery' ), '1.2', true );
		wp_register_script( 'jquery-infinitescroll', $js_url . 'libs/jquery.infinitescroll.min.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'jquery-magnificpopup', $js_url . 'libs/jquery.magnificpopup' . $min . '.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'jquery-mosaicflow', $js_url . 'libs/jquery.mosaicflow' . $min . '.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'jquery-waypoints', $js_url . 'libs/jquery.waypoints.min.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'jquery-wookmark', $js_url . 'libs/jquery.wookmark.min.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'jquery-throttle', $js_url . 'libs/jquery.ba-throttle-debounce.min.js', array( 'jquery' ), $ver, true );
		wp_register_script( 'yui3', $js_url . 'libs/yui3.min.js', array(), $ver, true );
		wp_register_script( 'youtube-player', 'https://www.youtube.com/iframe_api', array(), $ver, true );
		wp_register_script( 'vimeo-player', 'https://player.vimeo.com/api/player.js', array(), $ver, true );

		// Custom version?
		wp_deregister_script( 'imagesloaded' );
		wp_register_script( 'imagesloaded', $js_url . 'libs/jquery.imagesloaded.min.js', array( 'jquery' ), $ver, true );
	}

	/**
	 * Register freestanding libraries and vendor files.
	 * These should be registered everywhere but not enqueued.
	 */
	static public function register_ui_libraries() {
		global $wp_version;

		$ver        = FL_BUILDER_VERSION;
		$url        = FLBuilder::plugin_url();
		$is_debug   = FLBuilder::is_debug();
		$css_build  = $url . 'css/build/';
		$js_vendors = $url . 'js/vendors/';
		$js_build   = $url . 'js/build/';
		$tag        = $is_debug ? '.bundle' : '.bundle.min';
		$vendor_tag = $is_debug ? '' : '.min';

		// redux
		wp_register_script( 'redux', "{$js_vendors}redux.min.js", [], $ver, false );

		// FL.symbols API
		wp_register_script( 'fl-symbols', "{$js_build}fl-symbols{$tag}.js", [ 'react' ], $ver );

		// FL.state API
		wp_register_script( 'fl-state', "{$js_build}fl-state{$tag}.js", [ 'react', 'redux' ], $ver );

		$controls_js_deps = [
			'react',
			'wp-i18n',
			'wp-hooks',
			'wp-data',
			'wp-api-fetch',
			'jquery',
			'jquery-ui-sortable',
			'fl-builder-api',
			'fl-symbols',
			'fl-state',
		];
		wp_register_style( 'fl-controls', "{$css_build}fl-controls{$tag}.css", [], $ver );
		wp_register_script( 'fl-controls', "{$js_build}fl-controls{$tag}.js", $controls_js_deps, $ver, false );

		// Sets up FL.Builder API
		wp_register_script( 'fl-builder-api', "{$js_build}builder-api{$tag}.js", [ 'jquery' ], $ver );

		// Combined stylesheets (used to be fl-builder.min.css )
		wp_register_style( 'fl-builder-css', "{$css_build}builder-css{$tag}.css", [], $ver );

		// FL.Builder.settingsForms API
		$forms_css_deps = [ 'fl-controls', 'fl-builder-css' ];
		wp_register_style( 'fl-builder-forms', "{$css_build}builder-forms{$tag}.css", $forms_css_deps, $ver );

		$form_js_deps = [
			'react',
			'react-dom',
			'jquery',
			'fl-controls',
			'fl-state',
			'fl-builder-api',
			'wp-components',
			'wp-i18n',
			'wp-hooks',
		];
		wp_register_script( 'fl-builder-forms', "{$js_build}builder-forms{$tag}.js", $form_js_deps, $ver );

		// FL.Builder.system
		$sys_js_deps = [
			'react',
			'react-dom',
			'redux',
			'fl-builder-api',
		];
		wp_register_style( 'fl-builder-system', "{$css_build}builder-system{$tag}.css", [], $ver );
		wp_register_script( 'fl-builder-system', "{$js_build}builder-system{$tag}.js", $sys_js_deps, $ver, true );

		// Module Blocks
		wp_register_style( 'fl-builder-module-blocks', "{$css_build}module-blocks{$tag}.css", [], $ver );
		wp_register_script( 'fl-builder-module-blocks', "{$js_build}module-blocks{$tag}.js", [ 'wp-blocks' ], $ver, true );
	}

	/**
	 * Auto enqueue things before styles/scripts get rendered.
	 */
	static public function after_enqueue() {

		if ( wp_script_is( 'fl-controls', 'enqueued' ) ) {
			wp_enqueue_style( 'fl-controls' );
			wp_enqueue_media(); // Needed for Background Field
		}

		if ( wp_script_is( 'fl-builder-forms', 'enqueued' ) ) {
			wp_enqueue_style( 'fl-builder-forms' );
		}

		if ( wp_script_is( 'fl-builder-system', 'enqueued' ) ) {
			wp_enqueue_style( 'fl-builder-system' );
		}

		if ( wp_script_is( 'fl-builder-module-blocks', 'enqueued' ) ) {
			wp_enqueue_style( 'fl-builder-module-blocks' );
		}
	}
}

FLBuilderEnqueue::init();
