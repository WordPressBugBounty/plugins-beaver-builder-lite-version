<?php

$breakpoints = array( '', 'large', 'medium', 'responsive' );

// CSS selectors with compat for v1 so this file doesn't need to be deprecated.
$group_selector   = $module->root_selector( [ '.fl-button-group' ] );
$buttons_selector = $module->root_selector( [ '.fl-button-group', '.fl-button-group-buttons' ] );
$vert_selector    = $module->root_selector( [ '.fl-button-group-layout-vertical', '.fl-button-group-buttons' ] );
$horiz_selector   = $module->root_selector( [ '.fl-button-group-layout-horizontal', '.fl-button-group-buttons' ] );

// Width, Alignment, Space Between buttons
if ( '' === $settings->width ) {
	FLBuilderCSS::rule( array(
		'selector' => array(
			$vert_selector . ' .fl-button:is(a, button)',
			$horiz_selector . ' .fl-button:is(a, button)',
		),
		'props'    => array(
			'width' => '100%',
		),
	) );
} elseif ( 'custom' === $settings->width ) {
	FLBuilderCSS::responsive_rule( array(
		'settings'     => $settings,
		'setting_name' => 'custom_width',
		'selector'     => array(
			$vert_selector . ' .fl-button:is(a, button)',
			$horiz_selector . ' .fl-button:is(a, button)',
		),
		'prop'         => 'width',
	) );
}
?>

<?php echo $horiz_selector; ?> {
	<?php
	$button_group_horiz_align = '';
	if ( 'left' == $settings->align ) {
		$button_group_horiz_align = 'flex-start';
	} elseif ( 'center' == $settings->align ) {
		$button_group_horiz_align = 'center';
	} elseif ( 'right' == $settings->align ) {
		$button_group_horiz_align = 'flex-end';
	}
	?>
	justify-content: <?php echo $button_group_horiz_align; ?>
}

<?php

// Alignment on vertical layout.
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'align',
	'selector'     => "$vert_selector .fl-button-group-button .fl-button-wrap",
	'prop'         => 'text-align',
) );

// Align Horizontal -- Desktop
if ( 'horizontal' === $settings->layout && ! empty( $settings->align ) ) {
	FLBuilderCSS::rule( array(
		'selector' => $horiz_selector,
		'media'    => 'default',
		'props'    => array(
			'justify-content' => $module->map_horizontal_alignment( $settings->align ),
		),
	) );
}

// Align Horizontal -- Large
if ( 'horizontal' === $settings->layout && ! empty( $settings->align_large ) ) {
	FLBuilderCSS::rule( array(
		'selector' => $horiz_selector,
		'media'    => 'large',
		'props'    => array(
			'justify-content' => $module->map_horizontal_alignment( $settings->align_large ),
		),
	) );
}

// Align Horizontal -- Medium
if ( 'horizontal' === $settings->layout && ! empty( $settings->align_medium ) ) {
	FLBuilderCSS::rule( array(
		'selector' => $horiz_selector,
		'media'    => 'medium',
		'props'    => array(
			'justify-content' => $module->map_horizontal_alignment( $settings->align_medium ),
		),
	) );
}

// Align Horizontal -- Responsive
if ( 'horizontal' === $settings->layout && ! empty( $settings->align_responsive ) ) {
	FLBuilderCSS::rule( array(
		'selector' => $horiz_selector,
		'media'    => 'responsive',
		'props'    => array(
			'justify-content' => $module->map_horizontal_alignment( $settings->align_responsive ),
		),
	) );
}

// Button Spacing
FLBuilderCSS::dimension_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'button_spacing',
	'selector'     => ".fl-builder-content $buttons_selector .fl-button-group-button",
	'props'        => array(
		'padding-top'    => 'button_spacing_top',
		'padding-right'  => 'button_spacing_right',
		'padding-bottom' => 'button_spacing_bottom',
		'padding-left'   => 'button_spacing_left',
	),
) );

