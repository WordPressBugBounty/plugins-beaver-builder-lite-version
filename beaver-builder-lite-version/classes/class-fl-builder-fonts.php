<?php

/**
 * Helper class for font settings.
 *
 * @class   FLBuilderFonts
 * @since   1.6.3
 */
final class FLBuilderFonts {

	/**
	 * An array of fonts / weights.
	 * @var array
	 */
	private static $fonts = array();

	/**
	 * An array of WP Font Library fonts / weights collected per request.
	 * Kept separate from $fonts so the Google enqueue logic does not pick them up.
	 *
	 * @since 2.10.5
	 * @var array
	 */
	private static $wp_fonts = array();

	private static $enqueued_google_fonts_done = false;

	public static $preload_fa5 = array();

	/**
	 * @since 1.9.5
	 * @return void
	 */
	static public function init() {
		add_filter( 'the_content', __CLASS__ . '::combine_google_fonts', 11 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::combine_google_fonts', 10000 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_google_fonts', 9999 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_wp_fonts', 9999 );
		add_filter( 'wp_resource_hints', __CLASS__ . '::resource_hints', 10, 2 );
		add_action( 'wp_head', array( __CLASS__, 'preload' ), 5 );
		add_action( 'fl_builder_cache_cleared', array( __CLASS__, 'clear_transient' ), 11 );
		add_action( 'save_post_wp_font_family', __CLASS__ . '::on_wp_font_changed' );
		add_action( 'save_post_wp_font_face', __CLASS__ . '::on_wp_font_changed' );
		add_action( 'deleted_post', __CLASS__ . '::on_post_deleted', 10, 2 );
	}

	/**
	 * Resets the WP Font Library registry cache. Hooked to font CPT mutations
	 * so a font upload, edit, or delete is reflected next page render.
	 *
	 * @since 2.10.5
	 * @return void
	 */
	static public function clear_wp_fonts_cache() {
		FLBuilderFontFamilies::clear_wp_library_cache();
	}

	/**
	 * Invalidates BB's per-request WP font cache AND the on-disk layout CSS/JS
	 * asset cache when a Font Library mutation happens. Without the asset
	 * cache clear, layouts continue to serve stale CSS referencing a font the
	 * user has just deleted or replaced.
	 *
	 * @since 2.10.5
	 * @return void
	 */
	static public function on_wp_font_changed() {
		self::clear_wp_fonts_cache();
		if ( class_exists( 'FLBuilderModel' ) ) {
			FLBuilderModel::delete_asset_cache_for_all_posts();
		}
	}

	/**
	 * Routes deleted_post into the font-change handler only when the deleted
	 * post is a Font Library family or face. Avoids running the asset cache
	 * sweep on every unrelated post deletion.
	 *
	 * @since 2.10.5
	 * @param  int     $post_id
	 * @param  WP_Post $post
	 * @return void
	 */
	static public function on_post_deleted( $post_id, $post ) {
		if ( ! $post || ! in_array( $post->post_type, array( 'wp_font_family', 'wp_font_face' ), true ) ) {
			return;
		}
		self::on_wp_font_changed();
	}

	static public function clear_transient() {
		delete_transient( 'fl_builder_google_json' );
	}

	static public function preload() {
		$fa_version = FLBuilder::get_fa5_version();
		$icons      = array(
			'foundation-icons' => array(
				'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.woff',
			),
			'font-awesome-5'   => array(),
		);

		foreach ( array_unique( FLBuilderFonts::$preload_fa5 ) as $type ) {
			switch ( $type ) {
				case 'fas':
					$icons['font-awesome-5'][] = FLBuilder::plugin_url() . 'fonts/fontawesome/' . $fa_version . '/webfonts/fa-solid-900.woff2';
					break;
				case 'far':
					$icons['font-awesome-5'][] = FLBuilder::plugin_url() . 'fonts/fontawesome/' . $fa_version . '/webfonts/fa-regular-400.woff2';
					break;
				case 'fab':
					$icons['font-awesome-5'][] = FLBuilder::plugin_url() . 'fonts/fontawesome/' . $fa_version . '/webfonts/fa-brands-400.woff2';
					break;
			}
		}

		// if using pro cdn do not preload as we have no idea what the url will be.
		if ( get_option( '_fl_builder_enable_fa_pro', false ) || apply_filters( 'fl_enable_fa5_pro', false ) || empty( $icons['font-awesome-5'] ) ) {
			unset( $icons['font-awesome-5'] );
		}

		foreach ( $icons as $key => $preloads ) {
			if ( wp_style_is( $key, 'enqueued' ) ) {
				foreach ( $preloads as $url ) {
					printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin="anonymous">' . "\n", $url );
				}
			}
		}
	}

	/**
	 * Renders the JavaScript variable for font settings dropdowns.
	 *
	 * @since  1.6.3
	 * @return void
	 */
	static public function js() {
		/**
		 * @see fl_builder_font_families_default
		 */
		$default = json_encode( apply_filters( 'fl_builder_font_families_default', FLBuilderFontFamilies::$default ) );
		/**
		 * @see fl_builder_font_families_system
		 */
		$system = json_encode( apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system ) );
		/**
		 * @see fl_builder_font_families_google
		 */
		$google = json_encode( apply_filters( 'fl_builder_font_families_google', self::prepare_google_fonts( FLBuilderFontFamilies::google() ) ) );
		/**
		 * @see fl_builder_font_families_wp
		 */
		$wp = json_encode( apply_filters( 'fl_builder_font_families_wp', FLBuilderFontFamilies::wp_library() ) );

		echo 'var FLBuilderFontFamilies = { default: ' . $default . ', system: ' . $system . ', google: ' . $google . ', wp: ' . $wp . ' };';
	}

	static public function prepare_google_fonts( $fonts ) {
		foreach ( $fonts as $family => $variants ) {
			foreach ( $variants as $k => $variant ) {
				if ( 'italic' == $variant || 'i' == substr( $variant, -1 ) ) {
					unset( $fonts[ $family ][ $k ] );
				}
			}
		}
		return $fonts;
	}

