<?php

ob_start();

if ( is_callable( $module->render ?? null ) ) {
	echo call_user_func( $module->render, $module->settings, $module );
} elseif ( has_filter( 'fl_builder_module_frontend_custom_' . $module->slug ) ) {
	echo apply_filters( 'fl_builder_module_frontend_custom_' . $module->slug, (array) $module->settings, $module );
} else {
	/**
	 * Path to the frontend template file used to render a module.
	 */
	include apply_filters( 'fl_builder_module_frontend_file', $module->path( 'includes/frontend.php' ), $module );
}

$output = ob_get_clean();

// All container based nodes should only stick to wrapper custom attributes.
if ( ! in_array( $module->slug, array( 'row', 'col', 'box', 'loop' ), true ) ) {
	$custom = apply_filters( 'fl_builder_node_content_attributes', $module->settings->custom_attributes, $module );
	$output = FLBuilderCustomAttributes::inject_custom_attributes( $custom, $output );
}

/**
 * HTML output of a rendered module before it is echoed to the page.
 */
echo apply_filters( 'fl_builder_render_module_content', $output, $module );
