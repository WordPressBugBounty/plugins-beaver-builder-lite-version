<?php

$breakpoints = array( '', 'large', 'medium', 'responsive' );

// Custom Width
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'custom_width',
	'enabled'      => 'custom' === $settings->width,
	'selector'     => ".fl-node-$id .fl-button:is(a, button)",
	'prop'         => 'width',
) );

// Alignment
FLBuilderCSS::responsive_rule( array(
	'settings'     => $settings,
	'setting_name' => 'align',
	'selector'     => ".fl-node-$id.fl-button-wrap, .fl-node-$id .fl-button-wrap", // Both rules needed for compat with v1
	'prop'         => 'text-align',
) );

// Padding
FLBuilderCSS::dimension_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'padding',
	'selector'     => ".fl-builder-content .fl-node-$id .fl-button:is(a, button)",
	'unit'         => 'px',
	'props'        => array(
		'padding-top'    => 'padding_top',
		'padding-right'  => 'padding_right',
		'padding-bottom' => 'padding_bottom',
		'padding-left'   => 'padding_left',
	),
) );

// Typography
FLBuilderCSS::typography_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'typography',
	'selector'     => ".fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-builder-content .fl-node-$id a.fl-button:visited, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-page .fl-builder-content .fl-node-$id a.fl-button:visited",
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

// Background Gradient
if ( ! empty( $settings->bg_color ) ) {
	$bg_grad_start = FLBuilderColor::adjust_brightness( $settings->bg_color, 30, 'lighten' );
}

// Set Default BG Color for Gradient
$bg_gradient_color             = '';
$bg_gradient_hover_color       = '';
$bg_gradient_color_start       = '';
$bg_gradient_hover_color_start = '';
if ( 'gradient' === $settings->style ) {
	$bg_gradient_color             = empty( $settings->bg_color ) ? 'a3a3a3' : $settings->bg_color;
	$bg_gradient_hover_color       = empty( $settings->bg_hover_color ) ? $bg_gradient_color : $settings->bg_hover_color;
	$bg_gradient_color_start       = FLBuilderColor::adjust_brightness( $bg_gradient_color, 30, 'lighten' );
	$bg_gradient_hover_color_start = FLBuilderColor::adjust_brightness( $bg_gradient_hover_color, 30, 'lighten' );
} elseif ( 'adv-gradient' === $settings->style ) {
	$bg_gradient_color             = 'a3a3a3';
	$bg_gradient_hover_color       = 'a3a3a3';
	$bg_gradient_color_start       = FLBuilderColor::adjust_brightness( $bg_gradient_color, 30, 'lighten' );
	$bg_gradient_hover_color_start = FLBuilderColor::adjust_brightness( $bg_gradient_hover_color, 30, 'lighten' );
}

foreach ( $breakpoints as $device ) {
	// Border - Default
	$setting_name = empty( $device ) ? 'bg_color' : "bg_color_{$device}";

	if ( ! empty( $settings->{$setting_name} ) ) {
		FLBuilderCSS::rule( array(
			'enabled'  => $module->use_default_border() && 'adv-gradient' !== $settings->style,
			'media'    => $device,
			'selector' => array(
				".fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-builder-content .fl-node-$id a.fl-button:visited",
				".fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-page .fl-builder-content .fl-node-$id a.fl-button:visited",
			),
			'props'    => array(
				'border' => '1px solid ' . FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $settings->{$setting_name}, 12, 'darken' ) ),
			),
		) );
	}

	// Border - Hover Default
	$setting_name = empty( $device ) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	if ( ! empty( $settings->{$setting_name} ) ) {
		FLBuilderCSS::rule( array(
			'enabled'  => $module->use_default_border_hover() && 'adv-gradient' !== $settings->style,
			'media'    => $device,
			'selector' => array(
				".fl-builder-content .fl-node-$id .fl-button:is(a, button):hover, .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus",
				".fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus",
			),
			'props'    => array(
				'border' => '1px solid ' . FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( $settings->{$setting_name}, 12, 'darken' ) ),
			),
		) );
	}
}

$border_color_backup = '';
if ( 'adv-gradient' === $settings->style ) {
	if ( empty( $settings->border['color'] ) ) {
		$settings->border['color'] = FLBuilderColor::hex_or_rgb( FLBuilderColor::adjust_brightness( 'a3a3a3', 12, 'darken' ) );
	} else {
		$border_color_backup = $settings->border['color'];
	}
}

