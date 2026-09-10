<?php

$size_value               = array();
$size_value['']           = empty( $settings->size ) ? 30 : $settings->size;
$size_value['large']      = empty( $settings->size_large ) ? $size_value[''] : $settings->size_large;
$size_value['medium']     = empty( $settings->size_medium ) ? $size_value['large'] : $settings->size_medium;
$size_value['responsive'] = empty( $settings->size_responsive ) ? $size_value['medium'] : $settings->size_responsive;

// Responsive rules.
foreach ( array( '', 'large', 'medium', 'responsive' ) as $device ) {

	$key      = empty( $device ) ? 'size' : "size_{$device}";
	$unit_key = "{$key}_unit";

	$size_unit = $settings->{ $unit_key };

	// Font Size
	FLBuilderCSS::rule( array(
		'media'    => $device,
		'enabled'  => empty( $device ) ? true : ! empty( $settings->{ $key } ),
		'selector' => ".fl-node-$id .fl-icon i, .fl-node-$id .fl-icon i:before",
		'props'    => array(
			'font-size' => $size_value[ $device ] . $size_unit,
		),
	) );

	FLBuilderCSS::rule( array(
		'media'    => $device,
		'selector' => ".fl-node-$id .fl-icon-wrap .fl-icon-text",
		'props'    => array(
			'height' => array(
				'value' => $size_value[ $device ] * 1.75,
				'unit'  => $size_unit,
			),
		),
	) );

	$bg_key       = empty( $device ) ? 'bg_color' : "bg_color_{$device}";
	$bg_hover_key = empty( $device ) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	if ( $settings->{$bg_key} || $settings->{$bg_hover_key} ) {
		FLBuilderCSS::rule( array(
			'media'    => $device,
			'selector' => ".fl-node-$id .fl-icon i",
			'props'    => array(
				'line-height' => array(
					'value' => $size_value[ $device ] * 1.75,
					'unit'  => $size_unit,
				),
				'width'       => array(
					'value' => $size_value[ $device ] * 1.75,
					'unit'  => $size_unit,
				),
			),
		) );
		FLBuilderCSS::rule( array(
			'media'    => $device,
			'selector' => ".fl-node-$id .fl-icon i::before",
			'props'    => array(
				'line-height' => array(
					'value' => $size_value[ $device ] * 1.75,
					'unit'  => $size_unit,
				),
			),
		) );
	}
}

// Overall Alignment
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'align',
	'selector'     => ".fl-node-$id.fl-module-icon",
	'prop'         => 'text-align',
) );

// Text Spacing
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'text_spacing',
	'selector'     => ".fl-node-$id .fl-icon-text",
	'prop'         => 'padding-left',
	'unit'         => 'px',
) );

// Text Color
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'text_color',
	'selector'     => array(
		".fl-builder-content .fl-node-$id .fl-icon-wrap .fl-icon-text",
		".fl-builder-content .fl-node-$id .fl-icon-wrap .fl-icon-text *",
		".fl-builder-content .fl-node-$id .fl-icon-wrap .fl-icon-text-link *",
	),
	'prop'         => 'color',
) );

// Text Typography
FLBuilderCSS::typography_field_rule( array(
	'selector'     => ".fl-node-$id .fl-icon-text, .fl-node-$id .fl-icon-text-link",
	'setting_name' => 'text_typography',
	'settings'     => $settings,
) );

?>
<?php
if ( $settings->color && false === strpos( $settings->icon, 'fad fa' ) ) :
	FLBuilderCSS::responsive_rule( array(
		'settings'     => $settings,
		'setting_name' => 'color',
		'selector'     => array(
			".fl-node-$id .fl-icon i",
			".fl-node-$id .fl-icon i:before",
		),
		'prop'         => 'color',
	) );
endif;
?>

<?php if ( $settings->duo_color1 && false !== strpos( $settings->icon, 'fad fa' ) ) : ?>
.fl-node-<?php echo $id; ?> .fl-icon i,
.fl-node-<?php echo $id; ?> .fl-icon i:before {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->duo_color1 ); ?>;
}
<?php endif; ?>

