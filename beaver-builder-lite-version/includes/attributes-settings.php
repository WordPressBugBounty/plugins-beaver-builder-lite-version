<?php

FLBuilder::register_settings_form('custom_attributes', [
	'title' => __( 'Custom Attributes', 'fl-builder' ),
	'tabs'  => [
		'custom_attributes' => [
			'title'    => __( 'Attribute', 'fl-builder' ),
			'sections' => [
				'general' => [
					'title'  => '',
					'fields' => [
						'key'      => [
							'type'  => 'text',
							'label' => __( 'Key', 'fl-builder' ),
							'help'  => __( 'Only alphanumeric characters in lowercase, underscores, and hyphens are allowed.', 'fl-builder' ),
						],
						'value'    => [
							'type'  => 'text',
							'label' => __( 'Value', 'fl-builder' ),
						],
						'target'   => [
							'type'    => 'select',
							'default' => 'wrapper',
							'label'   => __( 'Target', 'fl-builder' ),
							'help'    => __( 'Choose which element the attribute will be applied to.', 'fl-builder' ),
							'options' => [
								'wrapper' => __( 'Wrapper', 'fl-builder' ),
								'custom'  => __( 'Custom', 'fl-builder' ),
							],
							'toggle'  => [
								'custom' => [
									'fields' => [ 'selector' ],
								],
							],
						],
						'selector' => [
							'type'        => 'text',
							'label'       => __( 'Selector', 'fl-builder' ),
							'placeholder' => 'div.class #id span > *',
							'help'        => __( 'Target the inner element(s) within the node wrapper to apply the attribute to. Only selectors with (id/classes/tag) & (>/*) are supported.', 'fl-builder' ),
						],
					],
				],
			],
		],
	],
]);