// Border - Settings
FLBuilderCSS::border_field_rule( array(
	'settings'     => $settings,
	'setting_name' => 'border',
	'selector'     => ".fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-builder-content .fl-node-$id a.fl-button:visited, .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover, .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-page .fl-builder-content .fl-node-$id a.fl-button:visited, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus",
) );

// theme based borders
FLBuilderCSS::rule( array(
	'enabled'  => ! empty( FLBuilderUtils::get_bb_theme_option( 'fl-button-border-hover-color' ) ) && ! empty( FLBuilderUtils::get_bb_theme_option( 'fl-button-style' ) ),
	'selector' => ".fl-builder-content .fl-module-button.fl-node-$id .fl-button:is(a, button):hover, .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus, .fl-page .fl-builder-content .fl-module-button.fl-node-$id .fl-button:is(a, button):hover, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus",
	'props'    => array(
		'border-color' => FLBuilderColor::hex_or_rgb( FLBuilderUtils::get_bb_theme_option( 'fl-button-border-hover-color' ) ),
	),
) );

if ( 'adv-gradient' === $settings->style ) {
	$settings->border['color'] = $border_color_backup;
}

foreach ( $breakpoints as $device ) {
	// Border - Hover Color
	$setting_name = empty( $device ) ? 'border_hover_color' : "border_hover_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			".fl-builder-content .fl-module-button.fl-node-$id .fl-button:is(a, button):hover",
			".fl-builder-content .fl-node-$id .fl-button:is(a, button):focus",
			".fl-page .fl-builder-content .fl-module-button.fl-node-$id .fl-button:is(a, button):hover",
			".fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):focus",
		),
		'props'    => array(
			'border-color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );

	// Background Color
	$setting_name = empty( $device ) ? 'bg_color' : "bg_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => 'flat' === $settings->style && ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			".fl-builder-content .fl-node-$id .fl-button:is(a, button)",
			".fl-builder-content .fl-node-$id a.fl-button:visited",
			".fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button)",
			".fl-page .fl-builder-content .fl-node-$id a.fl-button:visited",
		),
		'props'    => array(
			'background-color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );

	// Background Color - Hover
	$setting_name = empty( $device ) ? 'bg_hover_color' : "bg_hover_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => 'flat' === $settings->style && ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			".fl-builder-content .fl-node-$id .fl-button:is(a, button):hover",
			".fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover",
			".fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover",
			".fl-page .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover",
		),
		'props'    => array(
			'background-color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );
}
?>

<?php if ( 'gradient' === $settings->style ) : ?>
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button),
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):hover,
.fl-builder-content .fl-node-<?php echo $id; ?> a.fl-button:visited,
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button),
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):hover,
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> a.fl-button:visited {
	background: linear-gradient(to bottom,  <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_color_start ); ?> 0%, <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_color ); ?> 100%);
}
<?php elseif ( 'adv-gradient' === $settings->style ) : ?>
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button),
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):hover,
.fl-builder-content .fl-node-<?php echo $id; ?> a.fl-button:visited,
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button),
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):hover,
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> a.fl-button:visited {
	background-image: linear-gradient(to bottom,  <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_color_start ); ?> 0%, <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_color ); ?> 100%);
}
<?php endif; ?>

<?php
foreach ( $breakpoints as $device ) {
	$setting_name = empty( $device ) ? 'text_color' : "text_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button)',
			'.fl-builder-content .fl-node-' . $id . ' a.fl-button:visited',
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button) *',
			'.fl-builder-content .fl-node-' . $id . ' a.fl-button:visited *',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button)',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' a.fl-button:visited',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button) *',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' a.fl-button:visited *',
		),
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );
}
?>

<?php if ( $settings->duo_color1 && false !== strpos( $settings->icon, 'fad fa' ) ) : ?>
.fl-node-<?php echo $id; ?> .fl-button-icon:before {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->duo_color1 ); ?>;
}
<?php endif; ?>

<?php if ( $settings->duo_color2 && false !== strpos( $settings->icon, 'fad fa' ) ) : ?>
.fl-node-<?php echo $id; ?> .fl-button-icon:after {
	color: <?php echo FLBuilderColor::hex_or_rgb( $settings->duo_color2 ); ?>;
	opacity: 1;
}
<?php endif; ?>


<?php if ( 'gradient' === $settings->style ) : ?>
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):hover,
.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):focus,
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):hover,
.fl-page .fl-builder-content .fl-node-<?php echo $id; ?> .fl-button:is(a, button):focus {

	background: <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_hover_color ); ?>;

	<?php if ( 'gradient' == $settings->style ) : // Gradient ?>
	background: linear-gradient(to bottom,  <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_hover_color_start ); ?> 0%, <?php echo FLBuilderColor::hex_or_rgb( $bg_gradient_hover_color ); ?> 100%);
	<?php endif; ?>
}
<?php endif; ?>