	/**
	 * Renders a list of all available fonts.
	 *
	 * @since  1.6.3
	 * @param  string $font The current selected font.
	 * @return void
	 */
	static public function display_select_font( $font ) {
		$system_fonts = apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system );
		$google_fonts = apply_filters( 'fl_builder_font_families_google', FLBuilderFontFamilies::google() );
		$wp_fonts     = apply_filters( 'fl_builder_font_families_wp', FLBuilderFontFamilies::wp_library() );
		$recent_fonts = get_option( 'fl_builder_recent_fonts', array() );

		// Check if font is valid
		foreach ( $recent_fonts as $name => $variants ) {
			if ( ! array_key_exists( $name, $google_fonts ) && ! array_key_exists( $name, $system_fonts ) && ! array_key_exists( $name, $wp_fonts ) ) {
				unset( $recent_fonts[ $name ] );
			}
		}

		echo '<option value="Default" ' . selected( 'Default', $font, false ) . '>' . __( 'Default', 'fl-builder' ) . '</option>';

		if ( is_array( $recent_fonts ) && ! empty( $recent_fonts ) ) {
			echo '<optgroup label="Recently Used" class="recent-fonts">';
			foreach ( $recent_fonts as $name => $variants ) {
				if ( 'Default' == $name ) {
					continue;
				}
				echo '<option value="' . $name . '">' . $name . '</option>';
			}
		}

		if ( ! empty( $wp_fonts ) ) {
			echo '<optgroup label="' . esc_attr__( 'WordPress Fonts', 'fl-builder' ) . '">';
			foreach ( $wp_fonts as $name => $data ) {
				echo '<option value="' . esc_attr( $name ) . '" ' . selected( $name, $font, false ) . '>' . esc_html( $name ) . '</option>';
			}
		}

		echo '<optgroup label="System">';

		foreach ( $system_fonts as $name => $variants ) {
			echo '<option value="' . $name . '" ' . selected( $name, $font, false ) . '>' . $name . '</option>';
		}

		echo '<optgroup label="Google">';