// Text (Color, Typography, etc)
foreach ( $breakpoints as $device ) {
	// Text Color
	$setting_name = empty( $device ) ? 'text_color' : "text_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content ' . $group_selector . ' .fl-button:is(a, button) > span',
			'.fl-builder-content ' . $group_selector . ' .fl-button:is(a, button) > i',
		),
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );

	// Text Hover Color
	$setting_name = empty( $device ) ? 'text_hover_color' : "text_hover_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content ' . $group_selector . ' .fl-button:is(a, button):hover > span',
			'.fl-builder-content ' . $group_selector . ' .fl-button:is(a, button):focus > span',
			'.fl-builder-content ' . $group_selector . ' .fl-button:is(a, button):hover > i',
			'.fl-builder-content ' . $group_selector . ' .fl-button:is(a, button):focus > i',
		),
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );
}
?>

<?php
// Typography
FLBuilderCSS::typography_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'typography',
	'selector'     => ".fl-builder-content $group_selector .fl-button:is(a, button), .fl-builder-content $group_selector a.fl-button:visited",
) );

// Button Padding
FLBuilderCSS::dimension_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'button_spacing',
	'selector'     => ".fl-builder-content $buttons_selector .fl-button-group-button .fl-button:is(a, button)",
	'unit'         => 'px',
	'props'        => array(
		'padding-top'    => 'button_padding_top',
		'padding-right'  => 'button_padding_right',
		'padding-bottom' => 'button_padding_bottom',
		'padding-left'   => 'button_padding_left',
	),
) );

// Container Padding
FLBuilderCSS::dimension_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'padding',
	'selector'     => ".fl-builder-content $buttons_selector",
	'unit'         => 'px',
	'props'        => array(
		'padding-top'    => 'padding_top',
		'padding-right'  => 'padding_right',
		'padding-bottom' => 'padding_bottom',
		'padding-left'   => 'padding_left',
	),
) );

// Default background hover color
foreach ( $breakpoints as $device ) {
	$bg_color_name       = empty( $device ) ? 'bg_color' : "bg_color_{$device}";
	$bg_hover_color_name = empty( $device ) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	if ( ! empty( $settings->{$bg_color_name} ) && empty( $settings->{$bg_hover_color_name} ) ) {
		$settings->{$bg_hover_color_name} = $settings->{$bg_color_name};
	}
}

// Default background color for gradient styles.
if ( empty( $settings->bg_color ) && 'gradient' === $settings->style ) {
	$settings->bg_color = 'a3a3a3';
}

