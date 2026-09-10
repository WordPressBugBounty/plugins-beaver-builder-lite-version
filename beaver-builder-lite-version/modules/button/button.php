<?php

/**
 * @class FLButtonModule
 */
class FLButtonModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct(array(
			'name'            => __( 'Button', 'fl-builder' ),
			'description'     => __( 'A simple call to action button.', 'fl-builder' ),
			'category'        => __( 'Basic', 'fl-builder' ),
			'icon'            => 'button.svg',
			'partial_refresh' => true,
			'include_wrapper' => false,
			'element_setting' => false,
		));
	}

	/**
	 * Ensure backwards compatibility with old settings.
	 *
	 * @since 2.2
	 * @param object $settings A module settings object.
	 * @param object $helper A settings compatibility helper.
	 * @return object
	 */
	public function filter_settings( $settings, $helper ) {

		// Handle old responsive button align.
		if ( isset( $settings->mobile_align ) ) {
			$settings->align_responsive = $settings->mobile_align;
			unset( $settings->mobile_align );
		}

		if ( ! empty( $settings->style ) && 'gradient' === $settings->style ) {
			if ( ! empty( $settings->bg_gradient ) ) {
				$button_gradient = is_array( $settings->bg_gradient ) ? $settings->bg_gradient : json_decode( json_encode( $settings->bg_gradient ), true );
				if ( ! empty( $button_gradient['colors'][0] ) || ! empty( $button_gradient['colors'][1] ) ) {
					$settings->style = 'adv-gradient';
				}
			}
		}

		// Handle old font size setting.
		if ( isset( $settings->font_size ) ) {
			$settings->typography                = array();
			$settings->typography['font_size']   = array(
				'length' => $settings->font_size,
				'unit'   => isset( $settings->font_size_unit ) ? $settings->font_size_unit : 'px',
			);
			$settings->typography['line_height'] = array(
				'length' => $settings->font_size,
				'unit'   => isset( $settings->font_size_unit ) ? $settings->font_size_unit : 'px',
			);
			unset( $settings->font_size );
			unset( $settings->font_size_unit );
		}

		// Handle old padding setting.
		if ( isset( $settings->padding ) && is_numeric( $settings->padding ) ) {
			$settings->padding_top    = $settings->padding;
			$settings->padding_bottom = $settings->padding;
			$settings->padding_left   = $settings->padding * 2;
			$settings->padding_right  = $settings->padding * 2;
			unset( $settings->padding );
		}

		// Handle old gradient style setting.
		if ( isset( $settings->three_d ) && $settings->three_d ) {
			$settings->style = 'gradient';
		}

		// Handle old border settings.
		if ( ! empty( $settings->bg_color ) && ( ! isset( $settings->border ) || empty( $settings->border ) ) ) {
			$settings->border = array();

			// Border style, color, and width
			if ( isset( $settings->border_size ) && isset( $settings->style ) && 'transparent' === $settings->style ) {
				$settings->border['style'] = 'solid';
				$settings->border['color'] = FLBuilderColor::adjust_brightness( $settings->bg_color, 12, 'darken' );
				$settings->border['width'] = array(
					'top'    => $settings->border_size,
					'right'  => $settings->border_size,
					'bottom' => $settings->border_size,
					'left'   => $settings->border_size,
				);
				unset( $settings->border_size );
				if ( ! empty( $settings->bg_hover_color ) ) {
					$settings->border_hover_color = FLBuilderColor::adjust_brightness( $settings->bg_hover_color, 12, 'darken' );
				}
			}

			// Border radius
			if ( isset( $settings->border_radius ) ) {
				$settings->border['radius'] = array(
					'top_left'     => $settings->border_radius,
					'top_right'    => $settings->border_radius,
					'bottom_left'  => $settings->border_radius,
					'bottom_right' => $settings->border_radius,
				);
				unset( $settings->border_radius );
			}
		}

		// Handle old transparent background style.
		if ( isset( $settings->style ) && 'transparent' === $settings->style ) {
			$settings->style = 'flat';
			$helper->handle_opacity_inputs( $settings, 'bg_opacity', 'bg_color' );
			$helper->handle_opacity_inputs( $settings, 'bg_hover_opacity', 'bg_hover_color' );
		}

		// Return the filtered settings.
		return $settings;
	}

	/**
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts() {
		if ( $this->settings && 'lightbox' == $this->settings->click_action ) {
			$this->add_js( 'jquery-magnificpopup' );
			$this->add_css( 'font-awesome-5' );
			$this->add_css( 'jquery-magnificpopup' );
		}
	}

	/**
	 * @method update
	 */
	public function update( $settings ) {
		// Remove the old three_d setting.
		if ( isset( $settings->three_d ) ) {
			unset( $settings->three_d );
		}

		return $settings;
	}

	/**
	 * Gets the version flag according to the version.
	 *
	 * @since 2.11
	 * @method get_version_flag
	 * @param string $flag The version flag key to check.
	 * @return bool
	 */
	public function get_version_flag( $flag ) {
		$flags  = array( 'unwrapped', 'semantics' );
		$modern = array_fill_keys( $flags, true );
		$legacy = array(
			1 => array_fill_keys( $flags, false ),
			2 => array( 'semantics' => false ),
		);
		$result = array_merge( $modern, $legacy[ $this->version ] ?? array() );
		return $result[ $flag ] ?? false;
	}

	/**
	 * Gets the wrapper element attributes.
	 *
	 * @since 2.11
	 * @method get_wrapper_attributes
	 * @return string
	 */
	public function get_wrapper_attributes() {
		$classes = array( 'fl-button-wrap' );
		if ( ! empty( $this->settings->width ) ) {
			$classes[] = 'fl-button-width-' . $this->settings->width;
		}
		if ( ! empty( $this->settings->align ) ) {
			$classes[] = 'fl-button-' . $this->settings->align;
		}
		if ( ! empty( $this->settings->icon ) ) {
			$classes[] = 'fl-button-has-icon';
		}
		if ( $this->get_version_flag( 'unwrapped' ) ) {
			$attributes          = $this->render_attributes( [], false );
			$attributes['class'] = join( ' ', array_merge( $attributes['class'], $classes ) );
		} else {
			$attributes['class'] = FLBuilderUtils::sanitize_html_class( join( ' ', $classes ) );
		}
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Gets the tag to use for the button based on the click action
	 *
	 * @since 2.10
	 * @method get_button_tag
	 * @return string
	 */
	public function get_button_tag() {
		$modern = $this->get_version_flag( 'semantics' );
		$linked = 'link' === $this->settings->click_action;
		return $modern && ! $linked ? 'button' : 'a';
	}

	/**
	 * Gets the class attribute for the button element.
	 *
	 * @since 2.11
	 * @method get_button_class
	 * @return string
	 */
	public function get_button_class() {
		$classes   = array( 'fl-button' );
		$animation = 'enable' === $this->settings->icon_animation;
		$lightbox  = 'lightbox' === $this->settings->click_action;
		if ( $animation ) {
			$classes[] = 'fl-button-icon-animation';
		}
		if ( $lightbox ) {
			$classes[] = 'fl-button-lightbox';
			$classes[] = ! empty( $this->settings->id ) ? $this->settings->id : 'fl-node-' . $this->node;
		}
		return join( ' ', $classes );
	}

	/**
	 * Gets the button aria label attribute for accessibility.
	 *
	 * @since 2.10
	 * @method get_button_label
	 * @return string
	 */
	public function get_button_label() {
		if ( ! empty( $this->settings->text ) ) {
			return '';
		}
		return $this->settings->label_text ?? '';
	}

	/**
	 * Gets the button element fallback attributes for non-link buttons to ensure accessibility and proper semantics.
	 *
	 * @since 2.11
	 * @method get_button_semantics
	 * @param array $attributes Array of existing attributes to append to.
	 * @return array
	 */
	public function get_button_semantics( $attributes ) {
		$type   = $this->settings->button_type ?? 'button';
		$linked = 'link' === $this->settings->click_action;
		$modern = $this->get_version_flag( 'semantics' );
		if ( ! $linked ) {
			if ( $modern ) {
				$attributes['type'] = $type;
			} else {
				$attributes['role']     = 'button';
				$attributes['tabindex'] = '0';
			}
		}
		return $attributes;
	}

	/**
	 * Gets the button element attributes for the popup action.
	 *
	 * @since 2.11
	 * @method get_button_popup
	 * @param array $attributes Array of existing attributes to append to.
	 * @return array
	 */
	public function get_button_popup( $attributes ) {
		$attributes['commandfor'] = do_shortcode( $this->settings->popup );
		return $attributes;
	}

	/**
	 * Gets the button element attributes for the lightbox action.
	 *
	 * @since 2.11
	 * @method get_button_lightbox
	 * @param array $attributes Array of existing attributes to append to.
	 * @return array
	 */
	public function get_button_lightbox( $attributes ) {
		$video = 'video' === $this->settings->lightbox_content_type;
		$link  = $this->settings->lightbox_video_link;
		if ( $video && ! empty( $link ) ) {
			$attributes['data-mfp-src'] = esc_url( $link );
		}
		$attributes['aria-haspopup'] = 'dialog';
		return $attributes;
	}

	/**
	 * Gets the button element attributes for the copy text action.
	 *
	 * @since 2.11
	 * @method get_button_copy
	 * @param array $attributes Array of existing attributes to append to.
	 * @return array
	 */
	public function get_button_copy( $attributes ) {
		$mapping = array(
			'data-copy-text'            => $this->settings->copy_text,
			'data-copy-success-message' => $this->settings->copy_success_message,
		);
		foreach ( $mapping as $key => $value ) {
			if ( ! empty( $value ) ) {
				$attributes[ $key ] = $value;
			}
		}
		$attributes['aria-live'] = 'polite';
		return $attributes;
	}

	/**
	 * Gets the button action attributes for the button element.
	 *
	 * @since 2.11
	 * @method get_button_actions
	 * @param array $attributes Array of existing attributes to append to.
	 * @return array
	 */
	public function get_button_actions( $attributes ) {
		switch ( $this->settings->click_action ) {
			case 'link':
				return FLBuilderModuleUtils::get_link_attributes( $this->settings, 'link', $attributes, false );
			case 'popup':
				return $this->get_button_popup( $attributes );
			case 'lightbox':
				return $this->get_button_lightbox( $attributes );
			case 'copy_text':
				return $this->get_button_copy( $attributes );
			case 'button':
			default:
				return $attributes;
		}
	}

	/**
	 * Gets all the button element attributes
	 *
	 * @since 2.11
	 * @method get_button_attributes
	 * @return string
	 */
	public function get_button_attributes() {
		$attributes = array(
			'class'      => $this->get_button_class(),
			'aria-label' => $this->get_button_label(),
		);
		$attributes = $this->get_button_semantics( $attributes );
		$attributes = $this->get_button_actions( $attributes );
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Gets the icon attribute for the button element.
	 *
	 * @since 2.11
	 * @method get_icon_attributes
	 * @return string
	 */
	public function get_icon_attributes() {
		$classes    = array(
			'fl-button-icon',
			'fl-button-icon-' . $this->settings->icon_position,
			esc_attr( FLBuilderModuleUtils::get_icon_classes( $this->settings ) ),
		);
		$attributes = array(
			'class'       => join( ' ', $classes ),
			'aria-hidden' => 'true',
		);
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Builds the lightbox html output.
	 *
	 * @since 2.11
	 * @method build_lightbox_output
	 * @return string
	 */
	public function build_lightbox_output() {
		$lightbox = 'lightbox' === $this->settings->click_action;
		$html     = 'html' === $this->settings->lightbox_content_type;
		$content  = $this->settings->lightbox_content_html ?? '';
		if ( $lightbox && $html && $content ) {
			$selector = ! empty( $this->settings->id ) ? esc_attr( $this->settings->id ) : 'fl-node-' . $this->node;
			return sprintf( '<div class="%s fl-button-lightbox-content mfp-hide">%s</div>', $selector, $content );
		}
		return '';
	}

	/**
	 * Builds content output for the button element.
	 *
	 * @since 2.11
	 * @method build_content_output
	 * @return string
	 */
	public function build_content_output() {
		$icon    = ! empty( $this->settings->icon ) ? sprintf( '<i %s></i>', $this->get_icon_attributes() ) : '';
		$text    = ! empty( $this->settings->text ) ? sprintf( '<span class="fl-button-text">%s</span>', $this->settings->text ) : '';
		$content = 'after' === $this->settings->icon_position ? $text . $icon : $icon . $text;
		$notice  = 'a' === $this->get_button_tag() ? FLBuilderModuleUtils::get_link_notice( $this->settings, 'link' ) : '';
		return $content . $notice;
	}

	/**
	 * Builds the button element output.
	 *
	 * @since 2.11
	 * @method build_button_output
	 * @return string
	 */
	public function build_button_output() {
		$tag        = $this->get_button_tag();
		$content    = $this->build_content_output();
		$attributes = $this->get_button_attributes();
		return sprintf( '<%1$s %2$s>%3$s</%1$s>', $tag, $attributes, $content );
	}

	public function use_default_border() {
		if ( ! class_exists( 'FLBuilderGlobalStyles' ) ) {
			return true;
		}
		return empty( ( FLBuilderGlobalStyles::get_settings() )->button_border['style'] ) && empty( FLBuilderUtils::get_bb_theme_option( 'fl-button-border-color' ) );
	}

	public function use_default_border_hover() {
		if ( ! class_exists( 'FLBuilderGlobalStyles' ) ) {
			return true;
		}
		return empty( ( FLBuilderGlobalStyles::get_settings() )->button_border_hover_color ) && empty( FLBuilderUtils::get_bb_theme_option( 'fl-button-border-hover-color' ) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLButtonModule', array(
	'general' => array(
		'title'    => __( 'General', 'fl-builder' ),
		'sections' => array(
			'general'  => array(
				'title'  => '',
				'fields' => array(
					'text'                 => array(
						'type'        => 'text',
						'label'       => __( 'Text', 'fl-builder' ),
						'default'     => __( 'Click Here', 'fl-builder' ),
						'preview'     => array(
							'type'     => 'text',
							'selector' => '.fl-button-text',
						),
						'connections' => array( 'string' ),
					),
					'icon'                 => array(
						'type'               => 'icon',
						'label'              => __( 'Icon', 'fl-builder' ),
						'show_remove'        => true,
						'connections'        => array( 'icon' ),
						'show_extra_classes' => true,
						'show'               => array(
							'fields' => array( 'icon_position', 'icon_animation' ),
						),
						'preview'            => array( 'type' => 'none' ),
					),
					'icon_position'        => array(
						'type'    => 'select',
						'label'   => __( 'Icon Position', 'fl-builder' ),
						'default' => 'before',
						'options' => array(
							'before' => __( 'Before Text', 'fl-builder' ),
							'after'  => __( 'After Text', 'fl-builder' ),
						),
						'preview' => array( 'type' => 'none' ),
					),
					'icon_animation'       => array(
						'type'    => 'select',
						'label'   => __( 'Icon Visibility', 'fl-builder' ),
						'default' => 'disable',
						'options' => array(
							'disable' => __( 'Always Visible', 'fl-builder' ),
							'enable'  => __( 'Fade In On Hover', 'fl-builder' ),
						),
						'preview' => array( 'type' => 'none' ),
					),
					'click_action'         => array(
						'type'    => 'select',
						'label'   => __( 'Click Action', 'fl-builder' ),
						'default' => 'link',
						'options' => array(
							'link'      => __( 'Link', 'fl-builder' ),
							'popup'     => __( 'Popup', 'fl-builder' ),
							'button'    => __( 'Button', 'fl-builder' ),
							'lightbox'  => __( 'Lightbox', 'fl-builder' ),
							'copy_text' => __( 'Copy Text', 'fl-builder' ),
						),
						'toggle'  => array(
							'link'      => array( 'fields' => array( 'link' ) ),
							'popup'     => array( 'fields' => array( 'popup' ) ),
							'button'    => array( 'fields' => array( 'button' ) ),
							'lightbox'  => array( 'sections' => array( 'lightbox' ) ),
							'copy_text' => array( 'fields' => array( 'copy_text', 'copy_success_message' ) ),
						),
						'preview' => array( 'type' => 'none' ),
					),
					'link'                 => array(
						'type'          => 'link',
						'label'         => __( 'Link', 'fl-builder' ),
						'placeholder'   => 'https://www.example.com',
						'show_target'   => true,
						'show_nofollow' => true,
						'show_download' => true,
						'preview'       => array( 'type' => 'none' ),
						'connections'   => array( 'url' ),
					),
					'popup'                => array(
						'type'        => 'text',
						'label'       => __( 'Popup ID', 'fl-builder' ),
						'help'        => __( 'Used to open the popup with Invoker Commands. Be sure the ID is unique and doesn’t contain spaces.', 'fl-builder' ),
						'default'     => '',
						'placeholder' => __( 'e.g. my-popup-id', 'fl-builder' ),
						'preview'     => array( 'type' => 'none' ),
					),
					'button'               => array(
						'type'    => 'code',
						'editor'  => 'javascript',
						'label'   => __( 'Button Code', 'fl-builder' ),
						'rows'    => '18',
						'help'    => __( 'Implement custom button functionality using JavaScript. Your logic will be available to the button\'s click event.', 'fl-builder' ),
						'preview' => array( 'type' => 'none' ),
					),
					'copy_text'            => array(
						'type'        => 'text',
						'label'       => __( 'Text to Copy', 'fl-builder' ),
						'default'     => '',
						'min_version' => 3,
						'preview'     => array( 'type' => 'none' ),
					),
					'copy_success_message' => array(
						'type'        => 'text',
						'label'       => __( 'Copy Success Message', 'fl-builder' ),
						'default'     => __( 'Copied!', 'fl-builder' ),
						'min_version' => 3,
						'preview'     => array( 'type' => 'none' ),
					),
				),
			),
			'lightbox' => array(
				'title'  => __( 'Lightbox Content', 'fl-builder' ),
				'fields' => array(
					'lightbox_content_type' => array(
						'type'    => 'select',
						'label'   => __( 'Content Type', 'fl-builder' ),
						'default' => 'html',
						'options' => array(
							'html'  => __( 'HTML', 'fl-builder' ),
							'video' => __( 'Video', 'fl-builder' ),
						),
						'preview' => array( 'type' => 'none' ),
						'toggle'  => array(
							'html'  => array( 'fields' => array( 'lightbox_content_html' ) ),
							'video' => array( 'fields' => array( 'lightbox_video_link' ) ),
						),
					),
					'lightbox_content_html' => array(
						'type'        => 'code',
						'editor'      => 'html',
						'label'       => '',
						'rows'        => '19',
						'preview'     => array( 'type' => 'none' ),
						'connections' => array( 'string' ),
					),
					'lightbox_video_link'   => array(
						'type'        => 'text',
						'label'       => __( 'Video Link', 'fl-builder' ),
						'placeholder' => 'https://vimeo.com/122546221',
						'preview'     => array( 'type' => 'none' ),
						'connections' => array( 'custom_field' ),
					),
				),
			),
		),
	),
	'style'   => array(
		'title'    => __( 'Style', 'fl-builder' ),
		'sections' => array(
			'style'  => array(
				'title'  => '',
				'fields' => array(
					'width'        => array(
						'type'    => 'select',
						'label'   => __( 'Width', 'fl-builder' ),
						'default' => 'auto',
						'options' => array(
							'auto'   => _x( 'Auto', 'Width.', 'fl-builder' ),
							'full'   => __( 'Full Width', 'fl-builder' ),
							'custom' => __( 'Custom', 'fl-builder' ),
						),
						'toggle'  => array(
							'auto'   => array( 'fields' => array( 'align' ) ),
							'full'   => array(),
							'custom' => array( 'fields' => array( 'align', 'custom_width' ) ),
						),
					),
					'custom_width' => array(
						'type'       => 'unit',
						'label'      => __( 'Custom Width', 'fl-builder' ),
						'default'    => '200',
						'responsive' => true,
						'slider'     => array(
							'px' => array(
								'min'  => 0,
								'max'  => 1000,
								'step' => 10,
							),
						),
						'units'      => array( 'px', 'vw', '%' ),
						'preview'    => array(
							'type'     => 'css',
							'selector' => '.fl-button:is(a, button)',
							'property' => 'width',
						),
					),
					'align'        => array(
						'type'       => 'align',
						'label'      => __( 'Align', 'fl-builder' ),
						'default'    => 'left',
						'responsive' => true,
						'preview'    => array(
							'type'     => 'css',
							'selector' => '{node}.fl-button-wrap, .fl-button-wrap',
							'property' => 'text-align',
						),
					),
					'padding'      => array(
						'type'       => 'dimension',
						'label'      => __( 'Padding', 'fl-builder' ),
						'responsive' => true,
						'slider'     => true,
						'units'      => array( 'px' ),
						'preview'    => array(
							'type'     => 'css',
							'selector' => '.fl-button:is(a, button)',
							'property' => 'padding',
						),
					),
				),
			),
			'text'   => array(
				'title'  => __( 'Text', 'fl-builder' ),
				'fields' => array(
					'text_color'       => array(
						'type'        => 'color',
						'connections' => array( 'color' ),
						'label'       => __( 'Text Color', 'fl-builder' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => '.fl-button:is(a, button), .fl-button:is(a, button) *',
							'property'  => 'color',
							'important' => true,
						),
					),
					'text_hover_color' => array(
						'type'        => 'color',
						'connections' => array( 'color' ),
						'label'       => __( 'Text Hover Color', 'fl-builder' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => '.fl-button:is(a, button):hover, .fl-button:is(a, button):hover *',
							'property'  => 'color',
							'important' => true,
						),
					),
					'typography'       => array(
						'type'       => 'typography',
						'label'      => __( 'Typography', 'fl-builder' ),
						'responsive' => true,
						'preview'    => array(
							'type'     => 'css',
							'selector' => '.fl-button:is(a, button)',
						),
					),
				),
			),
			'icons'  => array(
				'title'  => __( 'Icon', 'fl-builder' ),
				'fields' => array(
					'duo_color1' => array(
						'label'       => __( 'DuoTone Icon Primary Color', 'fl-builder' ),
						'type'        => 'color',
						'connections' => array( 'color' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => 'i.fl-button-icon.fad:before',
							'property'  => 'color',
							'important' => true,
						),
					),
					'duo_color2' => array(
						'label'       => __( 'DuoTone Icon Secondary Color', 'fl-builder' ),
						'type'        => 'color',
						'connections' => array( 'color' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'preview'     => array(
							'type'      => 'css',
							'selector'  => 'i.fl-button-icon.fad:after',
							'property'  => 'color',
							'important' => true,
						),
					),
				),
			),
			'colors' => array(
				'title'  => __( 'Background', 'fl-builder' ),
				'fields' => array(
					'style'             => array(
						'type'    => 'select',
						'label'   => __( 'Background Style', 'fl-builder' ),
						'default' => 'flat',
						'options' => array(
							'flat'         => __( 'Flat', 'fl-builder' ),
							'gradient'     => __( 'Auto Gradient', 'fl-builder' ),
							'adv-gradient' => __( 'Advanced Gradient', 'fl-builder' ),
						),
						'toggle'  => array(
							'flat'         => array( 'fields' => array( 'button_transition' ) ),
							'adv-gradient' => array( 'fields' => array( 'bg_gradient', 'bg_gradient_hover' ) ),
						),
						'hide'    => array(
							'adv-gradient' => array( 'fields' => array( 'bg_color', 'bg_hover_color' ) ),
						),
					),
					'bg_color'          => array(
						'type'        => 'color',
						'connections' => array( 'color' ),
						'label'       => __( 'Background Color', 'fl-builder' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array( 'type' => 'refresh' ),
					),
					'bg_hover_color'    => array(
						'type'        => 'color',
						'connections' => array( 'color' ),
						'label'       => __( 'Background Hover Color', 'fl-builder' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array( 'type' => 'none' ),
					),
					'button_transition' => array(
						'type'       => 'select',
						'label'      => __( 'Background Animation', 'fl-builder' ),
						'default'    => 'disable',
						'options'    => array(
							'disable' => __( 'Disabled', 'fl-builder' ),
							'enable'  => __( 'Enabled', 'fl-builder' ),
						),
						'responsive' => true,
						'preview'    => array( 'type' => 'none' ),
					),
					'bg_gradient'       => array(
						'type'    => 'gradient',
						'label'   => __( 'Background Gradient', 'fl-builder' ),
						'preview' => array( 'type' => 'refresh' ),
					),
					'bg_gradient_hover' => array(
						'type'    => 'gradient',
						'label'   => __( 'Background Hover Gradient', 'fl-builder' ),
						'preview' => array( 'type' => 'none' ),
					),
				),
			),
			'border' => array(
				'title'  => __( 'Border', 'fl-builder' ),
				'fields' => array(
					'border'             => array(
						'type'       => 'border',
						'label'      => __( 'Border', 'fl-builder' ),
						'responsive' => true,
						'preview'    => array(
							'type'      => 'css',
							'selector'  => '.fl-button:is(a, button)',
							'important' => true,
						),
					),
					'border_hover_color' => array(
						'type'        => 'color',
						'connections' => array( 'color' ),
						'label'       => __( 'Border Hover Color', 'fl-builder' ),
						'default'     => '',
						'show_reset'  => true,
						'show_alpha'  => true,
						'responsive'  => true,
						'preview'     => array( 'type' => 'none' ),
					),
				),
			),
		),
	),
));