		foreach ( $google_fonts as $name => $variants ) {
			echo '<option value="' . $name . '" ' . selected( $name, $font, false ) . '>' . $name . '</option>';
		}
	}

	/**
	 * Renders a list of all available weights for a selected font.
	 *
	 * @since  1.6.3
	 * @param  string $font   The current selected font.
	 * @param  string $weight The current selected weight.
	 * @return void
	 */
	static public function display_select_weight( $font, $weight ) {
		if ( 'Default' == $font ) {
			echo '<option value="default" selected="selected">' . __( 'Default', 'fl-builder' ) . '</option>';
		} else {
			$system_fonts = apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system );
			$google_fonts = apply_filters( 'fl_builder_font_families_google', FLBuilderFontFamilies::google() );
			$wp_fonts     = apply_filters( 'fl_builder_font_families_wp', FLBuilderFontFamilies::wp_library() );

			if ( array_key_exists( $font, $wp_fonts ) ) {
				foreach ( $wp_fonts[ $font ]['weights'] as $variant ) {
					echo '<option value="' . $variant . '" ' . selected( $variant, $weight, false ) . '>' . FLBuilderFonts::get_weight_string( $variant ) . '</option>';
				}
			} elseif ( array_key_exists( $font, $system_fonts ) ) {
				foreach ( $system_fonts[ $font ]['weights'] as $variant ) {
					echo '<option value="' . $variant . '" ' . selected( $variant, $weight, false ) . '>' . FLBuilderFonts::get_weight_string( $variant ) . '</option>';
				}
			} elseif ( isset( $google_fonts[ $font ] ) ) {
				foreach ( $google_fonts[ $font ] as $variant ) {
					echo '<option value="' . $variant . '" ' . selected( $variant, $weight, false ) . '>' . FLBuilderFonts::get_weight_string( $variant ) . '</option>';
				}
			} else {
				// Stale reference (e.g. a deleted WP font still saved into a layout).
				// Emit only Default so the dropdown is usable; JS reset will follow.
				echo '<option value="default" selected="selected">' . __( 'Default', 'fl-builder' ) . '</option>';
			}
		}
	}

	/**
	 * Returns a font weight name for a respective weight.
	 *
	 * @since  1.6.3
	 * @param  string $weight The selected weight.
	 * @return string         The weight name.
	 */
	static public function get_weight_string( $weight ) {

		$weight_string = self::get_font_weight_strings();

		return $weight_string[ $weight ];
	}

	/**
	 * Return font weight strings.
	 */
	static public function get_font_weight_strings() {
		/**
		 * Array of font weights
		 * @see fl_builder_font_weight_strings
		 */
		return apply_filters( 'fl_builder_font_weight_strings', array(
			'default'   => __( 'Default', 'fl-builder' ),
			'regular'   => __( 'Regular', 'fl-builder' ),
			'italic'    => __( 'Italic', 'fl-builder' ),
			'100'       => __( 'Thin', 'fl-builder' ),
			'100i'      => __( 'Thin Italic', 'fl-builder' ),
			'100italic' => __( 'Thin Italic', 'fl-builder' ),
			'200'       => __( 'Extra-Light', 'fl-builder' ),
			'200i'      => __( 'Extra-Light Italic', 'fl-builder' ),
			'200italic' => __( 'Extra-Light Italic', 'fl-builder' ),
			'300'       => __( 'Light', 'fl-builder' ),
			'300i'      => __( 'Light Italic', 'fl-builder' ),
			'300italic' => __( 'Light Italic', 'fl-builder' ),
			'400'       => __( 'Normal', 'fl-builder' ),
			'400i'      => __( 'Normal Italic', 'fl-builder' ),
			'400italic' => __( 'Normal Italic', 'fl-builder' ),
			'500'       => __( 'Medium', 'fl-builder' ),
			'500i'      => __( 'Medium Italic', 'fl-builder' ),
			'500italic' => __( 'Medium Italic', 'fl-builder' ),
			'600'       => __( 'Semi-Bold', 'fl-builder' ),
			'600i'      => __( 'Semi-Bold Italic', 'fl-builder' ),
			'600italic' => __( 'Semi-Bold Italic', 'fl-builder' ),
			'700'       => __( 'Bold', 'fl-builder' ),
			'700i'      => __( 'Bold Italic', 'fl-builder' ),
			'700italic' => __( 'Bold Italic', 'fl-builder' ),
			'800'       => __( 'Extra-Bold', 'fl-builder' ),
			'800i'      => __( 'Extra-Bold Italic', 'fl-builder' ),
			'800italic' => __( 'Extra-Bold Italic', 'fl-builder' ),
			'900'       => __( 'Ultra-Bold', 'fl-builder' ),
			'900i'      => __( 'Ultra-Bold Italic', 'fl-builder' ),
			'900italic' => __( 'Ultra-Bold Italic', 'fl-builder' ),
		) );
	}

	/**
	 * Helper function to render css styles for a selected font.
	 *
	 * @since  1.6.3
	 * @param  array $font An array with font-family and weight.
	 * @return void
	 */
	static public function font_css( $font ) {

		$system_fonts = apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system );
		$wp_fonts     = apply_filters( 'fl_builder_font_families_wp', FLBuilderFontFamilies::wp_library() );
		$google       = FLBuilderFontFamilies::get_google_fallback( $font['family'] );

		$css = '';

		if ( array_key_exists( $font['family'], $wp_fonts ) ) {

			$css .= 'font-family: "' . $font['family'] . '", ' . $wp_fonts[ $font['family'] ]['fallback'] . ';';

		} elseif ( array_key_exists( $font['family'], $system_fonts ) ) {

			$css .= 'font-family: "' . $font['family'] . '",' . $system_fonts[ $font['family'] ]['fallback'] . ';';

		} elseif ( $google ) {
			$css .= 'font-family: "' . $font['family'] . '", ' . $google . ';';
		} else {
			$css .= 'font-family: "' . $font['family'] . '", sans-serif;';
		}

		if ( 'regular' == $font['weight'] ) {
			$css .= 'font-weight: normal;';
		} else {
			if ( 'i' == substr( $font['weight'], -1 ) ) {
				$css .= 'font-weight: ' . substr( $font['weight'], 0, -1 ) . ';';
				$css .= 'font-style: italic;';
			} elseif ( 'italic' == $font['weight'] ) {
				$css .= 'font-style: italic;';
			} else {
				$css .= 'font-weight: ' . $font['weight'] . ';';
			}
		}

		echo $css;
	}

	/**
	 * Add fonts to the $font array for global styles.
	 *
	 * @return void
	 */
	static public function add_fonts_for_global_css( $settings ) {
		$google   = FLBuilderFontFamilies::google();
		$wp_fonts = FLBuilderFontFamilies::wp_library();
		$fields   = array(
			'text_typography',
			'h1_typography',
			'h2_typography',
			'h3_typography',
			'h4_typography',
			'h5_typography',
			'h6_typography',
			'link_typography',
			'button_typography',
		);

		foreach ( $fields as $field ) {
			if ( empty( $settings->{ $field } ) ) {
				continue;
			}
			$font   = $settings->{ $field }['font_family'];
			$weight = isset( $settings->{ $field }['font_weight'] ) && '' !== $settings->{ $field }['font_weight'] ? $settings->{ $field }['font_weight'] : '400';

			// handle google italics — skip for WP Font Library fonts, which use separate italic faces.
			if ( ! isset( $wp_fonts[ $font ] ) && isset( $google[ $font ] ) ) {
				$selected_weight = $weight;
				$italic          = ( isset( $settings->{ $field }['font_style'] ) ) ? $settings->{ $field }['font_style'] : '';

				if ( ! $italic && count( $google[ $font ] ) === 1 && 'italic' === $google[ $font ][0] ) {
					$italic = 'italic';
				}
				if ( in_array( $selected_weight . 'i', $google[ $font ] ) && 'italic' == $italic ) {
					$weight = $selected_weight . 'i';
				}
				if ( ( '400' == $selected_weight || 'regular' == $selected_weight ) && 'italic' == $italic && in_array( 'italic', $google[ $font ] ) ) {
					$weight = '400i';
				}
			}

			if ( 'Molle' === $settings->{ $field }['font_family'] ) {
				$weight = 'i';
			}

			self::add_font( array(
				'family' => $settings->{ $field }['font_family'],
				'weight' => $weight,
			) );
		}
	}

	/**
	 * Add fonts to the $font array for a module.
	 *
	 * @since  1.6.3
	 * @param  object $module The respective module.
	 * @return void
	 */
	static public function add_fonts_for_module( $module ) {
		$fields = FLBuilderModel::get_settings_form_fields( $module->form );

		// needed for italics.
		$google   = FLBuilderFontFamilies::google();
		$wp_fonts = FLBuilderFontFamilies::wp_library();
		$bold     = false;

		if ( $module instanceof FLRichTextModule || $module instanceof FLListModule ) {
			$bold = true;
		}

		foreach ( $fields as $name => $field ) {

			if ( 'font' == $field['type'] && isset( $module->settings->$name ) ) {
				self::add_font( $module->settings->$name );
			} elseif ( 'typography' == $field['type'] && ! empty( $module->settings->$name ) ) {

				// Compound settings are normally stored as arrays, but corrupted or
				// externally-written layout data can store them as objects. Coerce to
				// an array (as add_fonts_for_nested_module_form already does) so the
				// access below never fatals on a stdClass. See issue #5305.
				$typo = (array) $module->settings->$name;

				if ( ! isset( $typo['font_family'] ) ) {
					continue;
				}

				$fname  = $typo['font_family'];
				$weight = isset( $typo['font_weight'] ) && '' !== $typo['font_weight'] ? $typo['font_weight'] : '400';
				// handle google italics — skip for WP Font Library fonts, which use separate italic faces.
				if ( ! isset( $wp_fonts[ $fname ] ) && isset( $google[ $fname ] ) ) {
					$selected_weight = $weight;
					$italic          = ( isset( $typo['font_style'] ) ) ? $typo['font_style'] : '';
					if ( ! $italic && count( $google[ $fname ] ) === 1 && 'italic' === $google[ $fname ][0] ) {
						$italic = 'italic';
					}
					if ( in_array( $selected_weight . 'i', $google[ $fname ] ) && 'italic' == $italic ) {
						$weight = $selected_weight . 'i';
					}
					if ( ( '400' == $selected_weight || 'regular' == $selected_weight ) && 'italic' == $italic && in_array( 'italic', $google[ $fname ] ) ) {
						$weight = '400i';
					}
				}

				if ( 'Molle' === $fname ) {
					$weight = 'i';
				}

				self::add_font( array(
					'family' => $fname,
					'weight' => $weight,
				), $bold );
			} elseif ( isset( $field['form'] ) ) {
				$form = FLBuilderModel::$settings_forms[ $field['form'] ];
				self::add_fonts_for_nested_module_form( $module, $form['tabs'], $name );
			}
		}
	}

	/**
	 * Add fonts to the $font array for a nested module form.
	 *
	 * @since 1.8.6
	 * @access private
	 * @param object $module The module to add for.
	 * @param array $form The nested form.
	 * @param string $setting The nested form setting key.
	 * @return void
	 */
	static private function add_fonts_for_nested_module_form( $module, $form, $setting ) {
		$fields   = FLBuilderModel::get_settings_form_fields( $form );
		$google   = FLBuilderFontFamilies::google();
		$wp_fonts = FLBuilderFontFamilies::wp_library();

		foreach ( $fields as $name => $field ) {
			if ( 'font' == $field['type'] && isset( $module->settings->$setting ) ) {
				foreach ( $module->settings->$setting as $key => $val ) {
					if ( isset( $val->$name ) ) {
						self::add_font( (array) $val->$name );
					} elseif ( $name == $key && ! empty( $val ) ) {
						self::add_font( (array) $val );
					}
				}
			} elseif ( 'typography' == $field['type'] && isset( $module->settings->$setting ) ) {
				foreach ( $module->settings->$setting as $val ) {
					if ( ! isset( $val->$name ) || empty( $val->$name ) ) {
						continue;
					}
					$typo = (array) $val->$name;
					if ( empty( $typo['font_family'] ) || 'Default' === $typo['font_family'] ) {
						continue;
					}
					$fname  = $typo['font_family'];
					$weight = isset( $typo['font_weight'] ) && '' !== $typo['font_weight'] ? $typo['font_weight'] : '400';
					// Skip google italic massaging for WP Font Library fonts (separate italic faces).
					if ( ! isset( $wp_fonts[ $fname ] ) && isset( $google[ $fname ] ) ) {
						$selected_weight = $weight;
						$italic          = isset( $typo['font_style'] ) ? $typo['font_style'] : '';
						if ( ! $italic && count( $google[ $fname ] ) === 1 && 'italic' === $google[ $fname ][0] ) {
							$italic = 'italic';
						}
						if ( in_array( $selected_weight . 'i', $google[ $fname ] ) && 'italic' == $italic ) {
							$weight = $selected_weight . 'i';
						}
						if ( ( '400' == $selected_weight || 'regular' == $selected_weight ) && 'italic' == $italic && in_array( 'italic', $google[ $fname ] ) ) {
							$weight = '400i';
						}
					}
					self::add_font( array(
						'family' => $fname,
						'weight' => $weight,
					) );
				}
			}
		}
	}

	/**
	 * Enqueue the stylesheet for fonts.
	 *
	 * @since  1.6.3
	 * @return void
	 */
	static public function enqueue_styles() {
		return false;
	}

	/**
	 * @since 2.1.3
	 */
	static public function enqueue_google_fonts() {
		/**
		 * Google fonts domain
		 * @see fl_builder_google_fonts_domain
		 */
		$google_fonts_domain = apply_filters( 'fl_builder_google_fonts_domain', 'https://fonts.googleapis.com/' );
		$google_url          = $google_fonts_domain . 'css?family=';

		/**
		 * Allow users to control what fonts are enqueued by modules.
		 * Returning array() will disable all enqueues.
		 * @see fl_builder_google_fonts_pre_enqueue
		 * @link https://docs.wpbeaverbuilder.com/beaver-builder/developer/how-to-tips/load-google-fonts-locally-gdpr
		 */
		if ( count( apply_filters( 'fl_builder_google_fonts_pre_enqueue', self::$fonts ) ) > 0 ) {

			foreach ( self::$fonts as $family => $weights ) {
				$google_url .= $family . ':' . implode( ',', $weights ) . '|';
			}

			$google_url = substr( $google_url, 0, -1 );

			/**
			 * Whether Google Fonts stylesheets should be enqueued.
			 */
			if ( true === apply_filters( 'fl_enable_google_fonts_enqueue', true ) ) {
				wp_enqueue_style( 'fl-builder-google-fonts-' . md5( $google_url ), $google_url, array() );
			}

			self::$fonts = array();
		}
	}

	/**
	 * Adds data to the $fonts array for a font to be rendered.
	 *
	 * @since  1.6.3
	 * @param  array $font an array with the font family and weight to add.
	 * @return void
	 */
	static public function add_font( $font, $bold = false ) {

		$recent_fonts_db = get_option( 'fl_builder_recent_fonts', array() );
		$recent_fonts    = array();

		if ( is_array( $font ) && isset( $font['family'] ) && isset( $font['weight'] ) && 'Default' != $font['family'] ) {

			/**
			 * Array of system font family definitions used when determining the font source.
			 */
			$system_fonts = apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system );

			/**
			 * Array of WP Font Library font family definitions used when determining the font source.
			 */
			$wp_fonts = apply_filters( 'fl_builder_font_families_wp', FLBuilderFontFamilies::wp_library() );

			// WP Font Library wins over Google on name collision: the user explicitly uploaded the font.
			if ( array_key_exists( $font['family'], $wp_fonts ) ) {
				self::add_wp_font_weight( $font['family'], $font['weight'], $bold );
			} elseif ( ! array_key_exists( $font['family'], $system_fonts ) ) {
				$google_fonts = apply_filters( 'fl_builder_font_families_google', FLBuilderFontFamilies::google() );
				// Only enqueue a Google stylesheet for fonts BB actually knows about.
				// Unknown families (e.g. a deleted WP font still referenced by a saved layout)
				// would otherwise trigger a wasted 404 against fonts.googleapis.com.
				if ( array_key_exists( $font['family'], $google_fonts ) ) {
					self::add_google_font_weight( $font['family'], $font['weight'], $bold );
				}
			}

			if ( ! isset( $recent_fonts_db[ $font['family'] ] ) ) {
				$recent_fonts[ $font['family'] ] = $font['weight'];
			}
		}

		$recent = array_merge( (array) $recent_fonts, (array) $recent_fonts_db );

		if ( isset( $_GET['fl_builder'] ) && ! empty( $recent ) && serialize( $recent ) !== serialize( $recent_fonts_db ) ) {
			FLBuilderUtils::update_option( 'fl_builder_recent_fonts', array_slice( $recent, -11 ), true );
		}
	}

	/**
	 * Routes a font/weight pair into the Google enqueue bucket.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  string $family
	 * @param  string $weight
	 * @param  bool   $bold
	 * @return void
	 */
	static private function add_google_font_weight( $family, $weight, $bold ) {
		if ( ! array_key_exists( $family, self::$fonts ) ) {
			self::$fonts[ $family ] = array( $weight );
		} elseif ( ! in_array( $weight, self::$fonts[ $family ] ) ) {
			self::$fonts[ $family ][] = $weight;
		}
		if ( $bold ) {
			self::$fonts[ $family ][] = ( strstr( $weight, 'i' ) ) ? '700i' : '700';
		}
		self::$fonts[ $family ] = array_unique( self::$fonts[ $family ] );
	}

	/**
	 * Routes a font/weight pair into the WP Font Library enqueue bucket.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  string $family
	 * @param  string $weight
	 * @param  bool   $bold
	 * @return void
	 */
	static private function add_wp_font_weight( $family, $weight, $bold ) {
		if ( ! isset( self::$wp_fonts[ $family ] ) ) {
			self::$wp_fonts[ $family ] = array();
		}
		if ( ! in_array( $weight, self::$wp_fonts[ $family ], true ) ) {
			self::$wp_fonts[ $family ][] = $weight;
		}
		if ( $bold ) {
			$bold_weight = ( strstr( $weight, 'i' ) ) ? '700i' : '700';
			if ( ! in_array( $bold_weight, self::$wp_fonts[ $family ], true ) ) {
				self::$wp_fonts[ $family ][] = $bold_weight;
			}
		}
	}

	/**
	 * Emits @font-face CSS for WP Font Library fonts referenced by the page.
	 *
	 * Only faces matching the weights actually in use are included. Output
	 * is delegated to core's wp_print_font_faces(), which wraps the rules
	 * in <style class="wp-fonts-local">. Resets the per-request bucket
	 * after emission so a second call in the same request is a no-op.
	 *
	 * @since 2.10.5
	 * @return void
	 */
	static public function enqueue_wp_fonts() {
		$builder_active = class_exists( 'FLBuilderModel' ) && FLBuilderModel::is_builder_active();

		if ( ! $builder_active && empty( self::$wp_fonts ) ) {
			return;
		}
		if ( ! function_exists( 'wp_print_font_faces' ) ) {
			self::$wp_fonts = array();
			return;
		}

		$library = FLBuilderFontFamilies::wp_library();
		$payload = $builder_active
			? self::build_wp_font_payload_all( $library )
			: self::build_wp_font_payload_used( $library, self::$wp_fonts );

		if ( ! empty( $payload ) ) {
			wp_print_font_faces( $payload );
		}

		self::$wp_fonts = array();
	}

	/**
	 * Builds a wp_print_font_faces() payload covering every family in the library.
	 * Used in builder mode so the dropdown and module preview can render any
	 * uploaded font, not just the ones currently saved into the layout.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $library
	 * @return array
	 */
	static private function build_wp_font_payload_all( $library ) {
		$payload = array();
		foreach ( $library as $family => $data ) {
			if ( empty( $data['faces'] ) ) {
				continue;
			}
			$payload[ $family ] = array_map( array( __CLASS__, 'face_to_kebab_case' ), $data['faces'] );
		}
		return $payload;
	}

	/**
	 * Builds a wp_print_font_faces() payload limited to faces actually
	 * referenced by the page being rendered.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $library
	 * @param  array $used Per-family used-weight lists.
	 * @return array
	 */
	static private function build_wp_font_payload_used( $library, $used ) {
		$payload = array();
		foreach ( $used as $family => $used_weights ) {
			if ( ! isset( $library[ $family ]['faces'] ) ) {
				continue;
			}
			$faces = self::filter_wp_faces( $library[ $family ]['faces'], $used_weights );
			if ( ! empty( $faces ) ) {
				$payload[ $family ] = array_map( array( __CLASS__, 'face_to_kebab_case' ), $faces );
			}
		}
		return $payload;
	}

	/**
	 * Keeps only the faces whose weight is referenced by the page.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $faces        Theme.json face definitions for a family.
	 * @param  array $used_weights BB-format weight strings (e.g. '400', '700i').
	 * @return array
	 */
	static private function filter_wp_faces( $faces, $used_weights ) {
		$kept = array();
		foreach ( $faces as $face ) {
			$face_weight = isset( $face['fontWeight'] ) ? (string) $face['fontWeight'] : '400';
			$face_italic = isset( $face['fontStyle'] ) && 'italic' === $face['fontStyle'];
			foreach ( FLBuilderFontFamilies::expand_weight_range( $face_weight ) as $discrete ) {
				$needle = $face_italic ? $discrete . 'i' : $discrete;
				if ( in_array( $needle, $used_weights, true ) ) {
					$kept[] = $face;
					continue 2;
				}
			}
		}
		return $kept;
	}

	/**
	 * Converts a theme.json face definition (camelCase keys) into the
	 * kebab-case shape expected by wp_print_font_faces().
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $face
	 * @return array
	 */
	static public function face_to_kebab_case( $face ) {
		$out = array();
		foreach ( $face as $key => $value ) {
			$kebab         = strtolower( preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $key ) );
			$out[ $kebab ] = $value;
		}
		return $out;
	}

	/**
	 * Returns true if the family is registered in any of the BB font sources
	 * (system, WP Font Library, or Google). Used to short-circuit CSS emission
	 * and dropdown surfaces when a saved layout references a deleted font.
	 *
	 * @since 2.10.5
	 * @param  string $family
	 * @return bool
	 */
	static public function is_known_family( $family ) {
		if ( '' === $family || 'Default' === $family ) {
			return false;
		}
		$system = apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system );
		if ( array_key_exists( $family, $system ) ) {
			return true;
		}
		$wp = apply_filters( 'fl_builder_font_families_wp', FLBuilderFontFamilies::wp_library() );
		if ( array_key_exists( $family, $wp ) ) {
			return true;
		}
		$google = apply_filters( 'fl_builder_font_families_google', FLBuilderFontFamilies::google() );
		return array_key_exists( $family, $google );
	}

	/**
	 * Filters the `fl_builder_recent_fonts` option down to families still
	 * present in one of the live font sources. Keeps deleted WP Font Library
	 * fonts (and any other dangling references) out of the Recently Used
	 * dropdown without mutating the stored option.
	 *
	 * @since 2.10.5
	 * @param  array $recent
	 * @return array
	 */
	static public function filter_recent_fonts( $recent ) {
		if ( ! is_array( $recent ) || empty( $recent ) ) {
			return array();
		}
		$filtered = array();
		foreach ( $recent as $name => $variants ) {
			if ( 'Default' === $name || self::is_known_family( $name ) ) {
				$filtered[ $name ] = $variants;
			}
		}
		return $filtered;
	}

	/**
	 * Combines all enqueued google font HTTP calls into one URL.
	 *
	 * @since  1.9.5
	 * @return void
	 */
	static public function combine_google_fonts( $content = false ) {
		global $wp_styles;

		// Check for any enqueued `fonts.googleapis.com` from BB theme or plugin
		if ( isset( $wp_styles->queue ) ) {

			/**
			 * @see fl_builder_combine_google_fonts_domain
			 */
			$google_fonts_domain   = apply_filters( 'fl_builder_combine_google_fonts_domain', '//fonts.googleapis.com/css' );
			$enqueued_google_fonts = array();
			$families              = array();
			$subsets               = array();
			$font_args             = array();

			// Collect all enqueued google fonts
			foreach ( $wp_styles->queue as $key => $handle ) {

				if ( ! isset( $wp_styles->registered[ $handle ] ) || strpos( $handle, 'fl-builder-google-fonts-' ) === false ) {
					continue;
				}

				$style_src = $wp_styles->registered[ $handle ]->src;

				if ( strpos( $style_src, 'fonts.googleapis.com/css' ) !== false ) {
					$url = wp_parse_url( $style_src );

					if ( is_string( $url['query'] ) ) {
						parse_str( $url['query'], $parsed_url );

						if ( isset( $parsed_url['family'] ) ) {

							// Collect all subsets
							if ( isset( $parsed_url['subset'] ) ) {
								$subsets[] = urlencode( trim( $parsed_url['subset'] ) );
							}

							$font_families = explode( '|', $parsed_url['family'] );
							foreach ( $font_families as $parsed_font ) {

								$get_font = explode( ':', $parsed_font );

								// Extract the font data
								if ( isset( $get_font[0] ) && ! empty( $get_font[0] ) ) {
									$family  = $get_font[0];
									$weights = isset( $get_font[1] ) && ! empty( $get_font[1] ) ? explode( ',', $get_font[1] ) : array();

									// Combine weights if family has been enqueued
									if ( isset( $enqueued_google_fonts[ $family ] ) && $weights != $enqueued_google_fonts[ $family ]['weights'] ) {
										$combined_weights                            = array_merge( $weights, $enqueued_google_fonts[ $family ]['weights'] );
										$enqueued_google_fonts[ $family ]['weights'] = array_unique( $combined_weights );
									} else {
										$enqueued_google_fonts[ $family ] = array(
											'handle'  => $handle,
											'family'  => $family,
											'weights' => $weights,
										);

									}
									// Remove enqueued google font style, so we would only have one HTTP request.
									wp_dequeue_style( $handle );
								}
							}
						}
					}
				}
			}

			// Start combining all enqueued google fonts
			if ( count( $enqueued_google_fonts ) > 0 ) {

				foreach ( $enqueued_google_fonts as $family => $data ) {
					// Collect all family and weights
					if ( ! empty( $data['weights'] ) ) {
						$families[] = $family . ':' . implode( ',', $data['weights'] );
					} else {
						$families[] = $family;
					}
				}

				if ( ! empty( $families ) ) {
					$font_args['family'] = implode( '|', $families );

					if ( ! empty( $subsets ) ) {
						$font_args['subset'] = implode( ',', $subsets );
					}

					/**
					 * Array of extra args passed to google fonts.
					 * @see fl_builder_google_font_args
					 */
					$font_args = apply_filters( 'fl_builder_google_font_args', $font_args );

					$src = add_query_arg( $font_args, $google_fonts_domain );

					// Enqueue google fonts into one URL request
					wp_enqueue_style(
						'fl-builder-google-fonts-' . md5( $src ),
						$src,
						array()
					);
					self::$enqueued_google_fonts_done = true;
					// Clears data
					$enqueued_google_fonts = array();
				}
			}
		}
		return $content;
	}

	/**
	 * Preconnect to fonts.gstatic.com to speed up google fonts.
	 * @since 2.1.5
	 */
	static public function resource_hints( $urls, $relation_type ) {
		if ( true == self::$enqueued_google_fonts_done && 'preconnect' === $relation_type ) {
			$urls[] = array(
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			);
		}
		return $urls;
	}

	/**
	 * Find font fallback, used by FLBuilderCSS
	 * @since 2.2
	 */
	static public function get_font_fallback( $font_family ) {
		$fallback = 'sans-serif';
		/**
		 * Array of default font family definitions used for font fallback lookup.
		 */
		$default = apply_filters( 'fl_builder_font_families_default', FLBuilderFontFamilies::$default );
		/**
		 * Array of system font family definitions.
		 */
		$system = apply_filters( 'fl_builder_font_families_system', FLBuilderFontFamilies::$system );
		/**
		 * Array of available Google Font family definitions.
		 */
		$google = apply_filters( 'fl_builder_font_families_google', FLBuilderFontFamilies::google() );
		foreach ( $default as $font => $data ) {
			if ( $font_family == $font && isset( $data['fallback'] ) ) {
				$fallback = $data['fallback'];
			}
		}
		foreach ( $system as $font => $data ) {
			if ( $font_family == $font && isset( $data['fallback'] ) ) {
				$fallback = $data['fallback'];
			}
		}
		foreach ( $google as $font => $data ) {
			if ( $font_family == $font ) {
				$fallback = FLBuilderFontFamilies::get_google_fallback( $font );
			}
		}
		return $fallback;
	}
}