foreach ( $breakpoints as $device ) {
	// Background Color
	$setting_name = empty( $device ) ? 'bg_color' : "bg_color_{$device}";

	if ( ! empty( $settings->{$setting_name} ) ) {
		FLBuilderCSS::rule( array(
			'media'    => $device,
			'selector' => ".fl-builder-content $buttons_selector .fl-button:is(a, button)",
			'props'    => array(
				'background' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
			),
		) );

		if ( 'gradient' === $settings->style ) {
			$bg_grad_start = FLBuilderColor::adjust_brightness( $settings->{$setting_name}, 30, 'lighten' );
			$bg_grad_end   = FLBuilderColor::hex_or_rgb( $settings->{$setting_name} );

			FLBuilderCSS::rule( array(
				'media'    => $device,
				'selector' => ".fl-builder-content $buttons_selector .fl-button:is(a, button)",
				'props'    => array(
					'background' => 'linear-gradient(to bottom, ' . FLBuilderColor::hex_or_rgb( $bg_grad_start ) . ' 0%, ' . $bg_grad_end . ' 100%)',
				),
			) );
		}

		// Default border: use only when this device has no border rules set.
		$border_setting_name = empty( $device ) ? 'border' : "border_{$device}";

		$use_default_button_group_border = empty( $settings->{$border_setting_name}['style'] )
			&& empty( $settings->{$border_setting_name}['color'] )
			&& empty( $settings->{$border_setting_name}['width']['top'] )
			&& empty( $settings->{$border_setting_name}['width']['bottom'] )
			&& empty( $settings->{$border_setting_name}['width']['left'] )
			&& empty( $settings->{$border_setting_name}['width']['right'] );

		if ( $use_default_button_group_border ) {
			FLBuilderCSS::rule( array(
				'media'    => $device,
				'selector' => ".fl-builder-content $buttons_selector .fl-button:is(a, button)",
				'props'    => array(
					'border' => '1px solid ' . FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $settings->{$setting_name}, 12, 'darken' ) ),
				),
			) );
		}
	}

	// Background Hover Color.
	$setting_name = empty( $device ) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	if ( ! empty( $settings->{$setting_name} ) ) {
		FLBuilderCSS::rule( array(
			'media'    => $device,
			'selector' => array(
				".fl-builder-content $buttons_selector .fl-button:is(a, button):hover",
				".fl-builder-content $buttons_selector .fl-button:is(a, button):focus",
			),
			'props'    => array(
				'background' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
			),
		) );

		if ( 'gradient' === $settings->style ) {
			$bg_hover_grad_start = FLBuilderColor::adjust_brightness( $settings->{$setting_name}, 30, 'lighten' );
			$bg_hover_grad_end   = FLBuilderColor::hex_or_rgb( $settings->{$setting_name} );

			FLBuilderCSS::rule( array(
				'media'    => $device,
				'selector' => array(
					".fl-builder-content $buttons_selector .fl-button:is(a, button):hover",
					".fl-builder-content $buttons_selector .fl-button:is(a, button):focus",
				),
				'props'    => array(
					'background' => 'linear-gradient(to bottom, ' . FLBuilderColor::hex_or_rgb( $bg_hover_grad_start ) . ' 0%, ' . $bg_hover_grad_end . ' 100%)',
				),
			) );
		}
	}
}

// Background Gradient (Advanced).
if ( 'adv-gradient' === $settings->style ) :
	$adv_grad_css_rule = array();
	if ( empty( $settings->bg_gradient['colors'][0] ) && empty( $settings->bg_gradient['colors'][1] ) ) {
		$adv_grad_bg_color       = 'a3a3a3';
		$adv_grad_bg_color_start = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $adv_grad_bg_color, 30, 'lighten' ) );
		$adv_grad_bg_color_end   = FLBuilderColor::hex_or_rgb( $adv_grad_bg_color );
		$adv_grad_border_color   = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $adv_grad_bg_color, 12, 'darken' ) );

		$adv_grad_css_rule['selector'] = "$buttons_selector .fl-button-group-button .fl-button:is(a, button), $buttons_selector .fl-button-group-button .fl-button:is(a, button):hover";
		$adv_grad_css_rule['props']    = array(
			'border'           => "1px solid $adv_grad_border_color",
			'background-image' => "linear-gradient(to bottom, $adv_grad_bg_color_start 0%, $adv_grad_bg_color_end 100%)",
		);
	} else {
		$adv_grad_css_rule['selector'] = "$buttons_selector .fl-button-group-button .fl-button:is(a, button)";
		$adv_grad_css_rule['props']    = array(
			'background-image' => FLBuilderColor::gradient( $settings->bg_gradient ),
		);
	}

	FLBuilderCSS::rule( $adv_grad_css_rule );

endif;

$group_custom_gradient_hover_enable = 'adv-gradient' === $settings->style && ! ( empty( $settings->bg_gradient_hover['colors'][0] ) && empty( $settings->bg_gradient_hover['colors'][1] ) );
FLBuilderCSS::rule( array(
	'selector' => "$buttons_selector .fl-button-group-button .fl-button:is(a, button):hover",
	'enabled'  => $group_custom_gradient_hover_enable,
	'props'    => array(
		'background-image' => FLBuilderColor::gradient( $settings->bg_gradient_hover ),
	),
) );

