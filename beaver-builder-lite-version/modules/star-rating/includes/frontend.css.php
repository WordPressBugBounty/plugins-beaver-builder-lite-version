<?php

FLBuilderCSS::rule( array(
	'selector' => '.fl-node-' . $id,
	'props'    => array(
		'--font'   => $settings->font,
		'--offset' => 'auto 0' === $settings->align ? 'calc(-1 * var(--space))' : 'auto',
	),
) );

foreach ( array( '', '_large', '_medium', '_responsive' ) as $breakpoint ) {
	FLBuilderCSS::rule( array(
		'enabled'  => array_filter( array(
			$settings->{'fill' . $breakpoint},
			$settings->{'background' . $breakpoint},
			$settings->{'border' . $breakpoint},
			$settings->{'stroke' . $breakpoint},
		) ),
		'selector' => '.fl-node-' . $id,
		'media'    => str_replace( '_', '', $breakpoint ),
		'props'    => array(
			'--fill'       => FLBuilderColor::hex_or_rgb( $settings->{'fill' . $breakpoint} ),
			'--background' => FLBuilderColor::hex_or_rgb( $settings->{'background' . $breakpoint} ),
			'--border'     => FLBuilderColor::hex_or_rgb( $settings->{'border' . $breakpoint} ),
			'--stroke'     => $settings->{'stroke' . $breakpoint} . 'px',
		),
	) );
	FLBuilderCSS::rule( array(
		'enabled'      => ! empty( $settings->{'align' . $breakpoint} ),
		'selector'     => '.fl-node-' . $id . '::after',
		'setting_name' => 'align' . $breakpoint,
		'media'        => str_replace( '_', '', $breakpoint ),
		'props'        => array( 'margin-inline' => $settings->{'align' . $breakpoint} ),
	) );
	FLBuilderCSS::rule( array(
		'enabled'      => ! empty( $settings->{'size' . $breakpoint} ),
		'selector'     => '.fl-node-' . $id,
		'setting_name' => 'size' . $breakpoint,
		'media'        => str_replace( '_', '', $breakpoint ),
		'props'        => array( 'font-size' => $settings->{'size' . $breakpoint} . 'px' ),
	) );
	FLBuilderCSS::rule( array(
		'enabled'      => ! empty( $settings->{'space' . $breakpoint} ),
		'selector'     => '.fl-node-' . $id,
		'setting_name' => 'space' . $breakpoint,
		'media'        => str_replace( '_', '', $breakpoint ),
		'props'        => array( '--space' => $settings->{'space' . $breakpoint} . 'px' ),
	) );
	FLBuilderCSS::rule( array(
		'enabled'      => array_filter( [ $settings->{'size' . $breakpoint}, $settings->{'space' . $breakpoint} ] ),
		'selector'     => '.fl-node-' . $id,
		'setting_name' => 'step' . $breakpoint,
		'media'        => str_replace( '_', '', $breakpoint ),
		'props'        => array( '--step' => $module->calculate_step( $breakpoint ) . 'px' ),
	) );
}
