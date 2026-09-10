<?php

/**
 * Popup module class.
 *
 * @since 2.11
 * @access public
 * @package FLBuilder
 * @subpackage Modules
 * @author Justin
 */
class FLBuilderPopupModule extends FLBuilderModule {
	/**
	 * Module class constructor.
	 *
	 * @since 2.11
	 * @access public
	 * @method __construct
	 * @return void
	 */
	public function __construct() {
		parent::__construct( [
			'name'            => __( 'Popup', 'fl-builder' ),
			'description'     => __( 'A container for building dialogs, popovers, and flyouts.', 'fl-builder' ),
			'category'        => __( 'Layout', 'fl-builder' ),
			'icon'            => 'popup.svg',
			'partial_refresh' => true,
			'include_wrapper' => false,
			'accepts'         => 'all',
		] );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_builder_scripts' );
		add_action( 'fl_builder_pre_editing_enabled', __CLASS__ . '::pre_editing_enabled' );
		add_filter( 'fl_builder_get_user_templates', __CLASS__ . '::get_user_templates', 10, 2 );
	}

	/**
	 * Filters the module old settings name to the new settings name for backwards compatibility.
	 *
	 * @since 2.11
	 * @access public
	 * @method filter_raw_settings_defaults
	 * @param object $settings The module settings object
	 * @param object $defaults The module default settings object
	 * @return object The updated module settings object with old settings names replaced with new settings names if applicable
	 */
	public function filter_raw_settings_defaults( $settings, $_defaults ): object {
		$mapping = [
			'trigger_scroll_percent' => 'trigger_scroll',
			'trigger_start_date'     => 'schedule_start_date',
			'trigger_end_date'       => 'schedule_end_date',
			'close_on_esc'           => 'auto_close',
			'loop_navigation_arrows' => 'loop_navigation',
			'position'               => 'popup_position',
			'size'                   => 'popup_size',
			'padding'                => 'popup_padding',
			'background'             => 'popup_background',
			'backdrop_background'    => 'popup_backdrop',
			'border'                 => 'popup_border',
			'close_button_bg_color'  => 'close_button_background',
			'nav_button_bg_color'    => 'nav_button_background',
		];
		foreach ( $mapping as $legacy => $modern ) {
			if ( isset( $settings->$legacy ) ) {
				$settings->$modern = $settings->$legacy;
				unset( $settings->$legacy );
			}
		}
		return $settings;
	}

	/**
	 * Enqueue scripts and styles for handling the UI when editing popups in the builder.
	 *
	 * @since 2.11
	 * @access public
	 * @method enqueue_builder_scripts
	 * @return void
	 */
	public static function enqueue_builder_scripts(): void {
		if ( ! FLBuilderModel::is_builder_active() && ! self::check_themer_layout() ) {
			return;
		}
		wp_enqueue_style( 'fl-popup-builder', FL_BUILDER_URL . 'modules/popup/css/builder.css', [], FL_BUILDER_VERSION );
		if ( ! FLBuilderModel::is_builder_active() ) {
			return;
		}
		wp_enqueue_script( 'fl-popup-builder', FL_BUILDER_URL . 'modules/popup/js/builder.js', [], FL_BUILDER_VERSION );
	}

	/**
	 * Ensure a popup module is added to the layout data when editing a Themer popup layout that is empty.
	 *
	 * @since 2.11
	 * @access public
	 * @method pre_editing_enabled
	 * @return void
	 */
	public static function pre_editing_enabled(): void {
		if ( ! FLThemeBuilderLayoutData::current_post_is( 'popup' ) ) {
			return;
		}
		if ( ! empty( FLBuilderModel::get_layout_data( 'published' ) ) ) {
			return;
		}
		if ( ! empty( FLBuilderModel::get_layout_data( 'draft' ) ) ) {
			return;
		}
		$settings = FLBuilderModel::get_module_defaults( 'popup' );
		FLBuilderModel::add_module( 'popup', $settings, null, 0 );
	}