if ( 'adv-gradient' !== $settings->style ) {
	$temp_border_color = empty( $settings->border['color'] ) ? '' : $settings->border['color'];
	if ( empty( $temp_border_color ) ) {
		$temp_border_color = empty( $settings->bg_color ) ? 'a3a3a3' : $settings->bg_color;
	}
	if ( ! empty( $settings->border['color'] ) ) {
		$settings->border['color'] = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $temp_border_color, 12, 'darken' ) );
	}
} else {
	$temp_border_color = empty( $settings->border['color'] ) ? 'a3a3a3' : $settings->border['color'];
	if ( ! empty( $settings->border['color'] ) ) {
		$settings->border['color'] = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $temp_border_color, 12, 'darken' ) );
	}
}
// Border - Settings
FLBuilderCSS::border_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'border',
	'selector'     => ".fl-builder-content $buttons_selector .fl-button:is(a, button)",
) );

foreach ( $breakpoints as $device ) {
	$hover_color_name = empty( $device ) ? 'border_hover_color' : "border_hover_color_{$device}";
	$bg_name          = empty( $device ) ? 'bg_color' : "bg_color_{$device}";
	$border_key       = empty( $device ) ? 'border' : "border_{$device}";

	if ( ! isset( $settings->{$border_key} ) ) {
		continue;
	}

	if ( 'adv-gradient' !== $settings->style ) {
		$temp_border_hover_color = isset( $settings->{$hover_color_name} ) && '' !== $settings->{$hover_color_name} ? $settings->{$hover_color_name} : '';
		if ( empty( $temp_border_hover_color ) ) {
			$temp_border_hover_color = ( isset( $settings->{$bg_name} ) && '' !== $settings->{$bg_name} ) ? $settings->{$bg_name} : 'a3a3a3';
		}
		$border_for_device = $settings->{$border_key};
		$has_color         = is_object( $border_for_device ) ? ! empty( $border_for_device->color ) : ! empty( $border_for_device['color'] );
		if ( $has_color ) {
			$adjusted = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $temp_border_hover_color, 12, 'darken' ) );
			if ( is_object( $settings->{$border_key} ) ) {
				$settings->{$border_key}->color = $adjusted;
			} else {
				$settings->{$border_key}['color'] = $adjusted;
			}
		}
	} else {
		$border_for_device = $settings->{$border_key};
		$has_color         = is_object( $border_for_device ) ? ! empty( $border_for_device->color ) : ! empty( $border_for_device['color'] );
		if ( isset( $settings->{$hover_color_name} ) && '' !== $settings->{$hover_color_name} && $has_color ) {
			$adjusted = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $settings->{$hover_color_name}, 12, 'darken' ) );
			if ( is_object( $settings->{$border_key} ) ) {
				$settings->{$border_key}->color = $adjusted;
			} else {
				$settings->{$border_key}['color'] = $adjusted;
			}
		}
	}
}

FLBuilderCSS::border_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'border',
	'selector'     => ".fl-builder-content $buttons_selector .fl-button:is(a, button):hover",
) );

