<?php

FLBuilder::register_module_alias( 'popup-centered', [
	'name'     => __( 'Centered', 'fl-builder' ),
	'module'   => 'popup',
	'location' => 'inline',
	'icon'     => 'rectangle-list.svg',
	'template' => [],
	'settings' => [
		'popup_position'            => 'auto auto auto auto',
		'size'                      => [
			'width'      => [
				'length' => 500,
				'unit'   => 'px',
			],
			'max_width'  => [
				'length' => '100',
				'unit'   => '%',
			],
			'height'     => [
				'length' => '',
				'unit'   => 'px',
			],
			'min_height' => [
				'length' => '200',
				'unit'   => 'px',
			],
		],
		'popup_border'              => [
			'style'  => 'solid',
			'color'  => 'rgb(223, 227, 231)',
			'width'  => [
				'top'    => '4',
				'right'  => '4',
				'bottom' => '4',
				'left'   => '4',
			],
			'radius' => [
				'top_left'     => '16',
				'top_right'    => '16',
				'bottom_left'  => '16',
				'bottom_right' => '16',
			],
			'shadow' => [
				'color' => 'rgba(106, 119, 124, 0.2)',
				'blur'  => '10',
			],
		],
		'close_button_position'     => 'absolute',
		'close_button_inset_top'    => '-20',
		'close_button_inset_right'  => '-20',
		'close_button_inset_bottom' => '',
		'close_button_inset_left'   => '',
		'close_button_size'         => '24',
		'close_button_size_unit'    => 'px',
		'close_button_background'   => '#ffffff',
		'close_button_border'       => [
			'style'  => 'solid',
			'color'  => 'rgb(223, 227, 231)',
			'width'  => [
				'top'    => '1',
				'right'  => '1',
				'bottom' => '1',
				'left'   => '1',
			],
			'radius' => [
				'top_left'     => '100',
				'top_right'    => '100',
				'bottom_left'  => '100',
				'bottom_right' => '100',
			],
			'shadow' => [
				'color' => 'rgba(106, 119, 124, 0.2)',
				'blur'  => '10',
			],
		],
		'animation'                 => [
			'style'    => 'fade-in',
			'delay'    => 0,
			'duration' => 0.5,
		],
		'margin_top'                => '',
		'margin_right'              => '',
		'margin_bottom'             => '',
		'margin_left'               => '',
	],
] );

FLBuilder::register_module_alias( 'popup-sidebar', [
	'name'     => __( 'Sidebar', 'fl-builder' ),
	'module'   => 'popup',
	'location' => 'inline',
	'icon'     => 'sidebar.svg',
	'template' => [],
	'settings' => [
		'popup_position'            => '0 0 auto auto',
		'popup_size'                => [
			'width'      => [
				'length' => 300,
				'unit'   => 'px',
			],
			'max_width'  => [
				'length' => 100,
				'unit'   => '%',
			],
			'height'     => [
				'length' => 100,
				'unit'   => '%',
			],
			'min_height' => [
				'length' => '',
				'unit'   => 'px',
			],
		],
		'popup_border'              => [
			'style'  => 'solid',
			'color'  => 'rgb(223, 227, 231)',
			'width'  => [
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '4',
			],
			'radius' => [
				'top_left'     => '',
				'top_right'    => '',
				'bottom_left'  => '',
				'bottom_right' => '',
			],
			'shadow' => [
				'color' => 'rgba(106, 119, 124, 0.2)',
				'blur'  => '10',
			],
		],
		'close_button_position'     => 'absolute',
		'close_button_inset_top'    => '20',
		'close_button_inset_right'  => '20',
		'close_button_inset_bottom' => '',
		'close_button_inset_left'   => '',
		'close_button_size'         => '30',
		'close_button_size_unit'    => 'px',
		'close_button_background'   => '',
		'close_button_border'       => [
			'style'  => 'none',
			'color'  => '',
			'width'  => [
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			],
			'radius' => [
				'top_left'     => '',
				'top_right'    => '',
				'bottom_left'  => '',
				'bottom_right' => '',
			],
			'shadow' => [
				'color' => '',
				'blur'  => '',
			],
		],
		'animation'                 => [
			'style'    => 'slide-in-right',
			'delay'    => 0,
			'duration' => 0.5,
		],
		'margin_top'                => '',
		'margin_right'              => '',
		'margin_bottom'             => '',
		'margin_left'               => '',
	],
] );