	/**
	 * Filter user layout templates to only include popups when editing a Themer popup layout in the builder.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_user_templates
	 * @param array $templates Array of templates
	 * @param string $type The type of templates being requested
	 * @return array Filtered array of popup templates if editing a Themer popup layout
	 */
	public static function get_user_templates( array $templates, string $type ): array {
		if ( 'layout' !== $type || ! FLThemeBuilderLayoutData::current_post_is( 'popup' ) ) {
			return $templates;
		}
		$filtered = array_filter( $templates, function ( $template ) {
			return 'popup' === get_post_meta( $template['postId'], '_fl_theme_layout_type', true );
		} );
		return array_values( $filtered );
	}

	/**
	 * Gets the popup module default svg icon for a given button type.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_button_icon
	 * @param string $type The type of button to retrieve default icon for
	 * @return string The SVG markup for the selected default icon
	 */
	private static function get_button_icon( string $type ): string {
		$paths = [
			'close' => 'M5 5 Q10 10 15 15 M15 5 Q10 10 5 15',
			'prev'  => 'M12.5 3.5a1 1 0 0 1 0 1.4L8.4 9l4.1 4.1a1 1 0 1 1-1.4 1.4L6.3 9.7a1 1 0 0 1 0-1.4l4.8-4.8a1 1 0 0 1 1.4 0z',
			'next'  => 'M7.5 3.5a1 1 0 0 0 0 1.4L11.6 9l-4.1 4.1a1 1 0 1 0 1.4 1.4l4.8-4.8a1 1 0 0 0 0-1.4L8.9 3.5a1 1 0 0 0-1.4 0z',
		];
		if ( isset( $paths[ $type ] ) ) {
			return sprintf( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true"><path d="%s"/></svg>', $paths[ $type ] );
		}
		return '';
	}

	/**
	 * Check if the current popup is a Themer layout.
	 *
	 * @since 2.11
	 * @access private
	 * @method check_themer_layout
	 * @return bool True if the current popup is a Themer layout
	 */
	private static function check_themer_layout(): bool {
		$post = FLThemeBuilderRulesLocation::get_preview_original_post();
		return $post && 'fl-theme-layout' === $post->post_type;
	}

	/**
	 * Checks if the popup requires to render a wrapper element to display a placeholder in the builder.
	 *
	 * @since 2.11
	 * @access private
	 * @method check_requires_wrapper
	 * @return bool True if the wrapper element is needed
	 */
	private function check_requires_wrapper(): bool {
		return FLBuilderModel::is_builder_active() || self::check_themer_layout();
	}

	/**
	 * Gets the wrapper attributes for the popup placeholder if the popup is being edited or viewed in Themer.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_wrapper_attributes
	 * @return string The HTML attributes for the wrapper element
	 */
	private function get_wrapper_attributes(): string {
		$attributes                 = $this->render_attributes( [ 'class' => [ 'fl-popup-wrapper' ] ], false );
		$attributes['data-node-id'] = $this->get_trigger_id();
		unset( $attributes['id'], $attributes['data-accepts'] );
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Gets the trigger ID or generates one if not set in the settings.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_trigger_id
	 * @return string The trigger ID for the popup element
	 */
	private function get_trigger_id(): string {
		$id = $this->settings->trigger_id;
		return empty( $id ) ? 'fl-popup-' . $this->node : do_shortcode( $id );
	}

	/**
	 * Gets the trigger configurations of the popup module.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_trigger_configuration
	 * @param string $type The type of trigger to get configurations for
	 * @return string The JSON encoded trigger configurations
	 */
	public function get_trigger_configuration( string $type ): string {
		$active  = in_array( $type, (array) $this->settings->trigger_on, true );
		$setting = $this->settings->{"trigger_{$type}"} ?? null;
		$values  = [
			'exit'   => $active ? true : null,
			'delay'  => $active ? intval( $setting ) : null,
			'scroll' => $active ? max( 0, min( 100, intval( $setting ) ) ) : null,
			'once'   => 'disabled' !== $setting ? $setting : null,
		];
		return json_encode( $values[ $type ] ?? null );
	}

	/**
	 * Gets the trigger schedule configurations of the popup module.
	 *
	 * @since 2.11
	 * @access public
	 * @method get_trigger_schedule
	 * @param string $type The type of schedule to get configurations for
	 * @return string The schedule date value for the specified type
	 */
	public function get_trigger_schedule( string $type ): string {
		$setting = $this->settings->{"schedule_{$type}_date"};
		return json_encode( '' !== $setting ? $setting : null );
	}

	/**
	 * Gets the toggling element tag name for the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_popup_tag
	 * @return string The HTML tag name for the toggling element
	 */
	private function get_popup_tag(): string {
		return 'modal' === $this->settings->open_behavior ? 'dialog' : 'div';
	}

	/**
	 * Gets the open behavior configurations of the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_popup_behavior
	 * @param array $attributes The current popup attributes
	 * @return array The modified popup attributes with open behavior
	 */
	private function get_popup_behavior( array $attributes ): array {
		if ( FLBuilderModel::is_builder_active() ) {
			$attributes['popover'] = 'manual';
			return $attributes;
		}
		$behavior = $this->settings->open_behavior;
		$dismiss  = 'yes' === $this->settings->auto_close;
		switch ( $behavior ) {
			case 'popover':
				$attributes['popover'] = $dismiss ? 'auto' : 'manual';
				break;
			case 'modal':
				$attributes['closedby'] = $dismiss ? 'any' : 'none';
				break;
		}
		return $attributes;
	}

	/**
	 * Gets toggling element HTML attributes of the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_popup_attributes
	 * @param bool $wrapper Whether to return attributes for the wrapper element or not
	 * @return string The HTML attributes for the popup element
	 */
	private function get_popup_attributes( $wrapper = false ): string {
		$attributes = [
			'id'    => $this->get_trigger_id(),
			'class' => [ 'fl-popup' ],
		];
		$attributes = $this->get_popup_behavior( $attributes );
		$attributes = $this->render_attributes( $attributes, false );
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Gets the close button HTML attributes of the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_close_attributes
	 * @return string The HTML attributes for the close button element
	 */
	private function get_close_attributes(): string {
		$id         = $this->get_trigger_id();
		$type       = $this->settings->close_button_type;
		$popover    = 'popover' === $this->settings->open_behavior;
		$attributes = [
			'type'  => 'button',
			'class' => 'fl-popup-close fl-popup-close-' . $type,
		];
		if ( ! FLBuilderModel::is_builder_active() ) {
			$attributes['command']    = $popover ? 'hide-popover' : 'close';
			$attributes['commandfor'] = $id;
		}
		if ( 'icon' === $type ) {
			$attributes['aria-label'] = __( 'Close popup', 'fl-builder' );
		}
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Gets the close button element HTML content of the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_close_content
	 * @return string The HTML content for the close button
	 */
	private function get_close_content(): string {
		$type = $this->settings->close_button_type;
		if ( 'text' === $type ) {
			$text = $this->settings->close_button_text;
			return ! empty( $text ) ? esc_html( $text ) : __( 'Close', 'fl-builder' );
		}
		if ( 'icon' === $type && ! empty( $this->settings->close_button_icon ) ) {
			$classes = FLBuilderModuleUtils::get_icon_classes( $this->settings, 'close_button_' );
			return sprintf( '<i class="%s" aria-hidden="true"></i>', esc_attr( $classes ) );
		}
		return self::get_button_icon( 'close' );
	}

	/**
	 * Builds the close button output of the popup module if enabled in the settings.
	 *
	 * @since 2.11
	 * @access public
	 * @method build_close_output
	 * @return string The HTML for the close button element if enabled
	 */
	public function build_close_output(): string {
		if ( 'no' === $this->settings->close_button ) {
			return '';
		}
		$attributes = $this->get_close_attributes();
		$content    = $this->get_close_content();
		return sprintf( '<button %s>%s</button>', $attributes, $content );
	}

	/**
	 * Gets the navigation button HTML attributes of the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_navigation_attributes
	 * @param string $direction The direction of the navigation button
	 * @return string The HTML attributes for the navigation button element
	 */
	private function get_navigation_attributes( string $direction ): string {
		$attributes = [
			'type'       => 'button',
			'class'      => 'fl-popup-nav fl-popup-nav-' . $direction,
			'aria-label' => 'prev' === $direction ? __( 'Previous', 'fl-builder' ) : __( 'Next', 'fl-builder' ),
		];
		return FLBuilderModuleUtils::join_html_attributes( $attributes );
	}

	/**
	 * Gets the navigation arrow button HTML content of the popup module.
	 *
	 * @since 2.11
	 * @access private
	 * @method get_arrow_content
	 * @param string $direction The direction of the navigation button
	 * @return string The HTML for the navigation arrow button element
	 */
	private function get_arrow_content( string $direction ): string {
		$attributes = $this->get_navigation_attributes( $direction );
		$icon       = self::get_button_icon( $direction );
		return sprintf( '<button %s disabled>%s</button>', $attributes, $icon );
	}

	/**
	 * Builds the navigation arrows output of the popup module if enabled in the settings.
	 *
	 * @since 2.11
	 * @access public
	 * @method build_navigation_output
	 * @return string The HTML for the navigation arrows if enabled
	 */
	public function build_navigation_output(): string {
		if ( 'no' === $this->settings->loop_navigation ) {
			return '';
		}
		$prev = $this->get_arrow_content( 'prev' );
		$next = $this->get_arrow_content( 'next' );
		return $prev . $next;
	}

	/**
	 * Builds the content children output of the popup module.
	 *
	 * @since 2.11
	 * @access public
	 * @method build_content_output
	 * @return string The HTML for the content children of the popup
	 */
	public function build_content_output(): string {
		ob_start();
		$this->render_children_with_wrapper( 'div', [ 'class' => 'fl-popup-content' ] );
		return ob_get_clean();
	}

	/**
	 * Builds the popup module opening tag output.
	 *
	 * @since 2.11
	 * @access public
	 * @method build_opening_output
	 * @return string The HTML opening tag for the popup element
	 */
	public function build_opening_output(): string {
		$opening = sprintf( '<%s %s>', $this->get_popup_tag(), $this->get_popup_attributes() );
		return $this->check_requires_wrapper() ? sprintf( '<div %s>%s', $this->get_wrapper_attributes(), $opening ) : $opening;
	}

	/**
	 * Builds the popup module closing tag output.
	 *
	 * @since 2.11
	 * @access public
	 * @method build_closing_output
	 * @return string The HTML closing tag for the popup element
	 */
	public function build_closing_output(): string {
		$closing = sprintf( '</%s>', $this->get_popup_tag() );
		return $this->check_requires_wrapper() ? $closing . '</div>' : $closing;
	}
}

FLBuilder::register_module( 'FLBuilderPopupModule', [
	'general' => [
		'title'    => __( 'General', 'fl-builder' ),
		'sections' => [
			'trigger'    => [
				'title'  => __( 'Trigger', 'fl-builder' ),
				'fields' => [
					'trigger_id'     => [
						'label'       => __( 'Popup ID', 'fl-builder' ),
						'help'        => __( 'Used to open the popup with button Invoker Commands or with anchor links (e.g., #my-popup-id). Be sure the ID is unique and doesn’t contain spaces.', 'fl-builder' ),
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'e.g. my-popup-id', 'fl-builder' ),
						'preview'     => [ 'type' => 'none' ],
					],
					'trigger_on'     => [
						'label'        => __( 'Show On', 'fl-builder' ),
						'help'         => __( 'Choose when the popup should appear automatically, such as after a delay or when the user shows exit intent by moving their cursor toward the browser’s close button.', 'fl-builder' ),
						'type'         => 'button-group',
						'fill_space'   => true,
						'allow_empty'  => true,
						'multi-select' => true,
						'default'      => '',
						'preview'      => [ 'type' => 'none' ],
						'options'      => [
							'delay'  => __( 'Delay', 'fl-builder' ),
							'scroll' => __( 'Scroll', 'fl-builder' ),
							'exit'   => __( 'Exit', 'fl-builder' ),
						],
						'toggle'       => [
							'exit'              => [ 'fields' => [ 'trigger_once' ] ],
							'delay'             => [ 'fields' => [ 'trigger_once', 'trigger_delay' ] ],
							'scroll'            => [ 'fields' => [ 'trigger_once', 'trigger_scroll' ] ],
							'delay,exit'        => [ 'fields' => [ 'trigger_once', 'trigger_delay' ] ],
							'scroll,exit'       => [ 'fields' => [ 'trigger_once', 'trigger_scroll' ] ],
							'delay,scroll'      => [ 'fields' => [ 'trigger_once', 'trigger_delay', 'trigger_scroll' ] ],
							'delay,scroll,exit' => [ 'fields' => [ 'trigger_once', 'trigger_delay', 'trigger_scroll' ] ],
						],
					],
					'trigger_delay'  => [
						'label'   => __( 'Show Delay', 'fl-builder' ),
						'type'    => 'unit',
						'default' => '0',
						'units'   => [ 'seconds' ],
						'preview' => [ 'type' => 'none' ],
					],
					'trigger_scroll' => [
						'label'   => __( 'Show on Percent Scrolled', 'fl-builder' ),
						'type'    => 'unit',
						'units'   => [ '%' ],
						'default' => '0',
						'slider'  => [
							'min'  => 0,
							'max'  => 100,
							'step' => 1,
						],
						'help'    => __( 'Displays the popup when the visitor has scrolled this much of the page.', 'fl-builder' ),
						'preview' => [ 'type' => 'none' ],
					],
					'trigger_once'   => [
						'label'       => __( 'Show Once', 'fl-builder' ),
						'help'        => __( 'Prevents the popup from appearing again after it has already been shown. A cookie is used to remember the user and ensure the popup doesn’t display on future visits.', 'fl-builder' ),
						'type'        => 'button-group',
						'fill_space'  => true,
						'allow_empty' => false,
						'default'     => 'disabled',
						'preview'     => [ 'type' => 'none' ],
						'options'     => [
							'per_user'    => __( 'Per User', 'fl-builder' ),
							'per_session' => __( 'Per Session', 'fl-builder' ),
							'disabled'    => __( 'Disabled', 'fl-builder' ),
						],
					],
				],
			],
			'schedule'   => [
				'title'  => __( 'Schedule', 'fl-builder' ),
				'fields' => [
					'schedule_start_date' => [
						'type'        => 'date',
						'label'       => __( 'Show Start Date', 'fl-builder' ),
						'help'        => __( 'The popup will begin displaying on this date.', 'fl-builder' ),
						'default'     => '',
						'preview'     => [ 'type' => 'none' ],
						'connections' => [ 'custom_field' ],
					],
					'schedule_end_date'   => [
						'type'        => 'date',
						'label'       => __( 'Show End Date', 'fl-builder' ),
						'help'        => __( 'The popup will stop displaying after this date.', 'fl-builder' ),
						'default'     => '',
						'preview'     => [ 'type' => 'none' ],
						'connections' => [ 'custom_field' ],
					],
				],
			],
			'behavior'   => [
				'title'  => __( 'Behavior', 'fl-builder' ),
				'fields' => [
					'open_behavior' => [
						'label'       => __( 'Open Behavior', 'fl-builder' ),
						'help'        => __( 'The modal behavior adds focus lock to the popup unlike the popover behavior.', 'fl-builder' ),
						'type'        => 'button-group',
						'fill_space'  => true,
						'allow_empty' => false,
						'default'     => 'popover',
						'options'     => [
							'popover' => __( 'Popover', 'fl-builder' ),
							'modal'   => __( 'Modal', 'fl-builder' ),
						],
					],
				],
			],
			'dismiss'    => [
				'title'  => __( 'Dismiss', 'fl-builder' ),
				'fields' => [
					'auto_close'   => [
						'label'       => __( 'Close on ESC/Click Outside', 'fl-builder' ),
						'type'        => 'button-group',
						'fill_space'  => true,
						'allow_empty' => false,
						'default'     => 'yes',
						'options'     => [
							'yes' => __( 'Enabled', 'fl-builder' ),
							'no'  => __( 'Disabled', 'fl-builder' ),
						],
					],
					'close_button' => [
						'label'       => __( 'Close Button', 'fl-builder' ),
						'type'        => 'button-group',
						'fill_space'  => true,
						'allow_empty' => false,
						'default'     => 'yes',
						'options'     => [
							'yes' => __( 'Enabled', 'fl-builder' ),
							'no'  => __( 'Disabled', 'fl-builder' ),
						],
						'toggle'      => [ 'yes' => [ 'sections' => [ 'close' ] ] ],
					],
				],
			],
			'navigation' => [
				'title'  => __( 'Loop', 'fl-builder' ),
				'fields' => [
					'loop_navigation' => [
						'label'       => __( 'Loop Navigation Arrows', 'fl-builder' ),
						'type'        => 'button-group',
						'fill_space'  => true,
						'allow_empty' => false,
						'default'     => 'no',
						'options'     => [
							'yes' => __( 'Enabled', 'fl-builder' ),
							'no'  => __( 'Disabled', 'fl-builder' ),
						],
						'toggle'      => [ 'yes' => [ 'sections' => [ 'arrows' ] ] ],
					],
				],
			],
		],
	],
	'style'   => [
		'title'    => __( 'Style', 'fl-builder' ),
		'sections' => [
			'sizing'     => [
				'title'  => __( 'Sizing & Placement', 'fl-builder' ),
				'fields' => [
					'popup_position' => [
						'label'      => 'Position',
						'type'       => 'select',
						'responsive' => true,
						'default'    => 'auto auto auto auto',
						'options'    => [
							'0 auto auto 0'       => 'Top Left',
							'0 auto auto auto'    => 'Top Center',
							'0 0 auto auto'       => 'Top Right',
							'auto auto auto 0'    => 'Center Left',
							'auto auto auto auto' => 'Center',
							'auto 0 auto auto'    => 'Center Right',
							'auto auto 0 0'       => 'Bottom Left',
							'auto auto 0 auto'    => 'Bottom Center',
							'auto 0 0 auto'       => 'Bottom Right',
						],
						'preview'    => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property' => 'margin',
									'selector' => '{node}.fl-popup',
								],
							],
						],
					],
					'popup_size'     => [
						'label'      => __( 'Width & Height', 'fl-builder' ),
						'type'       => 'size',
						'responsive' => true,
						'default'    => [
							'width'      => [
								'length' => '500',
								'unit'   => 'px',
							],
							'max_width'  => [
								'length' => '100',
								'unit'   => '%',
							],
							'min_height' => [
								'length' => '200',
								'unit'   => 'px',
							],
						],
						'preview'    => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property'  => 'min-width',
									'selector'  => '{node}.fl-popup',
									'sub_value' => [
										'setting_name' => 'min_width',
										'type'         => 'unit',
									],
								],
								[
									'property'  => 'width',
									'selector'  => '{node}.fl-popup',
									'sub_value' => [
										'setting_name' => 'width',
										'type'         => 'unit',
									],
								],
								[
									'property'  => 'max-width',
									'selector'  => '{node}.fl-popup',
									'sub_value' => [
										'setting_name' => 'max_width',
										'type'         => 'unit',
									],
								],
								[
									'property'  => 'min-height',
									'selector'  => '{node}.fl-popup',
									'sub_value' => [
										'setting_name' => 'min_height',
										'type'         => 'unit',
									],
								],
								[
									'property'  => 'height',
									'selector'  => '{node}.fl-popup',
									'sub_value' => [
										'setting_name' => 'height',
										'type'         => 'unit',
									],
								],
								[
									'property'  => 'max-height',
									'selector'  => '{node}.fl-popup',
									'sub_value' => [
										'setting_name' => 'max_height',
										'type'         => 'unit',
									],
								],
							],
						],
					],
					'popup_padding'  => [
						'label'      => __( 'Padding', 'fl-builder' ),
						'type'       => 'dimension',
						'responsive' => true,
						'slider'     => true,
						'units'      => [ 'px', 'em', '%', 'vw', 'vh' ],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'padding',
							'selector' => '{node}.fl-popup',
						],
					],
				],
			],
			'appearance' => [
				'title'  => __( 'Appearance', 'fl-builder' ),
				'fields' => [
					'popup_background' => [
						'label'      => 'Background',
						'type'       => 'background',
						'responsive' => true,
						'preview'    => [
							'type'      => 'css',
							'auto'      => true,
							'property'  => 'background',
							'selector'  => '{node}.fl-popup',
							'sub_value' => [ 'setting_name' => 'css' ],
						],
					],
					'popup_backdrop'   => [
						'label'      => 'Backdrop',
						'type'       => 'background',
						'responsive' => true,
						'preview'    => [
							'type'      => 'css',
							'auto'      => true,
							'property'  => 'background',
							'selector'  => '{node}.fl-popup::backdrop',
							'sub_value' => [ 'setting_name' => 'css' ],
						],
						'default'    => [
							'layers' => [
								[
									'id'    => 1,
									'type'  => 'color',
									'state' => [ 'color' => 'rgba(171, 183, 196, 0.3)' ],
								],
							],
							'css'    => 'rgba(171, 183, 196, 0.3)',
						],
					],
					'popup_border'     => [
						'label'      => 'Border',
						'type'       => 'border',
						'responsive' => true,
						'default'    => [ 'style' => 'none' ],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'border',
							'selector' => '{node}.fl-popup',
						],
					],
				],
			],
			'close'      => [
				'title'  => __( 'Close Button', 'fl-builder' ),
				'fields' => [
					'close_button_type'       => [
						'label'   => __( 'Button Type', 'fl-builder' ),
						'type'    => 'select',
						'default' => 'icon',
						'options' => [
							'icon' => __( 'Icon', 'fl-builder' ),
							'text' => __( 'Text', 'fl-builder' ),
						],
						'toggle'  => [
							'icon' => [ 'fields' => [ 'close_button_icon' ] ],
							'text' => [ 'fields' => [ 'close_button_text' ] ],
						],
					],
					'close_button_icon'       => [
						'label'              => __( 'Button Icon', 'fl-builder' ),
						'type'               => 'icon',
						'default'            => '',
						'show_remove'        => true,
						'show_extra_classes' => true,
						'connections'        => [ 'icon' ],
					],
					'close_button_text'       => [
						'label'   => __( 'Button Text', 'fl-builder' ),
						'type'    => 'text',
						'default' => __( 'Close', 'fl-builder' ),
					],
					'close_button_position'   => [
						'label'      => __( 'Button Attachment', 'fl-builder' ),
						'type'       => 'select',
						'responsive' => true,
						'default'    => 'absolute',
						'options'    => [
							'absolute' => __( 'Popup', 'fl-builder' ),
							'fixed'    => __( 'Window', 'fl-builder' ),
						],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'position',
							'selector' => '{node}.fl-popup button.fl-popup-close',
						],
						'set'        => [
							'fixed' => [
								'close_button_inset' => [
									'top'    => '20',
									'right'  => '20',
									'bottom' => '',
									'left'   => '',
								],
							],
						],
					],
					'close_button_inset'      => [
						'label'      => __( 'Button Position', 'fl-builder' ),
						'type'       => 'dimension',
						'responsive' => true,
						'slider'     => [
							'min' => '-100',
							'max' => '100',
						],
						'units'      => [ 'px' ],
						'default'    => [
							'top'    => '-45',
							'right'  => '-45',
							'bottom' => '',
							'left'   => '',
						],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'inset',
							'selector' => '{node}.fl-popup button.fl-popup-close',
						],
					],
					'close_button_padding'    => [
						'label'      => __( 'Button Padding', 'fl-builder' ),
						'type'       => 'dimension',
						'responsive' => true,
						'slider'     => true,
						'units'      => [ 'px' ],
						'default'    => [
							'top'    => '5',
							'right'  => '5',
							'bottom' => '5',
							'left'   => '5',
						],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'padding',
							'selector' => '{node}.fl-popup button.fl-popup-close',
						],
					],
					'close_button_size'       => [
						'label'        => __( 'Button Size', 'fl-builder' ),
						'type'         => 'unit',
						'responsive'   => true,
						'slider'       => true,
						'units'        => [ 'px', 'em', 'rem' ],
						'default'      => '40',
						'default_unit' => 'px',
						'preview'      => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property' => 'width',
									'selector' => '{node}.fl-popup button.fl-popup-close svg',
								],
								[
									'property' => 'height',
									'selector' => '{node}.fl-popup button.fl-popup-close svg',
								],
								[
									'property' => 'font-size',
									'selector' => '{node}.fl-popup button.fl-popup-close',
								],
							],
						],
					],
					'close_button_color'      => [
						'label'       => __( 'Button Color', 'fl-builder' ),
						'type'        => 'color',
						'default'     => '#333333',
						'responsive'  => true,
						'show_reset'  => true,
						'show_alpha'  => true,
						'connections' => [ 'color' ],
						'preview'     => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property' => 'color',
									'selector' => '{node}.fl-popup button.fl-popup-close',
								],
								[
									'property' => 'fill',
									'selector' => '{node}.fl-popup button.fl-popup-close svg',
								],
							],
						],
					],
					'close_button_background' => [
						'label'       => __( 'Button Background', 'fl-builder' ),
						'type'        => 'color',
						'responsive'  => true,
						'show_reset'  => true,
						'show_alpha'  => true,
						'connections' => [ 'color' ],
						'preview'     => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'background',
							'selector' => '{node}.fl-popup button.fl-popup-close',
						],
					],
					'close_button_border'     => [
						'label'      => 'Button Border',
						'type'       => 'border',
						'responsive' => true,
						'default'    => [ 'style' => 'none' ],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'border',
							'selector' => '{node}.fl-popup button.fl-popup-close',
						],
					],
				],
			],
			'arrows'     => [
				'title'  => __( 'Navigation Arrows', 'fl-builder' ),
				'fields' => [
					'nav_button_offset'     => [
						'label'        => __( 'Horizontal Offset', 'fl-builder' ),
						'type'         => 'unit',
						'responsive'   => true,
						'units'        => [ 'px', 'em', 'rem', '%' ],
						'default'      => '0',
						'default_unit' => 'px',
						'preview'      => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property' => 'left',
									'selector' => '{node}.fl-popup .fl-popup-nav-prev',
								],
								[
									'property' => 'right',
									'selector' => '{node}.fl-popup .fl-popup-nav-next',
								],
							],
						],
					],
					'nav_button_size'       => [
						'label'        => __( 'Button Size', 'fl-builder' ),
						'type'         => 'unit',
						'responsive'   => true,
						'slider'       => true,
						'units'        => [ 'px', 'em', 'rem' ],
						'default'      => '42',
						'default_unit' => 'px',
						'preview'      => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property' => 'width',
									'selector' => '{node}.fl-popup .fl-popup-nav',
								],
								[
									'property' => 'height',
									'selector' => '{node}.fl-popup .fl-popup-nav',
								],
							],
						],
					],
					'nav_button_color'      => [
						'label'       => __( 'Icon Color', 'fl-builder' ),
						'type'        => 'color',
						'default'     => '#333333',
						'responsive'  => true,
						'show_reset'  => true,
						'show_alpha'  => true,
						'connections' => [ 'color' ],
						'preview'     => [
							'type'  => 'css',
							'auto'  => true,
							'rules' => [
								[
									'property' => 'color',
									'selector' => '{node}.fl-popup .fl-popup-nav',
								],
								[
									'property' => 'fill',
									'selector' => '{node}.fl-popup .fl-popup-nav svg',
								],
							],
						],
					],
					'nav_button_background' => [
						'label'       => __( 'Button Background', 'fl-builder' ),
						'type'        => 'color',
						'responsive'  => true,
						'show_reset'  => true,
						'show_alpha'  => true,
						'connections' => [ 'color' ],
						'preview'     => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'background',
							'selector' => '{node}.fl-popup .fl-popup-nav',
						],
					],
					'nav_button_border'     => [
						'label'      => __( 'Button Border', 'fl-builder' ),
						'type'       => 'border',
						'responsive' => true,
						'default'    => [ 'style' => 'none' ],
						'preview'    => [
							'type'     => 'css',
							'auto'     => true,
							'property' => 'border',
							'selector' => '{node}.fl-popup .fl-popup-nav',
						],
					],
				],
			],
		],
	],
] );

require_once __DIR__ . '/popup-aliases.php';