FLBuilderFonts::init();

/**
 * Font info class for system and Google fonts.
 *
 * @class FLFontFamilies
 * @since 1.6.3
 */
// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
final class FLBuilderFontFamilies {

	/**
	 * Cache for google fonts
	 */
	static private $_google_json  = array();
	static private $_google_fonts = false;
	static private $_google_run   = 0;

	/**
	 * Cache for WP Font Library fonts. Null = not yet built, array = built (may be empty).
	 *
	 * @since 2.10.5
	 */
	static private $_wp_fonts = null;

	/**
	 * Array with a list of default font weights.
	 * @var array
	 */
	static public $default = array(
		'Default' => array(
			'default',
			'100',
			'200',
			'300',
			'400',
			'500',
			'600',
			'700',
			'800',
			'900',
		),
	);

	/**
	 * Array with a list of system fonts.
	 * @var array
	 */
	static public $system = array(
		'Helvetica' => array(
			'fallback' => 'Verdana, Arial, sans-serif',
			'weights'  => array(
				'300',
				'400',
				'700',
			),
		),
		'Verdana'   => array(
			'fallback' => 'Helvetica, Arial, sans-serif',
			'weights'  => array(
				'300',
				'400',
				'700',
			),
		),
		'Arial'     => array(
			'fallback' => 'Helvetica, Verdana, sans-serif',
			'weights'  => array(
				'300',
				'400',
				'700',
			),
		),
		'Times'     => array(
			'fallback' => 'Georgia, serif',
			'weights'  => array(
				'300',
				'400',
				'700',
			),
		),
		'Georgia'   => array(
			'fallback' => 'Times, serif',
			'weights'  => array(
				'300',
				'400',
				'700',
			),
		),
		'Courier'   => array(
			'fallback' => 'monospace',
			'weights'  => array(
				'300',
				'400',
				'700',
			),
		),
	);

