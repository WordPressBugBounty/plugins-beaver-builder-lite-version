<?php
FLBuilder::register_module_deprecations( 'menu', [
	// Deprecates old HTML markup & the photo module within the search module.
	'v1' => [],
	// Deprecates hiding the search label & the button module within the search module.
	'v2' => [
		'defaults' => [
			'search_action' => 'fullscreen',
		],
	],
] );