// Style for the individual button in the group.
for ( $i = 0; $i < count( $settings->items ); $i++ ) :
	$button_group_button_id = "#fl-button-group-button-$id-$i";

	if ( ! is_object( $settings->items[ $i ] ) ) {
		continue;
	}

	// Padding
	FLBuilderCSS::dimension_field_rule( array(
		'settings'     => $settings->items[ $i ],
		'setting_name' => 'padding',
		'selector'     => "$button_group_button_id .fl-button:is(a, button)",
		'unit'         => 'px',
		'props'        => array(
			'padding-top'    => 'padding_top',
			'padding-right'  => 'padding_right',
			'padding-bottom' => 'padding_bottom',
			'padding-left'   => 'padding_left',
		),
	) );

	foreach ( $breakpoints as $device ) {
		// Text Color
		$setting_name = empty( $device ) ? 'button_item_text_color' : "button_item_text_color_{$device}";

		FLBuilderCSS::rule( array(
			'enabled'  => ! empty( $settings->items[ $i ]->{$setting_name} ),
			'media'    => $device,
			'selector' => array(
				$button_group_button_id . ' .fl-button:is(a, button) > span',
				$button_group_button_id . ' .fl-button:is(a, button) > i',
			),
			'props'    => array(
				'color' => FLBuilderColor::hex_or_rgb( $settings->items[ $i ]->{$setting_name} ),
			),
		) );

		// Text Hover Color
		$setting_name = empty( $device ) ? 'button_item_text_hover_color' : "button_item_text_hover_color_{$device}";

		FLBuilderCSS::rule( array(
			'enabled'  => ! empty( $settings->items[ $i ]->{$setting_name} ),
			'media'    => $device,
			'selector' => array(
				$button_group_button_id . ' .fl-button:is(a, button):hover > span',
				$button_group_button_id . ' .fl-button:is(a, button):focus > span',
				$button_group_button_id . ' .fl-button:is(a, button):hover > i',
				$button_group_button_id . ' .fl-button:is(a, button):focus > i',
			),
			'props'    => array(
				'color' => FLBuilderColor::hex_or_rgb( $settings->items[ $i ]->{$setting_name} ),
			),
		) );
	}

	// Typography
	FLBuilderCSS::typography_field_rule( array(
		'settings'     => $settings->items[ $i ],
		'setting_name' => 'button_item_typography',
		'selector'     => "$button_group_button_id .fl-button:is(a, button), $button_group_button_id a.fl-button:visited",
	) );

	$bi_border                      = $settings->items[ $i ]->button_item_border;
	$use_default_button_item_border = empty( $bi_border->style )
				&& empty( $bi_border->color )
				&& empty( $bi_border->width->top )
				&& empty( $bi_border->width->bottom )
				&& empty( $bi_border->width->left )
				&& empty( $bi_border->width->right );

	foreach ( $breakpoints as $device ) {
		$bg_color_name       = empty( $device ) ? 'button_item_bg_color' : "button_item_bg_color_{$device}";
		$bg_hover_color_name = empty( $device ) ? 'button_item_bg_hover_color' : "button_item_bg_hover_color_{$device}";

		FLBuilderCSS::rule( array(
			'enabled'  => $use_default_button_item_border && ! empty( $settings->items[ $i ]->{$bg_color_name} ),
			'media'    => $device,
			'selector' => "$button_group_button_id .fl-button:is(a, button)",
			'props'    => array(
				'border' => '1px solid ' . FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $settings->items[ $i ]->{$bg_color_name}, 12, 'darken' ) ),
			),
		) );

		FLBuilderCSS::rule( array(
			'enabled'  => ! empty( $settings->items[ $i ]->{$bg_color_name} ),
			'media'    => $device,
			'selector' => "$button_group_button_id .fl-button:is(a, button)",
			'props'    => array(
				'background' => FLBuilderColor::hex_or_rgb( $settings->items[ $i ]->{$bg_color_name} ),
			),
		) );

		if ( ! empty( $settings->items[ $i ]->button_item_style ) && 'gradient' === $settings->items[ $i ]->button_item_style ) {
			if ( empty( $settings->items[ $i ]->{$bg_color_name} ) ) {
				$settings->items[ $i ]->{$bg_color_name} = 'a3a3a3';
			}

			$button_item_bg_grad_start = FLBuilderColor::adjust_brightness( $settings->items[ $i ]->{$bg_color_name}, 30, 'lighten' );

			FLBuilderCSS::rule( array(
				'enabled'  => ! empty( $settings->items[ $i ]->{$bg_color_name} ),
				'media'    => $device,
				'selector' => "$button_group_button_id .fl-button:is(a, button)",
				'props'    => array(
					'background' => 'linear-gradient(to bottom, ' . FLBuilderColor::hex_or_rgb( $button_item_bg_grad_start ) . ' 0%, ' . FLBuilderColor::hex_or_rgb( $settings->items[ $i ]->{$bg_color_name} ) . ' 100%)',
				),
			) );
		}

		FLBuilderCSS::rule( array(
			'enabled'  => ! empty( $settings->items[ $i ]->{$bg_hover_color_name} ),
			'media'    => $device,
			'selector' => array(
				"$button_group_button_id .fl-button:is(a, button):hover",
				"$button_group_button_id .fl-button:is(a, button):focus",
			),
			'props'    => array(
				'background' => FLBuilderColor::hex_or_rgb( $settings->items[ $i ]->{$bg_hover_color_name} ),
			),
		) );

		if ( ! empty( $settings->items[ $i ]->{$bg_hover_color_name} ) ) {
			$button_item_bg_hover_grad_start = FLBuilderColor::adjust_brightness( $settings->items[ $i ]->{$bg_hover_color_name}, 30, 'lighten' );

			FLBuilderCSS::rule( array(
				'enabled'  => ! empty( $settings->items[ $i ]->button_item_style ) && 'gradient' === $settings->items[ $i ]->button_item_style,
				'media'    => $device,
				'selector' => array(
					"$button_group_button_id .fl-button:is(a, button):hover",
					"$button_group_button_id .fl-button:is(a, button):focus",
				),
				'props'    => array(
					'background' => 'linear-gradient(to bottom, ' . FLBuilderColor::hex_or_rgb( $button_item_bg_hover_grad_start ) . ' 0%, ' . FLBuilderColor::hex_or_rgb( $settings->items[ $i ]->{$bg_hover_color_name} ) . ' 100%)',
				),
			) );
		}
	}

	if ( 'adv-gradient' === $settings->items[ $i ]->button_item_style ) :
		// Background Gradient
		$button_item_gradient = json_decode( json_encode( $settings->items[ $i ]->button_item_bg_gradient ), true );
		$adv_grad_css_rule    = array();
		if ( empty( $button_item_gradient['colors'][0] ) && empty( $button_item_gradient['colors'][1] ) ) {
			$adv_grad_bg_color       = 'a3a3a3';
			$adv_grad_bg_color_start = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $adv_grad_bg_color, 30, 'lighten' ) );
			$adv_grad_bg_color_end   = FLBuilderColor::hex_or_rgb( $adv_grad_bg_color );
			$adv_grad_border_color   = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $adv_grad_bg_color, 12, 'darken' ) );

			$adv_grad_css_rule['selector'] = "$button_group_button_id .fl-button:is(a, button), $button_group_button_id .fl-button:is(a, button):hover";
			$adv_grad_css_rule['props']    = array(
				'border'           => "1px solid $adv_grad_border_color",
				'background-image' => "linear-gradient(to bottom, $adv_grad_bg_color_start 0%, $adv_grad_bg_color_end 100%)",
			);
		} else {
			$adv_grad_css_rule['selector'] = "$button_group_button_id .fl-button:is(a, button)";
			$adv_grad_css_rule['props']    = array(
				'background-image' => FLBuilderColor::gradient( $button_item_gradient ),
			);
		}

		FLBuilderCSS::rule( $adv_grad_css_rule );

		// Background Hover Gradient
		$button_item_gradient_hover = json_decode( json_encode( $settings->items[ $i ]->button_item_bg_gradient_hover ), true );
		if ( ! ( empty( $button_item_gradient_hover['colors'][0] ) && empty( $button_item_gradient_hover['colors'][1] ) ) ) :
			FLBuilderCSS::rule( array(
				'selector' => "$button_group_button_id .fl-button:is(a, button):hover",
				'props'    => array(
					'background-image' => FLBuilderColor::gradient( $button_item_gradient_hover ),
				),
			) );
		endif;
	endif;

	foreach ( $breakpoints as $device ) {
		// Transition
		$setting_name = empty( $device ) ? 'button_item_button_transition' : "button_item_button_transition_{$device}";
		$transition   = 'enable' === $settings->items[ $i ]->{$setting_name} ? 'all 0.2s linear' : 'none';

		FLBuilderCSS::rule( array(
			'enabled'  => 'flat' === $settings->style && ! empty( $settings->items[ $i ]->{$setting_name} ),
			'media'    => $device,
			'selector' => array(
				"$button_group_button_id .fl-button",
				"$button_group_button_id .fl-button *",
			),
			'props'    => array(
				'transition'         => $transition,
				'-moz-transition'    => $transition,
				'-webkit-transition' => $transition,
				'-o-transition'      => $transition,
			),
		) );
	}

	if ( ( 'html' == $settings->items[ $i ]->lightbox_content_type ) && ! empty( $settings->items[ $i ]->lightbox_content_html ) ) :
		$button_node_id = "fl-node-$id-$i";
		?>

		.<?php echo "$button_node_id.fl-button-lightbox-content"; ?> {
			background: #fff none repeat scroll 0 0;
			margin: 20px auto;
			max-width: 600px;
			padding: 20px;
			position: relative;
			width: auto;
		}

		.<?php echo "$button_node_id.fl-button-lightbox-content"; ?> .mfp-close,
		.<?php echo "$button_node_id.fl-button-lightbox-content"; ?> .mfp-close:hover {
			top: -10px!important;
			right: -10px;
		}

		.mfp-wrap .<?php echo "$button_node_id.fl-button-lightbox-content"; ?> .mfp-close,
		.mfp-wrap .<?php echo "$button_node_id.fl-button-lightbox-content"; ?> .mfp-close:hover {
			color:#333!important;
			right: -4px;
			top: -10px!important;
		}
		<?php
	endif;

	// Click action - lightbox
	if ( isset( $settings->items[ $i ]->click_action ) && 'lightbox' == $settings->items[ $i ]->click_action ) :
		if ( 'video' == $settings->items[ $i ]->lightbox_content_type ) :
			?>
			.fl-button-lightbox-wrap .mfp-content {
				background: #fff;
			}
			.fl-button-lightbox-wrap .mfp-iframe-scaler iframe {
				left: 2%;
				height: 94%;
				top: 3%;
				width: 96%;
			}
			.mfp-wrap.fl-button-lightbox-wrap .mfp-close,
			.mfp-wrap.fl-button-lightbox-wrap .mfp-close:hover {
				color: #333!important;
				right: -4px;
				top: -10px!important;
			}
			<?php
		endif;
	endif;

	// Border
	if ( ! empty( $settings->items[ $i ]->button_item_border->style ) ) {
		if ( empty( $settings->items[ $i ]->button_item_border->width->top ) ) {
			$settings->items[ $i ]->button_item_border->width->top = $settings->border['width']['top'] ?? null;
		}
		if ( empty( $settings->items[ $i ]->button_item_border->width->bottom ) ) {
			$settings->items[ $i ]->button_item_border->width->bottom = $settings->border['width']['bottom'] ?? null;
		}
		if ( empty( $settings->items[ $i ]->button_item_border->width->left ) ) {
			$settings->items[ $i ]->button_item_border->width->left = $settings->border['width']['left'] ?? null;
		}
		if ( empty( $settings->items[ $i ]->button_item_border->width->right ) ) {
			$settings->items[ $i ]->button_item_border->width->right = $settings->border['width']['right'] ?? null;
		}
		FLBuilderCSS::border_field_rule( array(
			'settings'     => $settings->items[ $i ],
			'setting_name' => 'button_item_border',
			'selector'     => "$button_group_button_id .fl-button:is(a, button)",
		) );
	}

	// Border Hover
	FLBuilderCSS::responsive_rule( array(
		'enabled'      => ! empty( $settings->items[ $i ]->button_item_border_hover_color ),
		'settings'     => $settings->items[ $i ],
		'setting_name' => 'button_item_border_hover_color', // As in $settings->align.
		'selector'     => "$button_group_button_id .fl-button:is(a, button):hover",
		'prop'         => 'border-color',
	) );

endfor;

foreach ( $breakpoints as $device ) {
	// Transition
	$setting_name = empty( $device ) ? 'button_transition' : "button_transition_{$device}";
	$transition   = 'enable' === $settings->{$setting_name} ? 'all 0.2s linear' : 'none';

	FLBuilderCSS::rule( array(
		'enabled'  => 'flat' === $settings->style,
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content .fl-node-' . $id . ' .fl-button',
			'.fl-builder-content .fl-node-' . $id . ' .fl-button *',
		),
		'props'    => array(
			'transition'         => $transition,
			'-moz-transition'    => $transition,
			'-webkit-transition' => $transition,
			'-o-transition'      => $transition,
		),
	) );
}