<?php
foreach ( $breakpoints as $device ) {
	$setting_name = empty( $device ) ? 'text_hover_color' : "text_hover_color_{$device}";

	FLBuilderCSS::rule( array(
		'enabled'  => ! empty( $settings->{$setting_name} ),
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button):hover',
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button):hover span.fl-button-text',
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button):hover *',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button):hover',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button):hover span.fl-button-text',
			'.fl-page .fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button):hover *',
		),
		'props'    => array(
			'color' => FLBuilderColor::hex_or_rgb( $settings->{$setting_name} ),
		),
	) );

	// Transition
	$setting_name = empty( $device ) ? 'button_transition' : "button_transition_{$device}";
	$transition   = 'disable' === $settings->{$setting_name} ? 'none' : 'all 0.2s linear';

	FLBuilderCSS::rule( array(
		'enabled'  => 'enable' === $settings->{$setting_name} && 'flat' === $settings->style,
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button)',
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button) *',
		),
		'props'    => array(
			'transition'         => $transition,
			'-moz-transition'    => $transition,
			'-webkit-transition' => $transition,
			'-o-transition'      => $transition,
		),
	) );

	FLBuilderCSS::rule( array(
		'enabled'  => 'disable' === $settings->{$setting_name} && 'flat' === $settings->style,
		'media'    => $device,
		'selector' => array(
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button)',
			'.fl-builder-content .fl-node-' . $id . ' .fl-button:is(a, button) *',
		),
		'props'    => array(
			'transition'         => 'none',
			'-moz-transition'    => 'none',
			'-webkit-transition' => 'none',
			'-o-transition'      => 'none',
		),
	) );
}
?>

<?php if ( empty( $settings->text ) ) : ?>
	<?php if ( 'after' == $settings->icon_position ) : ?>
	.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button i.fl-button-icon-after {
		margin-left: 0;
	}
	<?php endif; ?>
	<?php if ( 'before' == $settings->icon_position ) : ?>
	.fl-builder-content .fl-node-<?php echo $id; ?> .fl-button i.fl-button-icon-before {
		margin-right: 0;
	}
	<?php endif; ?>
<?php endif; ?>

<?php

	$button_node_id = "fl-node-$id";
if ( isset( $settings->id ) && ! empty( $settings->id ) ) {
	$button_node_id = $settings->id;
}

// Background Gradient
FLBuilderCSS::rule( array(
	'selector' => ".fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button), .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover",
	'enabled'  => 'adv-gradient' === $settings->style && FLBuilderColor::gradient( $settings->bg_gradient, true ),
	'props'    => array(
		'background-image' => FLBuilderColor::gradient( $settings->bg_gradient ),
	),
) );

FLBuilderCSS::rule( array(
	'selector' => ".fl-builder-content .fl-node-$id .fl-button:is(a, button):hover, .fl-page .fl-builder-content .fl-node-$id .fl-button:is(a, button):hover",
	'enabled'  => 'adv-gradient' === $settings->style && FLBuilderColor::gradient( $settings->bg_gradient_hover, true ),
	'props'    => array(
		'background-image' => FLBuilderColor::gradient( $settings->bg_gradient_hover ),
	),
) );

// Click action - lightbox
if ( isset( $settings->click_action ) && 'lightbox' == $settings->click_action ) :
	if ( 'html' == $settings->lightbox_content_type ) :
		?>
	.<?php echo $button_node_id; ?>.fl-button-lightbox-content,
	.fl-node-<?php echo $id; ?>.fl-button-lightbox-content {
		background: #fff none repeat scroll 0 0;
		margin: 20px auto;
		max-width: 600px;
		padding: 20px;
		position: relative;
		width: auto;
	}

	.<?php echo $button_node_id; ?>.fl-button-lightbox-content .mfp-close,
	.<?php echo $button_node_id; ?>.fl-button-lightbox-content .mfp-close:hover,
	.fl-node-<?php echo $id; ?>.fl-button-lightbox-content .mfp-close,
	.fl-node-<?php echo $id; ?>.fl-button-lightbox-content .mfp-close:hover {
		top: -10px!important;
		right: -10px;
	}
	<?php endif; ?>

	<?php if ( 'video' == $settings->lightbox_content_type ) : ?>
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
	<?php endif; ?>

<?php endif; ?>
