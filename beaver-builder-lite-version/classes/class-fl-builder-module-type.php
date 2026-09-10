<?php

/**
 * Declarative API for registering BB modules without PHP class files.
 *
 * Usage:
 *   FLBuilderModuleType::register( 'hero-a1b2c3d4e5f6', [
 *       'name'        => 'Hero',
 *       'description' => 'A hero section',
 *       'category'    => 'Design System',
 *       'namespace'   => 'ds',
 *       'form'        => [ ... ],
 *       'render'      => function( $settings, $module ) { return '<div>...</div>'; },
 *       'css'         => function( $module ) { echo '.my-class { color: red; }'; },
 *       'js'          => function( $module ) { echo 'console.log("init");'; },
 *   ] );
 */
class FLBuilderModuleType {

	/**
	 * Register a module type from a configuration array.
	 *
	 * @param string $key Unique module key. If a namespace is provided, the
	 *                    final slug will be "{namespace}-{key}".
	 * @param array $config {
	 *     Module configuration.
	 *
	 *     @type string   $name            Display name.
	 *     @type string   $description     Short description.
	 *     @type string   $category        Module category.
	 *     @type string   $namespace       Optional. Namespace prefix for slug composition and grouping.
	 *     @type array    $form            Optional. Settings form configuration.
	 *     @type callable $render          Optional. HTML render callback. Receives ($settings, $module), returns HTML string.
	 *     @type callable $css             Optional. CSS render callback. Receives ($module). Can echo CSS or use FLBuilderCSS::rule().
	 *     @type callable $js              Optional. JS render callback. Receives ($module). Should echo JavaScript code.
	 *     @type string   $icon            Optional. Icon filename or SVG.
	 *     @type string   $group           Optional. Module group for panel organization.
	 *     @type bool     $enabled         Optional. Default true.
	 *     @type bool     $partial_refresh Optional. Default true.
	 *     @type bool     $include_wrapper Optional. Default true.
	 *     @type array    $accepts         Optional. Child module types this module accepts (containers).
	 *     @type array    $parents         Optional. Allowed parent module types.
	 *     @type bool     $top_level       Optional. Allow at layout root.
	 * }
	 */
	static public function register( $key, $config ) {
		$form = isset( $config['form'] ) ? $config['form'] : [];
		unset( $config['form'] );

		// Extract callbacks before passing config to FLBuilderModule constructor.
		$render    = isset( $config['render'] ) ? $config['render'] : null;
		$css       = isset( $config['css'] ) ? $config['css'] : null;
		$js        = isset( $config['js'] ) ? $config['js'] : null;
		$namespace = isset( $config['namespace'] ) ? $config['namespace'] : null;
		unset( $config['render'], $config['css'], $config['js'], $config['namespace'] );

		// Compose slug from namespace + key.
		$config['slug'] = $namespace ? $namespace . '-' . $key : $key;

		if ( ! isset( $config['partial_refresh'] ) ) {
			$config['partial_refresh'] = true;
		}
		if ( ! isset( $config['include_wrapper'] ) ) {
			$config['include_wrapper'] = true;
		}

		// Create a module instance directly (no PHP class file needed).
		$instance                 = new FLBuilderModule( $config );
		$instance->clone_instance = true;
		$instance->namespace      = $namespace;

		// Store callbacks as properties (survive cloning).
		// Using css_callback/js_callback to avoid collision with
		// $module->css and $module->js arrays used by add_css()/add_js().
		if ( $render ) {
			$instance->render = $render;
		}
		if ( $css ) {
			$instance->css_callback = $css;
		}
		if ( $js ) {
			$instance->js_callback = $js;
		}

		FLBuilder::register_module( $instance, $form );
	}
}