	/**
	 * Parse fonts.json to get all possible Google fonts.
	 * @since 1.10.7
	 * @return array
	 */
	public static function google() {

		if ( false !== self::$_google_fonts ) {
			return self::$_google_fonts;
		}

		$fonts = array();
		$json  = self::_get_json();

		foreach ( $json as $k => $font ) {

			$name = key( $font );

			foreach ( $font[ $name ]['variants'] as $key => $variant ) {
				if ( 'italic' !== $variant ) {
					if ( stristr( $variant, 'italic' ) ) {
						$font[ $name ]['variants'][ $key ] = str_replace( 'talic', '', $variant );
					}
				}
				if ( 'regular' == $variant ) {
					$font[ $name ]['variants'][ $key ] = '400';
				}
			}
			$fonts[ $name ] = $font[ $name ]['variants'];
		}
		// only cache after 1st run to save rams.
		if ( self::$_google_run > 0 ) {
			self::$_google_fonts = $fonts;
		}
		self::$_google_run++;
		return $fonts;
	}

	/**
	 * @since 2.1.5
	 */
	static private function _get_json() {
		if ( ! empty( self::$_google_json ) ) {
			$json = self::$_google_json;
		} else {
			$json = self::try_cached_google();
			if ( ! $json ) {
				$json = (array) json_decode( file_get_contents( trailingslashit( FL_BUILDER_DIR ) . 'json/fonts.json' ), true );
			}
			self::$_google_json = $json;
		}
		/**
		 * Filter raw google json data
		 * @see fl_builder_get_google_json
		 */
		return apply_filters( 'fl_builder_get_google_json', $json );
	}

