<?php

/**
 * @class FLStarRatingModule
 */

class FLStarRatingModule extends FLBuilderModule {

	/**
	 * @method constructor for setting module configuration
	 */

	public function __construct() {
		parent::__construct(array(
			'name'            => __( 'Star Rating', 'fl-builder' ),
			'description'     => __( 'Display a star rating.', 'fl-builder' ),
			'category'        => true === FL_BUILDER_LITE ? __( 'Basic', 'fl-builder' ) : __( 'Media', 'fl-builder' ),
			'icon'            => 'star-half.svg',
			'partial_refresh' => true,
			'include_wrapper' => false,
		));
	}

	/**
	 * @since 2.10
	 * @method calculate_step for adjusting rating with star spaces
	 * @param string|null $breakpoint the responsive breakpoint
	 * @return float
	 */

	public function calculate_step( $breakpoint = null ) {
		$number   = floor( $this->settings->rating );
		$rating   = floatval( $this->settings->rating );
		$space    = floatval( empty( $this->settings->{'space' . $breakpoint} ) ? $this->settings->space : $this->settings->{'space' . $breakpoint} );
		$size     = floatval( empty( $this->settings->{'size' . $breakpoint} ) ? $this->settings->size : $this->settings->{'size' . $breakpoint} ) * floatval( $this->settings->ratio );
		$unit     = $number * ( $space + $size );
		$fraction = ( $rating - $number ) * $size;
		return $unit + $fraction;
	}
}

// Creates the form settings of the module
$settings = array(
	'general' => array(
		'title'    => __( 'General', 'fl-builder' ),
		'sections' => array(
			'section' => array(
				'title'  => '',
				'fields' => array(
					'total'   => array(
						'type'        => 'unit',
						'label'       => __( 'Total Stars', 'fl-builder' ),
						'slider'      => array(
							'min'  => '1',
							'max'  => '10',
							'step' => '1',
						),
						'default'     => 5,
						'preview'     => array( 'type' => 'none' ),
						'connections' => array( 'custom_field' ),
					),
					'rating'  => array(
						'type'        => 'unit',
						'label'       => __( 'Star Rating', 'fl-builder' ),
						'slider'      => array(
							'min'  => '0',
							'max'  => '5',
							'step' => '0.1',
						),
						'default'     => 0,
						'preview'     => array( 'type' => 'none' ),
						'connections' => array( 'custom_field' ),
					),
					'icon'    => array(
						'type'        => 'icon',
						'label'       => __( 'Star Icon', 'fl-builder' ),
						'show_remove' => true,
						'preview'     => array( 'type' => 'none' ),
					),
					'unicode' => array(
						'type'    => 'hidden',
						'default' => '★',
					),
					'font'    => array(
						'type'    => 'hidden',
						'default' => 'times',
					),
					'ratio'   => array(
						'type'    => 'hidden',
						'default' => '1',
					),
				),
			),
		),
	),
	'style'   => array(
		'title'    => __( 'Style', 'fl-builder' ),
		'sections' => array(
			'colors' => array(
				'title'  => 'Colors',
				'fields' => array(
					'fill'       => array(
						'type'        => 'color',
						'label'       => __( 'Star Fill', 'fl-builder' ),
						'default'     => '#fd0',
						'responsive'  => true,
						'connections' => array( 'color' ),
						'preview'     => array(
							'type'     => 'css',
							'property' => '--fill',
						),
					),
					'background' => array(
						'type'        => 'color',
						'label'       => __( 'Star Background', 'fl-builder' ),
						'default'     => '#fff',
						'responsive'  => true,
						'connections' => array( 'color' ),
						'preview'     => array(
							'type'     => 'css',
							'property' => '--background',
						),
					),
					'border'     => array(
						'type'        => 'color',
						'label'       => __( 'Star Border', 'fl-builder' ),
						'default'     => '#000',
						'responsive'  => true,
						'connections' => array( 'color' ),
						'preview'     => array(
							'type'     => 'css',
							'property' => '--border',
						),
					),
				),
			),
			'layout' => array(
				'title'  => 'Layout',
				'fields' => array(
					'align'  => array(
						'type'       => 'align',
						'label'      => __( 'Star Alignment', 'fl-builder' ),
						'default'    => 'auto',
						'responsive' => true,
						'values'     => array(
							'left'   => '0 auto',
							'center' => 'auto',
							'right'  => 'auto 0',
						),
						'preview'    => array(
							'type'     => 'css',
							'selector' => '{node}::after',
							'property' => 'margin-inline',
						),
					),
					'size'   => array(
						'type'        => 'unit',
						'label'       => __( 'Star Size', 'fl-builder' ),
						'responsive'  => true,
						'slider'      => true,
						'description' => 'px',
						'default'     => 30,
						'preview'     => array(
							'type'     => 'css',
							'property' => 'font-size',
							'unit'     => 'px',
						),
					),
					'space'  => array(
						'type'        => 'unit',
						'label'       => __( 'Star Spacing', 'fl-builder' ),
						'responsive'  => true,
						'slider'      => true,
						'description' => 'px',
						'default'     => 5,
						'preview'     => array(
							'type'     => 'css',
							'property' => '--space',
							'unit'     => 'px',
						),
					),
					'stroke' => array(
						'type'        => 'unit',
						'label'       => __( 'Star Stroke', 'fl-builder' ),
						'slider'      => true,
						'responsive'  => true,
						'description' => 'px',
						'default'     => 1,
						'preview'     => array(
							'type'     => 'css',
							'property' => '--stroke',
							'unit'     => 'px',
						),
					),
				),
			),
		),
	),
);

// Registers the module to Beaver Builder
FLBuilder::register_module( 'FLStarRatingModule', $settings );
