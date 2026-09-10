<?php

FLBuilderCSS::rule( array(
	'selector' => ".fl-builder-content .fl-node-$id.fl-module-rich-text.fl-rich-text, .fl-builder-content .fl-node-$id.fl-module-rich-text.fl-rich-text *",
	'enabled'  => ! empty( $settings->color ),
	'props'    => array(
		'color' => $settings->color,
	),
) );

FLBuilderCSS::rule( array(
	'selector' => ":where(.fl-builder-content .fl-node-$id.fl-module-rich-text.fl-rich-text *)",
	'enabled'  => empty( $settings->color ),
	'props'    => array(
		'color' => 'inherit',
	),
) );

FLBuilderCSS::typography_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'typography',
	'selector'     => ".fl-builder-content .fl-node-$id.fl-module-rich-text.fl-rich-text, .fl-builder-content .fl-node-$id.fl-module-rich-text.fl-rich-text *:not(b, strong)",
) );