FLBuilder::register_module_alias( 'popup-corner', [
	'name'     => __( 'Corner', 'fl-builder' ),
	'module'   => 'popup',
	'location' => 'inline',
	'icon'     => 'square-list.svg',
	'template' => [],
	'settings' => [
		'popup_position'            => 'auto 0 0 auto',
		'popup_size'                => [
			'width'      => [
				'length' => 300,
				'unit'   => 'px',
			],
			'max_width'  => [
				'length' => 100,
				'unit'   => '%',
			],
			'height'     => [
				'length' => '',
				'unit'   => 'px',
			],
			'min_height' => [
				'length' => 200,
				'unit'   => 'px',
			],
		],
		'popup_border'              => [
			'style'  => 'solid',
			'color'  => 'rgb(223, 227, 231)',
			'width'  => [
				'top'    => '4',
				'right'  => '4',
				'bottom' => '4',
				'left'   => '4',
			],
			'radius' => [
				'top_left'     => '16',
				'top_right'    => '16',
				'bottom_left'  => '16',
				'bottom_right' => '16',
			],
			'shadow' => [
				'color' => 'rgba(106, 119, 124, 0.2)',
				'blur'  => '10',
			],
		],
		'close_button_position'     => 'absolute',
		'close_button_inset_top'    => '-20',
		'close_button_inset_right'  => '',
		'close_button_inset_bottom' => '',
		'close_button_inset_left'   => '-20',
		'close_button_size'         => '24',
		'close_button_size_unit'    => 'px',
		'close_button_background'   => '#ffffff',
		'close_button_border'       => [
			'style'  => 'solid',
			'color'  => 'rgb(223, 227, 231)',
			'width'  => [
				'top'    => '1',
				'right'  => '1',
				'bottom' => '1',
				'left'   => '1',
			],
			'radius' => [
				'top_left'     => '100',
				'top_right'    => '100',
				'bottom_left'  => '100',
				'bottom_right' => '100',
			],
			'shadow' => [
				'color' => 'rgba(106, 119, 124, 0.2)',
				'blur'  => '10',
			],
		],
		'animation'                 => [
			'style'    => 'slide-in-right',
			'delay'    => 0,
			'duration' => 0.5,
		],
		'margin_top'                => '20',
		'margin_right'              => '20',
		'margin_bottom'             => '20',
		'margin_left'               => '20',
		'margin_unit'               => 'px',
	],
] );

FLBuilder::register_module_alias( 'popup-full', [
	'name'     => __( 'Fullscreen', 'fl-builder' ),
	'module'   => 'popup',
	'location' => 'inline',
	'icon'     => 'dropdown-list.svg',
	'template' => [],
	'settings' => [
		'popup_position'            => 'auto auto auto auto',
		'popup_size'                => [
			'width'      => [
				'length' => 100,
				'unit'   => '%',
			],
			'max_width'  => [
				'length' => '',
				'unit'   => 'px',
			],
			'height'     => [
				'length' => 100,
				'unit'   => '%',
			],
			'min_height' => [
				'length' => '',
				'unit'   => 'px',
			],
		],
		'popup_border'              => [
			'style'  => 'none',
			'color'  => '',
			'width'  => [
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			],
			'radius' => [
				'top_left'     => '',
				'top_right'    => '',
				'bottom_left'  => '',
				'bottom_right' => '',
			],
			'shadow' => [
				'color' => '',
				'blur'  => '',
			],
		],
		'close_button_position'     => 'absolute',
		'close_button_inset_top'    => '20',
		'close_button_inset_right'  => '20',
		'close_button_inset_bottom' => '',
		'close_button_inset_left'   => '',
		'close_button_size'         => '30',
		'close_button_size_unit'    => 'px',
		'close_button_background'   => '',
		'close_button_border'       => [
			'style'  => 'none',
			'color'  => '',
			'width'  => [
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			],
			'radius' => [
				'top_left'     => '',
				'top_right'    => '',
				'bottom_left'  => '',
				'bottom_right' => '',
			],
			'shadow' => [
				'color' => '',
				'blur'  => '',
			],
		],
		'animation'                 => [
			'style'    => 'slide-in-right',
			'delay'    => 0,
			'duration' => 0.5,
		],
		'margin_top'                => '',
		'margin_right'              => '',
		'margin_bottom'             => '',
		'margin_left'               => '',
	],
] );