<?php if ( $settings->duo_color2 && false !== strpos( $settings->icon, 'fad fa' ) ) : ?>
.fl-node-<?php echo $id; ?> .fl-icon i:after {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->duo_color2 ); ?>;
	opacity: 1;
}
<?php endif; ?>

<?php
// Background and border colors
FLBuilderCSS::responsive_rule( array(
	'enabled'      => ! empty( $settings->bg_color ),
	'settings'     => $settings,
	'setting_name' => 'bg_color',
	'selector'     => array(
		".fl-node-$id .fl-icon i",
	),
	'prop'         => 'background',
) );
foreach ( array( '', 'large', 'medium', 'responsive' ) as $device ) {
	$key = empty( $device ) ? 'bg_color' : "bg_color_{$device}";

	if ( ! empty( $settings->{$key} ) ) {
		if ( $settings->three_d ) {
			$bg_grad_start = FLBuilderColor::adjust_brightness( $settings->{$key}, 30, 'lighten' );
			$border_color  = FLBuilderColor::adjust_brightness( $settings->{$key}, 20, 'darken' );

			FLBuilderCSS::rule( array(
				'media'    => $device,
				'selector' => ".fl-node-$id .fl-icon i",
				'props'    => array(
					'background' => 'linear-gradient(to bottom, ' . FLBuilderColor::hex_or_rgb( $bg_grad_start ) . ' 0%, ' . FLBuilderColor::hex_or_rgb( $settings->{$key} ) . ' 100%)',
					'border'     => '1px solid ' . FLBuilderColor::hex_or_rgb( $border_color ),
				),
			) );
		}

		FLBuilderCSS::rule( array(
			'media'    => $device,
			'selector' => ".fl-node-$id .fl-icon i",
			'props'    => array(
				'border-radius' => '100%',
				'text-align'    => 'center',
			),
		) );
	}
}
if ( ! empty( $settings->hover_color ) && false === strpos( $settings->icon, 'fad fa' ) ) :
	FLBuilderCSS::responsive_rule( array(
		'settings'     => $settings,
		'setting_name' => 'hover_color',
		'selector'     => array(
			".fl-node-$id .fl-icon i:hover",
			".fl-node-$id .fl-icon i:hover:before",
			".fl-node-$id .fl-icon a:hover i",
			".fl-node-$id .fl-icon a:hover i:before",
		),
		'prop'         => 'color',
	) );
endif;
?>
<?php
FLBuilderCSS::responsive_rule( array(
	'enabled'      => ! empty( $settings->bg_hover_color ),
	'settings'     => $settings,
	'setting_name' => 'bg_hover_color',
	'selector'     => array(
		".fl-node-$id .fl-icon i:hover",
		".fl-node-$id .fl-icon a:hover i",
	),
	'prop'         => 'background',
) );
foreach ( array( '', 'large', 'medium', 'responsive' ) as $device ) {
	$key = empty( $device ) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	if ( ! empty( $settings->{$key} ) ) {
		if ( $settings->three_d ) {
			$bg_hover_grad_start = FLBuilderColor::adjust_brightness( $settings->{$key}, 30, 'lighten' );
			$border_hover_color  = FLBuilderColor::adjust_brightness( $settings->{$key}, 20, 'darken' );

			FLBuilderCSS::rule( array(
				'media'    => $device,
				'selector' => array(
					".fl-node-$id .fl-icon i:hover",
					".fl-node-$id .fl-icon a:hover i",
				),
				'props'    => array(
					'background' => 'linear-gradient(to bottom, ' . FLBuilderColor::hex_or_rgb( $bg_hover_grad_start ) . ' 0%, ' . FLBuilderColor::hex_or_rgb( $settings->{$key} ) . ' 100%)',
					'border'     => '1px solid ' . FLBuilderColor::hex_or_rgb( $border_hover_color ),
				),
			) );
		}

		FLBuilderCSS::rule( array(
			'media'    => $device,
			'selector' => array(
				".fl-node-$id .fl-icon i:hover",
				".fl-node-$id .fl-icon a:hover i",
			),
			'props'    => array(
				'border-radius' => '100%',
				'text-align'    => 'center',
			),
		) );
	}
}
?>