	static public function try_cached_google() {

		// Check if enabled
		if ( ! get_option( '_fl_builder_google_auto' ) ) {
			return false;
		}

		if ( false === ( $json = get_transient( 'fl_builder_google_json' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, WordPress.CodeAnalysis.AssignmentInCondition.Found,Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

			try {
				$response = wp_remote_get( 'https://updates.wpbeaverbuilder.com/fonts.json', array(
					'headers' => array(
						'Accept' => 'application/json',
					),
				) );
				if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
					$body = json_decode( $response['body'] );
					if ( json_last_error() === JSON_ERROR_NONE && isset( $body->items ) ) {
						$json = array();
						foreach ( $body->items as $font ) {
							$fallback = 'sans-serif';
							if ( 'sans-serif' === $font->category || 'serif' === $font->category || 'monospace' === $font->category || 'handwriting' === $font->category ) {
								if ( 'handwriting' === $font->category ) {
									$fallback = 'cursive';
								} else {
									$fallback = $font->category;
								}
							}
							$json[] = array(
								$font->family => array(
									'variants' => $font->variants,
									'fallback' => $fallback,
								),
							);
						}
						set_transient( 'fl_builder_google_json', $json, 604800 );
					}
				}
			} catch ( Exception $ex ) {
				return false; // TODO load default json here into transient?
			}
		}
		return $json;
	}

	/**
	 * @since 2.1.5
	 */
	static public function get_google_fallback( $font ) {
		$json = self::_get_json();
		foreach ( $json as $k => $google ) {
			$name = key( $google );
			if ( $name == $font ) {
				return $google[ $name ]['fallback'];
			}
		}
		return false;
	}

	/**
	 * Returns user-uploaded fonts from the WordPress Font Library (WP 6.5+).
	 *
	 * Reads the 'custom' origin from wp_get_global_settings(), so theme-declared
	 * fonts are excluded. The result is cached per request; cache is busted on
	 * wp_font_family / wp_font_face mutations via FLBuilderFonts::clear_wp_fonts_cache.
	 *
	 * @since 2.10.5
	 * @return array Keyed by family name. Each entry has 'weights' (sorted, deduped),
	 *               'fallback' (CSS fallback chain string), and 'faces' (raw theme.json
	 *               face definitions for downstream enqueue).
	 */
	static public function wp_library() {
		if ( null !== self::$_wp_fonts ) {
			return self::$_wp_fonts;
		}
		if ( ! function_exists( 'wp_print_font_faces' ) || ! post_type_exists( 'wp_font_family' ) ) {
			self::$_wp_fonts = array();
			return self::$_wp_fonts;
		}
		$settings        = wp_get_global_settings( array( 'typography', 'fontFamilies' ) );
		self::$_wp_fonts = self::parse_wp_library( is_array( $settings ) ? $settings : array() );
		return self::$_wp_fonts;
	}

	/**
	 * Parses the typography.fontFamilies settings shape into BB's font registry shape.
	 *
	 * Exposed for direct testing without WP global state. Reads only the 'custom'
	 * origin so theme-declared families don't double up.
	 *
	 * @since 2.10.5
	 * @param  array $settings The 'fontFamilies' settings node, keyed by origin.
	 * @return array
	 */
	static public function parse_wp_library( $settings ) {
		$custom = isset( $settings['custom'] ) && is_array( $settings['custom'] ) ? $settings['custom'] : array();
		$fonts  = array();
		foreach ( $custom as $family ) {
			if ( empty( $family['fontFace'] ) || ! is_array( $family['fontFace'] ) ) {
				continue;
			}
			$family_name = self::parse_wp_family_name( $family );
			if ( '' === $family_name ) {
				continue;
			}
			$fonts[ $family_name ] = array(
				'weights'  => self::collect_wp_weights( $family['fontFace'] ),
				'fallback' => self::parse_wp_fallback( $family ),
				'faces'    => $family['fontFace'],
			);
		}
		return $fonts;
	}

	/**
	 * Extracts the display name for a WP Font Library family.
	 *
	 * Prefers the explicit `name`; falls back to the first comma-separated
	 * token of `fontFamily` (mirrors WP_Font_Face_Resolver).
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $family
	 * @return string
	 */
	static private function parse_wp_family_name( $family ) {
		if ( ! empty( $family['name'] ) ) {
			return trim( (string) $family['name'] );
		}
		if ( empty( $family['fontFamily'] ) ) {
			return '';
		}
		$tokens = explode( ',', $family['fontFamily'] );
		return trim( $tokens[0], " \"'" );
	}

	/**
	 * Parses the CSS fallback chain from a family's fontFamily declaration.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $family
	 * @return string
	 */
	static private function parse_wp_fallback( $family ) {
		if ( empty( $family['fontFamily'] ) || false === strpos( $family['fontFamily'], ',' ) ) {
			return 'sans-serif';
		}
		$tokens = explode( ',', $family['fontFamily'] );
		array_shift( $tokens );
		$fallback = trim( implode( ', ', array_map( 'trim', $tokens ) ), ", \t\n\r" );
		return '' === $fallback ? 'sans-serif' : $fallback;
	}

	/**
	 * Collects discrete weight strings from a family's faces.
	 *
	 * Variable-font ranges ("100 900") are expanded into 100-step values.
	 * Italic faces get an 'i' suffix to match BB's existing weight convention.
	 *
	 * @since 2.10.5
	 * @access private
	 * @param  array $faces
	 * @return array
	 */
	static private function collect_wp_weights( $faces ) {
		$weights = array();
		foreach ( $faces as $face ) {
			$weight = isset( $face['fontWeight'] ) ? (string) $face['fontWeight'] : '400';
			$italic = isset( $face['fontStyle'] ) && 'italic' === $face['fontStyle'];
			foreach ( self::expand_weight_range( $weight ) as $w ) {
				$weights[] = $italic ? $w . 'i' : $w;
			}
		}
		$weights = array_values( array_unique( $weights ) );
		sort( $weights );
		return $weights;
	}

	/**
	 * Expands a CSS font-weight value into discrete weight strings.
	 *
	 * Single values pass through ("400" → ["400"]). Variable-font ranges
	 * produce a 100-step sequence inclusive of endpoints ("100 900" →
	 * ["100","200",...,"900"]). Non-numeric input is returned untouched so
	 * unusual values still surface in the UI rather than disappearing.
	 *
	 * @since 2.10.5
	 * @param  string|int $weight
	 * @return array
	 */
	static public function expand_weight_range( $weight ) {
		$weight = trim( (string) $weight );
		if ( '' === $weight ) {
			return array();
		}
		if ( false === strpos( $weight, ' ' ) ) {
			return array( $weight );
		}
		$parts = preg_split( '/\s+/', $weight, 2 );
		if ( ! is_numeric( $parts[0] ) || ! is_numeric( $parts[1] ) ) {
			return array( $weight );
		}
		$min   = (int) $parts[0];
		$max   = (int) $parts[1];
		$start = (int) ( ceil( $min / 100 ) * 100 );
		$out   = array();
		for ( $w = $start; $w <= $max; $w += 100 ) {
			$out[] = (string) $w;
		}
		return empty( $out ) ? array( (string) $min ) : $out;
	}

	/**
	 * Resets the WP Font Library registry cache.
	 *
	 * @since 2.10.5
	 * @return void
	 */
	static public function clear_wp_library_cache() {
		self::$_wp_fonts = null;
	}
}
