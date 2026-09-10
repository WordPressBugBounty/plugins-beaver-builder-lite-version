<?php

FLBuilder::register_module_deprecations( 'button', [
	// Deprecate to remove the wrapper element
	'v1' => [
		'config' => [
			'include_wrapper' => true,
			'element_setting' => true,
		],
	],
	// Deprecate to use the button element instead of a link with button semantics
	'v2' => [],
] );
