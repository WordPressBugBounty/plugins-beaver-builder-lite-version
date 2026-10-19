<?php
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'color',
	'selector'     => '.fl-row .fl-col ' . $settings->tag . '.fl-node-' . $id . ',
		.fl-row .fl-col ' . $settings->tag . '.fl-node-' . $id . ' a,
		' . $settings->tag . '.fl-node-' . $id . ',
		' . $settings->tag . '.fl-node-' . $id . ' a',
	'prop'         => 'color',
	// Evaluate per breakpoint so a responsive-only color (e.g. mobile set, desktop blank) still renders.
	'enabled'      => 'not_empty',
) );

FLBuilderCSS::typography_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'typography',
	'selector'     => [
		".fl-node-$id.fl-module-heading",
		".fl-node-$id.fl-module-heading :where(a, q, p, span)",
	],
) );
