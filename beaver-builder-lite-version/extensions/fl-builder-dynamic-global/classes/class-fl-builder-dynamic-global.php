<?php

/**
 * Dynamic Global.
 *
 * @since 2.10
 */
final class FLBuilderDynamicGlobal {

	static private $current_component_node = null;
	static private $current_template_node  = null;

	/**
	 * An array of node IDs and their corresponding IDs with suffixes.
	 * Used to ensure unique IDs for multiple instance of the same dynamic
	 * node on the same page. Storing them here allows up to update parent
	 * references on child nodes when dynamic settings are merged.
	 */
	static private $node_suffix_map = [];

	/**
	 * Initializer method.
	 *
	 * @since 2.10
	 * @return void
	 */
	static public function init() {
		self::load_hooks();
	}

	static private function load_hooks() {
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_scripts' );
		add_filter( 'fl_themer_builder_connect_node_settings_cache_key', __CLASS__ . '::maybe_update_themer_cache_key', 10, 2 );
		add_filter( 'post_row_actions', __CLASS__ . '::row_actions' );
		add_action( 'admin_init', __CLASS__ . '::convert_to_component' );
	}

	static public function enqueue_scripts() {
		if ( ! FLBuilderModel::is_builder_active() ) {
			return;
		}

		$ver = FL_BUILDER_VERSION;
		wp_enqueue_script( 'fl-builder-dynamic-global', FL_BUILDER_DYNAMIC_GLOBAL_URL . 'js/fl-builder-dynamic-global.js', array( 'jquery' ), $ver );
	}

	/**
	 * Save the current Dynamic Node object as property.
	 *
	 * @since 2.10
	 * @return void
	 */
	static private function set_current_component_node( $node = null ) {
		self::$current_component_node = $node;
	}

	/**
	 * After selecting a Component, get its template node object and save as property.
	 *
	 * @since 2.10
	 * @return void
	 */
	static private function set_current_template_node( $node = null ) {
		$template_root_node = null;
		$template_post_id   = FLBuilderModel::is_node_global( $node );
		if ( $template_post_id ) {
			$template_post = get_post( $template_post_id );

			$template_node_id   = $node->template_node_id;
			$template_data      = FLBuilderModel::get_layout_data( 'published', $template_post_id );
			$template_root_node = $template_data[ $template_node_id ] ?? null;
		}

		self::$current_template_node = $template_root_node;
	}

	/**
	 * Get the Component Tabs, Sections and Fields for rendering to the Component Settings form.
	 *
	 * @since 2.10
	 * @param int node_id
	 * @param string group
	 * @return void
	 */
	static public function get_dynamic_node_tabs( $node_id = 0, $group = '' ) {
		$node                  = FLBuilderModel::get_node( $node_id );
		$dynamic_node_settings = null;
		$config                = [
			'title'          => '',
			'isEmpty'        => true,
			'dynamicEditing' => FLBuilderModel::is_node_dynamic( $node ),
			'tabs'           => [],
			'notice'         => '',
		];
		$tabs                  = [
			'content_tab'   => [
				'title'    => __( 'Content', 'fl-builder' ),
				'sections' => [],
			],
			'container_tab' => [
				'title'    => __( 'Container', 'fl-builder' ),
				'sections' => [],
			],
		];

		if ( empty( $node ) || ! $config['dynamicEditing'] ) {
			return $config;
		}

		if ( ! in_array( $node->type, [ 'row', 'column', 'module' ] ) ) {
			return $config;
		}

		self::set_current_component_node( $node );
		self::set_current_template_node( $node );

		if ( isset( $node->settings->dynamic_node_settings ) ) {
			$is_empty_root  = self::is_empty_component_root( $node->settings->dynamic_node_settings );
			$is_empty_child = self::is_empty_component_child( $node->settings->dynamic_node_settings );

			if ( $is_empty_root && $is_empty_child ) {
				$dynamic_node_settings = self::get_empty_rc_dynamic_node_settings( $node );
			} else {
				$dynamic_node_settings = self::get_saved_dynamic_node_settings( $node );
			}
		} else {
			$dynamic_node_settings = self::get_dynamic_node_settings( $node );
		}

		$config['title'] = $dynamic_node_settings['title'];

		$dynamic_sections_tabs = self::get_dynamic_node_sections( $node, $dynamic_node_settings );
		$node_sections         = $dynamic_sections_tabs['node_sections'] ?? [];
		$qs_tabs               = $dynamic_sections_tabs['query_tabs'] ?? [];

		if ( empty( $node_sections ) && empty( $qs_tabs ) ) {
			$edit_url = FLBuilderModel::get_edit_url( $dynamic_node_settings['template_post_id'] );

			$config['isEmpty'] = false;
			/* translators: %s: Add Settings URL */
			$config['notice'] = sprintf( __( 'No editable settings are defined for this component. To allow editing, add settings in the main instance. <a href="%s" class="fl-dynamic-node-edit-link">Add Settings &rarr; </a>', 'fl-builder' ), $edit_url );

			return $config;
		}

		$root_node_settings  = [];
		$child_node_settings = [];

		foreach ( $node_sections as $section_key => $section_data ) {
			if ( ! isset( $section_data['nodeId'] ) ) {
				continue;
			}

			if ( ! isset( $section_data['node_type'] ) ) {
				continue;
			}

			$target_node_id = $section_data['nodeId'];

			if ( ! empty( $section_data['fields'] ) ) {

				foreach ( (array) $section_data['fields'] as $section_field_key => $section_field_data ) {

					if ( $target_node_id === $node->node ) {
						$target_field_name = str_replace( '__' . $target_node_id . '__', '', $section_field_key );
						$setting_val       = self::get_root_node_setting_value( $node, $section_field_key );

						if ( ! is_null( $setting_val ) ) {
							$root_node_settings[ $section_field_key ] = $setting_val;

							// Add photo _src to root node settings.
							if ( isset( $section_field_data['type'] ) && 'photo' === $section_field_data['type'] ) {

								$image_src = $target_field_name . '_src';

								if ( isset( $dynamic_node_settings['root'][ $target_node_id ][ $image_src ] ) ) {
									$photo_src = $dynamic_node_settings['root'][ $target_node_id ][ $image_src ];
								} else {
									$photo_src = wp_get_attachment_image_url( $setting_val, 'full' );
								}

								$root_node_settings[ $section_field_key . '_src' ] = $photo_src;
							}
						}
					} else {
						$target_field_name = str_replace( '__' . $target_node_id . '__', '', $section_field_key );
						$setting_val       = self::get_child_setting_value( $node, $target_node_id, $target_field_name );

						if ( ! is_null( $setting_val ) ) {
							$child_node_settings[ $section_field_key ] = $setting_val;

							// Add photo _src to child node settings.
							if ( isset( $section_field_data['type'] ) && 'photo' === $section_field_data['type'] ) {

								$parts     = explode( '__', $target_node_id );
								$image_src = '__' . $parts[0] . '__' . $target_field_name . '_src';

								if ( isset( $dynamic_node_settings['child'][ $image_src ] ) ) {
									$photo_src = $dynamic_node_settings['child'][ $image_src ];
								} else {
									$photo_src = wp_get_attachment_image_url( $setting_val, 'full' );
								}

								$child_node_settings[ $section_field_key . '_src' ] = $photo_src;
							}
						}
					}
				}
			}

			// Place the section to the appropriate tab position.
			$target_tab = '';
			if ( 'row' === $section_data['node_type'] || 'column' === $section_data['node_type'] ) {
				$target_tab = 'container_tab';
			} elseif ( 'module' === $section_data['node_type'] ) {
				$target_tab = ( 'box' === $section_data['module_type'] ) ? 'container_tab' : 'content_tab';
			}

			if ( ! empty( $target_tab ) ) {
				$tabs[ $target_tab ]['sections'][ $section_key ] = $section_data;
			}
		}

		if ( empty( $tabs['container_tab']['sections'] ) ) {
			unset( $tabs['container_tab'] );
		}

		if ( empty( $tabs['content_tab']['sections'] ) ) {
			unset( $tabs['content_tab'] );
		}

		$form_settings = self::get_settings_for_dynamic_form( $dynamic_node_settings );
		$all_settings  = array_merge( $form_settings, $root_node_settings, $child_node_settings );

		// Get Query Tabs.
		if ( ! empty( $qs_tabs ) ) {
			foreach ( $qs_tabs as $qt_key => $qt_tab ) {
				$tabs[ $qt_key ] = [
					'title'         => $qt_tab['title'],
					'query_node_id' => $qt_tab['query_node_id'],
					'defaults'      => $qt_tab['defaults'],
					'group'         => $group,
					'file'          => $qt_tab['file'],
				];
			}
		}

		$config['isEmpty']               = false;
		$config['dynamicEditing']        = true;
		$config['tabs']                  = $tabs;
		$config['settings']              = $all_settings;
		$config['dynamic_node_settings'] = $dynamic_node_settings;

		return $config;
	}

	/**
	 * Get setting value from the root node.
	 *
	 * @since 2.10
	 * @param object $root_node
	 * @param string $field_name
	 * @return mixed $value
	 */
	static private function get_root_node_setting_value( $root_node, $field_name ) {
		$value = null;
		foreach ( $root_node->settings as $setting_prop => $setting_val ) {
			if ( $field_name === $setting_prop ) {
				return $setting_val;
			}
		}
		return $value;
	}

	/**
	 * Get setting value from a child node.
	 *
	 * @since 2.10
	 * @param object $root_node
	 * @param string $node_id
	 * @param string $field_name
	 * @return mixed $value
	 */
	static private function get_child_setting_value( $root_node, $node_id = '', $field_name = '' ) {
		$value             = null;
		$settings          = null;
		$categorized_nodes = FLBuilderModel::get_categorized_child_nodes( $root_node );

		if ( isset( $categorized_nodes['columns'][ $node_id ] ) ) {
			$settings = $categorized_nodes['columns'][ $node_id ]->settings;
		} elseif ( isset( $categorized_nodes['modules'][ $node_id ] ) ) {
			$settings = $categorized_nodes['modules'][ $node_id ]->settings;
		}

		if ( ! empty( $settings ) ) {
			if ( isset( $settings->{ $field_name } ) ) {
				$value = $settings->{ $field_name };

			} elseif ( isset( $settings->{ $field_name . '_unit' } ) ) {
				$value = ''; // Fix for Dimension field.
			}
		}
		return $value;
	}

	/**
	 * Get field settings to be rendered in the Dynamic Settings Form
	 *
	 * @since 2.10
	 * @param array $dynamic_node_settings
	 * @return array $settings
	 */
	static private function get_settings_for_dynamic_form( $dynamic_node_settings ) {
		$settings = [];

		$root_fields = reset( $dynamic_node_settings['root'] ) ?? [];
		foreach ( $root_fields as $field_key => $field_value ) {
			$settings[ $field_key ] = $field_value;
		}

		$child_fields = $dynamic_node_settings['child'] ?? [];
		foreach ( $child_fields as $field_key => $field_value ) {
			$settings[ $field_key ] = $field_value;
		}

		return $settings;
	}

	/**
	 * Organize the dynamic fields to each section for rendering in the Form Settings.
	 *
	 * @since 2.10
	 * @param object $node
	 * @return array
	 */
	static private function get_dynamic_node_sections( $node, $dynamic_node_settings ) {
		if ( empty( $node ) || ! in_array( $node->type, [ 'row', 'column', 'module' ] ) ) {
			return [];
		}

		$qs_tabs       = [];
		$node_sections = [];

		// Get Root Node Fields
		$root     = $dynamic_node_settings['root'];
		$root_key = $dynamic_node_settings['root_node_id'];

		if ( ! isset( $root[ $root_key ] ) && 0 < count( $root ) ) {
			$root_key = array_keys( $root )[0];
		}

		$root_settings = $root[ $root_key ];
		$root_fields   = [];
		if ( ! empty( self::$current_template_node->settings->dynamic_fields->fields ) ) {
			$root_fields = self::$current_template_node->settings->dynamic_fields->fields;
		}

		$dynamic_fields_data = [
			'dynamic_fields' => $root_fields,
			'field_data'     => [
				'type'     => $node->type,
				'node_key' => '',
			],
		];

		$dynamic_node_data = self::get_dynamic_node_data( $node, $dynamic_fields_data );
		$node_section      = self::get_section_from_fields( $node, $dynamic_node_data['selected_fields'] );

		$flattened_toggle_fields = [];

		if ( ! empty( $node_section ) ) {
			$node_sections[ 'section_' . $node->node ] = $node_section;

			if ( ! empty( $dynamic_node_data['selected_sections'] ) ) {
				$section_data         = [
					'title'       => $node_section['title'],
					'section_key' => $dynamic_fields_data['field_data']['node_key'],
					'sections'    => $dynamic_node_data['selected_sections'],
				];
				$node_toggle_sections = self::get_node_toggle_sections( $node, $section_data );

				$flattened_toggle_fields[ $node->node ] = self::get_fields_from_toggle_sections( $node_toggle_sections );
				$node_sections                          = array_merge( $node_sections, $node_toggle_sections );
			}
		}

		if ( ! empty( $dynamic_node_data['selected_tabs'] ) ) {
			$qs_tabs = array_merge( $qs_tabs, $dynamic_node_data['selected_tabs'] );
		}

		// Get Child Node Fields
		$categorized_nodes = FLBuilderModel::get_categorized_child_nodes( $node->node );
		foreach ( $categorized_nodes as $cat_key => $cat ) {
			foreach ( $cat as $node_key => $node_item ) {

				$dynamic_fields      = [];
				$is_nested_component = ! empty( $node_item->global ) && ! empty( $node_item->dynamic ) && ! empty( $node_item->template_root_node );

				if ( $is_nested_component ) {
					$template_data      = FLBuilderModel::get_layout_data( 'published', $node_item->global );
					$template_root_node = $template_data[ $node_item->template_node_id ] ?? null;
					$dynamic_fields     = $template_root_node->settings->dynamic_fields ?? [];
				} else {
					$dynamic_fields = self::get_dynamic_fields_from_template( $node_item );
				}

				if ( empty( $dynamic_fields ) ) {
					continue;
				}

				if ( is_array( $dynamic_fields ) ) {
					$dynamic_fields = (object) $dynamic_fields;
				}

				$dynamic_fields_data = [
					'dynamic_fields' => $dynamic_fields->fields,
					'field_data'     => [
						'type'     => $node_item->type,
						'node_key' => $node_key,
					],
				];

				$dynamic_node_data = self::get_dynamic_node_data( $node_item, $dynamic_fields_data );
				$node_section      = self::get_section_from_fields( $node_item, $dynamic_node_data['selected_fields'] );

				if ( ! empty( $node_section ) ) {
					$node_sections[ 'section_' . $node_item->node ] = $node_section;
				}

				if ( ! empty( $dynamic_node_data['selected_tabs'] ) ) {
					$qs_tabs = array_merge( $qs_tabs, $dynamic_node_data['selected_tabs'] );
				}

				$section_data = [
					'title'       => $node_section['title'] ?? '',
					'section_key' => $dynamic_fields_data['field_data']['node_key'],
					'sections'    => $dynamic_node_data['selected_sections'],
				];

				$node_toggle_sections = self::get_node_toggle_sections( $node_item, $section_data );

				if ( ! empty( $node_toggle_sections ) ) {
					$flattened_toggle_fields[ $node_item->node ] = self::get_fields_from_toggle_sections( $node_toggle_sections );
				}

				$node_sections = array_merge( $node_sections, $node_toggle_sections );

			}
		}

		if ( empty( $node_sections ) && empty( $qs_tabs ) ) {
			return [];
		}

		$repositioned_section_fields = self::reposition_section_fields( $node_sections );

		foreach ( $repositioned_section_fields as $rs_key => $rs_data ) {
			if ( strpos( $rs_key, 'section_' ) === false ) {
				continue;
			}

			$curr_node_id   = str_replace( 'section_', '', $rs_key );
			$section_fields = $rs_data['fields'] ?? [];
			foreach ( $section_fields as $sf_key => $sf_data ) {
				if ( in_array( $sf_key, $flattened_toggle_fields[ $curr_node_id ] ?? [] ) ) {
					unset( $repositioned_section_fields[ $rs_key ]['fields'][ $sf_key ] );
				}
			}
		}

		return [
			'query_tabs'    => $qs_tabs,
			'node_sections' => $repositioned_section_fields,
		];
	}

	/**
	 * Get fields from Toggle Sections.
	 *
	 * @since 2.10.0.1
	 * @param array $sections
	 * @return array
	 */
	static private function get_fields_from_toggle_sections( $sections ) {
		$flattened_fields = [];

		foreach ( $sections as $section_key => $section_data ) {
			$fields = $section_data['fields'] ?? [];
			if ( ! empty( $section_data['fields'] ) ) {
				$flattened_fields = array_merge( $flattened_fields, array_keys( $section_data['fields'] ) );
			}
		}

		return $flattened_fields;
	}

	/**
	 * Rearrange the given node sections.
	 *
	 * @since 2.10
	 * @param object $node_sections
	 * @return array
	 */
	static private function reposition_section_fields( $node_sections ) {
		$sorted_sections = [];

		foreach ( $node_sections as $section_key => $section_data ) {
			$sorted_sections[ $section_key ] = $section_data;

			// Unset for now, but later it will be replaced with a sorted version.
			unset( $sorted_sections[ $section_key ]['fields'] );

			$sorted_fields = [];
			foreach ( $section_data['fields'] as $fkey => $field ) {
				if ( ! array_key_exists( $fkey, $sorted_fields ) ) {
					$sorted_fields[ $fkey ] = $field;
				}

				if ( ! empty( $field['toggle'] ) ) {
					foreach ( $field['toggle'] as $ftkey => $toggle ) {
						// Check for fields, sections, tabs
						if ( ! empty( $toggle['fields'] ) ) {
							foreach ( $toggle['fields'] as $tfield_key => $tfield_name ) {
								if ( ! array_key_exists( $tfield_name, $sorted_fields ) && isset( $section_data['fields'][ $tfield_name ] ) ) {
									$sorted_fields[ $tfield_name ] = $section_data['fields'][ $tfield_name ];
								}
							}
						}
					}
				}
			}

			// For now, add the sorted fields to 'sorted_fields'.
			if ( ! empty( $sorted_fields ) ) {
				$sorted_sections[ $section_key ]['fields'] = $sorted_fields;
			}
		}

		return $sorted_sections;
	}

	/**
	 * Get sections from fields to be rendered in the Dynamic Form.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param array $node_fields
	 * @return array
	 */
	static private function get_section_from_fields( $node, $node_fields ) {
		$node_section = [];

		if ( empty( $node_fields ) ) {
			return $node_section;
		}

		if ( ! empty( $node->settings->node_label ) ) {

			$node_name = $node->settings->node_label;

		} else {

			$node_name = ucfirst( $node->type );

			if ( 'module' === $node->type ) {
				if ( isset( FLBuilderModel::$modules[ $node->settings->type ] ) ) {
					$node_name = FLBuilderModel::$modules[ $node->settings->type ]->name;
				} else {
					$node_name = ucfirst( $node->settings->type );
				}
			}
		}

		$node_section = array(
			'title'       => $node_name,
			'fields'      => $node_fields,
			'nodeId'      => $node->node,
			'node_type'   => $node->type,
			'module_type' => 'module' === $node->type ? $node->settings->type : '',
			'collapsed'   => false,
		);

		return $node_section;
	}

	/**
	 * Get the dynamic fields selected for the node.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param array $dynamic_fields_data
	 * @return array
	 */
	static private function get_dynamic_node_data( $node, $dynamic_fields_data ) {

		$field_prefix = '';
		if ( ! empty( $dynamic_fields_data['field_data']['node_key'] ) ) {
			$field_prefix = $dynamic_fields_data['field_data']['node_key'];
		}

		$toggle_sections = [];
		$selected_fields = [];
		$selected_tabs   = [];

		$fields_from_form = self::get_dynamic_fields_from_form( $node, $dynamic_fields_data );
		$prefixed_toggles = [];
		if ( ! empty( $fields_from_form['selected_fields'] ) ) {
			$prefixed_toggles = self::apply_prefix_toggles( $fields_from_form['selected_fields'], $field_prefix );
		}

		if ( ! empty( $fields_from_form['toggle_sections'] ) ) {
			$toggle_sections = array_merge( $toggle_sections, $fields_from_form['toggle_sections'] );
		}

		if ( ! empty( $fields_from_form['selected_tabs'] ) ) {
			$selected_tabs = $fields_from_form['selected_tabs'];
		}

		$selected_fields = array_merge( $selected_fields, $prefixed_toggles );

		while ( ! empty( $fields_from_form['toggle_fields'] ) ) {
			$dynamic_fields_data['dynamic_fields'] = $fields_from_form['toggle_fields'];
			$fields_from_form                      = self::get_dynamic_fields_from_form( $node, $dynamic_fields_data );

			if ( ! empty( $fields_from_form['selected_tabs'] ) ) {
				$selected_tabs = array_merge( $selected_tabs, $fields_from_form['selected_tabs'] );
			}

			$prefixed_toggles = [];
			if ( ! empty( $fields_from_form['selected_fields'] ) ) {
				$prefixed_toggles = self::apply_prefix_toggles( $fields_from_form['selected_fields'], $field_prefix );
			}
			$selected_fields = array_merge( $selected_fields, $prefixed_toggles );
		}

		return [
			'selected_fields'   => $selected_fields,
			'selected_sections' => $toggle_sections,
			'selected_tabs'     => $selected_tabs,
		];
	}

	/**
	 * Get toggle sections of a given node.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param array $section_data
	 * @return  array
	 */
	static private function get_node_toggle_sections( $node, $section_data = [] ) {

		$selected_sections = $section_data['sections'] ?? [];
		$node_sections     = [];

		if ( empty( $selected_sections ) ) {
			return [];
		}

		$form = self::get_node_form( $node );
		foreach ( $form as $tab_key => $tab ) {
			if ( empty( $tab['sections'] ) ) {
				continue;
			}
			foreach ( $tab['sections'] as $skey => $section ) {
				if ( in_array( $skey, $selected_sections ) ) {

					$prefix = empty( $section_data['section_key'] ) ? '' : '__' . $section_data['section_key'] . '__';
					$ns_key = $prefix . $skey;

					$node_sections[ $ns_key ] = [
						'fields'      => $section['fields'] ?? [],
						'title'       => $section_data['title'] . ' → ' . $section['title'],
						'nodeId'      => $node->node,
						'node_type'   => $node->type,
						'module_type' => 'module' === $node->type ? $node->settings->type : '',
						'collapsed'   => false,
					];

					if ( ! empty( $section['file'] ) ) {
						$node_sections[ $ns_key ]['file'] = $section['file'];
					}

					$ns_fields = $node_sections[ $ns_key ]['fields'];

					$selected_fields = [];

					foreach ( $node_sections[ $ns_key ]['fields'] as $key => $field ) {
						$selected_fields[ $prefix . $key ] = $field;
					}

					$prefixed_fields                    = self::apply_prefix_toggles( $selected_fields, $section_data['section_key'] );
					$node_sections[ $ns_key ]['fields'] = $prefixed_fields;

				}
			}
		}

		return $node_sections;
	}

	/**
	 * Get form given a node object.
	 *
	 * @since 2.10
	 * @param object $node
	 * @return array
	 */
	static private function get_node_form( $node ) {
		$form = null;

		if ( ! empty( $node->type ) && in_array( $node->type, [ 'row', 'column' ] ) ) {
			$form = FLBuilderModel::$settings_forms[ substr( $node->type, 0, 3 ) ]['tabs'];
		} else {
			$module_type = '';
			if ( ! empty( $node->slug ) ) {
				$module_type = $node->slug;
			} elseif ( ! empty( $node->settings->type ) ) {
				$module_type = $node->settings->type;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			} elseif ( ! empty( $node->moduleType ) ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$module_type = $node->moduleType;
			}
			if ( ! empty( $module_type ) ) {
				$form = FLBuilderModel::$modules[ $module_type ]->form;
			}
		}
		return $form;
	}

	/**
	 * Given a component node object, retrieve its Dynamic Fields from the source Template Node.
	 *
	 * @since 2.10
	 * @param object $target_node
	 * @return object
	 */
	static private function get_dynamic_fields_from_template( $target_node ) {
		$parts          = explode( '__', $target_node->node );
		$target_node_id = ( count( $parts ) >= 0 ) ? $parts[0] : $target_node->node;
		$settings       = null;

		$cur_template_categorized_nodes = FLBuilderModel::get_categorized_child_nodes( self::$current_template_node );

		if ( 'column' === $target_node->type ) {
			$settings = $cur_template_categorized_nodes['columns'][ $target_node_id ]->settings;
		} elseif ( 'module' === $target_node->type ) {
			$settings = $cur_template_categorized_nodes['modules'][ $target_node_id ]->settings;
		}

		if ( empty( $settings ) ) {
			return null;
		}

		if ( empty( $settings->dynamic_fields ) ) {
			return null;
		}

		return $settings->dynamic_fields;
	}

	/**
	 * Apply prefix to keys of toggle fields and sections.
	 *
	 * @since 2.10
	 * @param array $selected_fields
	 * @param string $prefix
	 * @return array
	 */
	static private function apply_prefix_toggles( $selected_fields, $prefix = '' ) {
		$prefix_toggles = [];

		if ( empty( $prefix ) ) {
			return $selected_fields;
		}

		foreach ( $selected_fields as $target_key => $target_field ) {

			$prefix_toggles[ $target_key ] = $target_field;

			if ( ! empty( $target_field['toggle'] ) ) {
				$new_toggle = [];
				foreach ( $target_field['toggle'] as $tk => $toggle ) {
					$new_toggle[ $tk ] = $toggle;

					// Toggle Fields
					if ( isset( $toggle['fields'] ) ) {
						foreach ( $toggle['fields'] as $k => $v ) {
							$new_toggle[ $tk ]['fields'][ $k ] = '__' . $prefix . '__' . $v;
						}
					}

					// Toggle Sections
					if ( isset( $toggle['sections'] ) ) {
						foreach ( $toggle['sections'] as $k => $v ) {
							$new_toggle[ $tk ]['sections'][ $k ] = '__' . $prefix . '__' . $v;
						}
					}
				}
				$prefix_toggles[ $target_key ]['toggle'] = $new_toggle;
			}
		}

		return $prefix_toggles;
	}

	/**
	 * Get Dynamic Fields from forms.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param array $dynamic_fields_data
	 * @return array
	 */
	static private function get_dynamic_fields_from_form( $node, $dynamic_fields_data ) {

		$form = self::get_node_form( $node );

		if ( empty( $form ) || empty( $dynamic_fields_data ) ) {
			return [];
		}

		if ( empty( $dynamic_fields_data['dynamic_fields'] ) || empty( $dynamic_fields_data['field_data'] ) ) {
			return [];
		}

		$dynamic_fields      = $dynamic_fields_data['dynamic_fields'];
		$field_data          = $dynamic_fields_data['field_data'];
		$field_node_key      = ! empty( $field_data['node_key'] ) ? $field_data['node_key'] : '';
		$selected_fields     = array();
		$preview_field_types = [ 'text', 'css', 'font', 'animation' ];
		$selected_tabs       = array();
		$qs_field            = '';
		$has_query_settings  = false;

		if ( self::is_query_module( $node, $form ) ) {
			$has_query_settings = in_array( 'data_source', $dynamic_fields );
		}

		$toggle_fields   = [];
		$toggle_sections = [];

		foreach ( $form as $tab_key => $tab ) {

			if ( empty( $tab['sections'] ) ) {
				if ( isset( $tab['file'] ) && $has_query_settings ) {
					$query_tab = self::get_node_query_tab( $node, $tab );
					if ( ! empty( $query_tab ) ) {
						$selected_tabs[ $node->node . '_query' ] = $query_tab;
					}
				}
				continue;
			}

			foreach ( $tab['sections'] as $section_key => $section ) {
				$section_title = empty( $section['title'] ) ? '' : $section['title'];

				if ( empty( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field_key => $field ) {

					if ( ! in_array( $field_key, $dynamic_fields ) ) {
						continue;
					}

					// Add Prefix (__NodeID__) to make field index unique and prevent duplicate field names.
					$index = ( '' === $field_node_key ) ? $field_key : ( '__' . $field_node_key . '__' . $field_key );

					$field['button_label'] = ! empty( $field['label'] ) ? $field['label'] : '';
					if ( ! empty( $section_title ) ) {
						$field['label'] = $section_title . ' → ' . $field['button_label'];
					}

					$selected_fields[ $index ]              = $field;
					$selected_fields[ $index ]['orig_name'] = $field_key;

					if ( ! empty( $field['toggle'] ) ) {
						foreach ( $field['toggle'] as $t_key => $t_arr ) {
							if ( ! empty( $t_arr['fields'] ) ) {
								foreach ( $t_arr['fields'] as $f_key => $f_val ) {
									if ( ! in_array( $f_val, $toggle_fields ) ) {
										$toggle_fields[] = $f_val;
									}
								}
							}
							if ( ! empty( $t_arr['sections'] ) ) {
								foreach ( $t_arr['sections'] as $s_key => $s_val ) {
									if ( ! in_array( $s_val, $toggle_sections ) ) {
										$toggle_sections[] = $s_val;
									}
								}
							}
						}
					}

					if ( ! isset( $field['preview'] ) ) {
						continue;
					}

					// Copy the original preview setting.
					$selected_fields[ $index ]['orig_preview'] = $field['preview'];

					if ( ! in_array( $field['preview']['type'], $preview_field_types ) ) {
						continue;
					}

					if ( empty( $selected_fields[ $index ]['orig_preview']['selector'] ) ) {
						$selected_fields[ $index ]['orig_preview']['selector'] = '{node}';
					}

					$orig_selectors = explode( ',', $selected_fields[ $index ]['orig_preview']['selector'] );

					if ( ! empty( $selected_fields[ $index ]['orig_preview']['selector'] ) ) {
						$orig_selectors = explode( ',', $selected_fields[ $index ]['orig_preview']['selector'] );
					}

					$new_selectors = array_map( function ( $item ) use ( $field_node_key ) {
						$node_id = empty( $field_node_key ) ? '{node}' : ".fl-node-$field_node_key";

						if ( false !== strpos( $item, $node_id ) ) {
							return $item;
						}
						if ( false !== strpos( $item, '{node}' ) ) {
							return str_replace( '{node}', $node_id, $item );
						}
						return "$node_id $item";
					}, $orig_selectors );

					$new_selector                         = join( ',', $new_selectors );
					$new_preview                          = $selected_fields[ $index ]['orig_preview'];
					$new_preview['selector']              = $new_selector;
					$selected_fields[ $index ]['preview'] = $new_preview;

				}
			}
		}

		return [
			'selected_fields' => $selected_fields,
			'toggle_fields'   => $toggle_fields,
			'toggle_sections' => $toggle_sections,
			'selected_tabs'   => $selected_tabs,
		];
	}

	/**
	 * Get Query Tab of a node in a component.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param array $tab
	 * @return array
	 */
	static public function get_node_query_tab( $node, $tab ) {
		if ( empty( $node->settings ) ) {
			return null;
		}
		if ( empty( $tab['file'] ) ) {
			return null;
		}
		$query_fields = [
			'data_source',
			'acf_repeater_key',
			'terms_taxonomy',
			'select_terms',
			'term_parent',
			'term_order',
			'term_hide_empty',
			'term_order_by',
			'term_order_by_meta_key',
			'post_type',
			'order',
			'order_by',
			'order_by_meta_key',
			'offset',
			'exclude_self',
			'users',
			'custom_field_relation',
			'custom_field',
		];

		$query_settings = clone $node->settings;
		if ( isset( $query_settings->dynamic_fields ) ) {
			unset( $query_settings->dynamic_fields );
		}

		$dynamic_node_settings = null;
		if ( isset( $query_settings->dynamic_node_settings ) ) {
			$dynamic_node_settings = clone $query_settings->dynamic_node_settings;
			unset( $query_settings->dynamic_node_settings );
		}

		$qs_defaults = [];
		if ( empty( $dynamic_node_settings ) ) {
			foreach ( $query_settings as $key => $val ) {
				if ( ! in_array( $key, $query_fields ) ) {
					unset( $query_settings->{$key} );
				}
			}
			$qs_defaults = $query_settings;
		} else {
			$parts          = explode( '__', $node->node );
			$target_node_id = ( count( $parts ) >= 0 ) ? $parts[0] : $node->node;

			if ( isset( $dynamic_node_settings->child->{ $target_node_id } ) ) {
				$qs_defaults = $dynamic_node_settings->child->{ $target_node_id };
			}
		}

		$tab_title = __( 'Query', 'fl-builder' );

		if ( ! empty( $node->settings->node_label ) ) {
			/* translators: %s: Node Label */
			$tab_title = sprintf( __( 'Query: %s', 'fl-builder' ), $node->settings->node_label );
		}

		return [
			'title'         => $tab_title,
			'query_node_id' => $node->node,
			'defaults'      => $qs_defaults,
			'file'          => $tab['file'],
		];
	}

	/**
	 * Get the existing dynamic settings that we will use to
	 * merge with new settings when saving.
	 *
	 * @since 2.10
	 * @param object $node
	 * @return object
	 */
	static public function get_dynamic_node_settings_for_save( $node ) {
		$template_post_id    = FLBuilderModel::is_node_global( $node );
		$template_post_title = '';

		if ( $template_post_id ) {
			$template_post       = get_post( $template_post_id );
			$template_post_title = isset( $template_post->post_title ) ? $template_post->post_title : '';
		}

		$saved         = isset( $node->settings->dynamic_node_settings ) ? $node->settings->dynamic_node_settings : new StdClass();
		$root          = isset( $saved->root ) ? $saved->root : new StdClass();
		$root_settings = new StdClass();

		foreach ( $root as $saved_root_settings ) {
			$root_settings = $saved_root_settings; // Get the first root node settings found.
			break;
		}

		$settings                        = new StdClass();
		$settings->root_node_id          = $node->node;
		$settings->template_post_id      = $template_post_id;
		$settings->title                 = $template_post_title;
		$settings->root                  = new StdClass();
		$settings->root->{ $node->node } = $root_settings;
		$settings->child                 = isset( $saved->child ) ? clone $saved->child : new StdClass();

		return $settings;
	}

	/**
	 * Merge dynamic settings for saving on the root node.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param object $settings
	 * @return object
	 */
	static public function merge_settings_for_save( $node, $settings ) {
		$save = self::get_dynamic_node_settings_for_save( $node );

		// Handle merging settings to save.
		foreach ( $settings as $key => $value ) {
			if ( 'dynamic_node_settings' === $key || 'connections' === $key ) {
				continue;
			}
			if ( strpos( $key, 'as_values' ) === 0 && strpos( $key, 'link-search' ) !== false ) {
				continue;
			}
			if ( '__' !== substr( $key, 0, 2 ) ) {
				// Save a setting on the root node.
				$node->settings->{ $key } = $value;

				if ( ! isset( $save->root->{ $node->node } ) ) {
					$save->root->{ $node->node } = new StdClass();
				}

				$save->root->{ $node->node }->{ $key } = $value;
			} else {
				// Save a setting for a child node.
				$parsed_key = self::parse_dynamic_child_key( $key );
				$node_id    = $parsed_key['node_id'];
				$field_name = $parsed_key['field_name'];

				if ( ! isset( $save->child->{ $node_id } ) ) {
					$save->child->{ $node_id } = new StdClass();
				}

				$save->child->{ $node_id }->{ $field_name } = $value;
			}
		}

		// Handle merging field connections.
		if ( isset( $settings->connections ) ) {
			foreach ( $settings->connections as $key => $value ) {
				if ( '__' !== substr( $key, 0, 2 ) ) {
					// Merge connections for the root node.
					if ( ! isset( $node->settings->connections ) ) {
						$node->settings->connections = [];
					}
					if ( ! isset( $save->root->{ $node->node }->connections ) ) {
						$save->root->{ $node->node }->connections = [];
					}

					$node->settings->connections[ $key ]              = $value;
					$save->root->{ $node->node }->connections[ $key ] = $value;
				} else {
					// Merge connections for a child node.
					$parsed_key = self::parse_dynamic_child_key( $key );
					$node_id    = $parsed_key['node_id'];
					$field_name = $parsed_key['field_name'];

					if ( ! isset( $save->child->{ $node_id }->connections ) ) {
						$save->child->{ $node_id }->connections = [];
					}

					$save->child->{ $node_id }->connections[ $field_name ] = $value;
				}
			}
		}

		$node->settings->dynamic_node_settings = $save;

		return $node->settings;
	}

	/**
	 * Parse the dynamic child key to get the node ID and field name.
	 *
	 * @since 2.10
	 * @param string $key
	 * @return array|null
	 */
	static public function parse_dynamic_child_key( $key ) {
		if ( empty( $key ) || '__' !== substr( $key, 0, 2 ) ) {
			return null;
		}

		$parts   = explode( '__', $key );
		$node_id = $parts[1];

		// If parts length is 4, we have a dynamic prefix added
		// in self::merge_settings_for_render.
		$field_name = 4 === count( $parts ) ? $parts[3] : $parts[2];

		return [
			'node_id'    => $node_id,
			'field_name' => $field_name,
		];
	}

	/**
	 * Check if a field connection is for a dynamic child node.
	 *
	 * @since 2.10
	 * @param string $name
	 * @return bool
	 */
	static public function is_dynamic_child_connection( $name ) {
		$parsed_key = self::parse_dynamic_child_key( $name );

		return null !== $parsed_key;
	}

	/**
	 * Get the connection for a dynamic child node.
	 *
	 * @since 2.10
	 * @param string $name
	 * @param object $settings
	 * @return object|null
	 */
	static public function get_dynamic_child_connection( $name, $settings ) {
		$parsed_key = self::parse_dynamic_child_key( $name );

		if ( null === $parsed_key ) {
			return null;
		}

		$node_id    = $parsed_key['node_id'];
		$field_name = $parsed_key['field_name'];

		if ( ! isset( $settings->dynamic_node_settings->child->{ $node_id }->connections ) ) {
			return null;
		}

		if ( ! isset( $settings->dynamic_node_settings->child->{ $node_id }->connections[ $field_name ] ) ) {
			return null;
		}

		return $settings->dynamic_node_settings->child->{ $node_id }->connections[ $field_name ];
	}

	/**
	 * Merge dynamic settings for a child node of a global template.
	 *
	 * @since 2.10
	 * @param object $parent
	 * @param object $node
	 * @param bool $add_suffix
	 * @return object
	 */
	static public function merge_child_settings( $parent, $node, $add_suffix = true ) {

		// Bail if the parent doesn't have dynamic settings.
		if ( ! isset( $parent->settings ) || ! isset( $parent->settings->dynamic_node_settings ) ) {
			return $node;
		}

		// Ensure the node has settings.
		if ( ! $node->settings ) {
			$node->settings = new StdClass();
		}

		// Clone the node and settings so we don't override the layout data.
		$node           = clone $node;
		$node->settings = clone $node->settings;

		// Store dynamic settings on the node for children to access during render.
		$dynamic                               = $parent->settings->dynamic_node_settings;
		$node->settings->dynamic_node_settings = $dynamic;

		// Merge dynamic settings for this node.
		if ( isset( $dynamic->child->{ $node->node } ) ) {
			foreach ( $dynamic->child->{ $node->node } as $key => $value ) {

				if ( 'connections' === $key && is_array( $node->settings->{ $key } ) ) {
					$node->settings->connections = array_merge( $node->settings->connections, (array) $value );
				} else {
					$node->settings->{ $key } = $value;
				}
			}
		}

		// Add a suffix to the node ID so this dynamic instance is unique on the page.
		// Without this, styles and scripts that depend on the node ID will
		// conflict with other instances of the same node.
		if ( $add_suffix ) {
			$original_node_id                           = $node->node;
			$node->node                                .= '__' . md5( json_encode( $dynamic ) );
			self::$node_suffix_map[ $original_node_id ] = $node->node;

			// Map the parent ID to an ID with a suffix if it exists.
			if ( isset( self::$node_suffix_map[ $node->parent ] ) ) {
				$node->parent = self::$node_suffix_map[ $node->parent ];
			}
		}

		return $node;
	}

	/**
	 * Merge root node dynamic settings with the main source template node.
	 *
	 * @since 2.10
	 */
	static public function merge_settings_with_template( $node, $settings ) {

		$orig_node_settings    = $settings;
		$dynamic_node_settings = isset( $orig_node_settings->dynamic_node_settings ) ? $orig_node_settings->dynamic_node_settings : null;

		if ( ! $dynamic_node_settings ) {
			return $orig_node_settings;
		}

		$parts                = explode( '__', $node->node );
		$target_node_id       = ( count( $parts ) >= 0 ) ? $parts[0] : $node->node;
		$target_node_settings = null;
		if ( isset( $dynamic_node_settings->root->{ $target_node_id } ) ) {
			$target_node_settings = $dynamic_node_settings->root->{ $target_node_id };
		} elseif ( isset( $dynamic_node_settings->child->{ $target_node_id } ) ) {
			$target_node_settings = $dynamic_node_settings->child->{ $target_node_id };
		}

		if ( empty( $target_node_settings ) ) {
			return $orig_node_settings;
		}

		$template_post_id = FLBuilderModel::is_node_global( $node );

		if ( empty( $template_post_id ) ) {
			return $orig_node_settings;
		}

		if ( empty( $node->template_node_id ) ) {
			return $orig_node_settings;
		}

		$template_node_id = $node->template_node_id;
		$template_data    = FLBuilderModel::get_layout_data( 'published', $template_post_id );
		$source_node      = isset( $template_data[ $template_node_id ] ) ? $template_data[ $template_node_id ] : null;

		if ( empty( $source_node ) ) {
			return $orig_node_settings;
		}

		$new_settings          = clone $source_node->settings;
		$source_dynamic_fields = $new_settings->dynamic_fields->fields ?? [];
		$toggles_from_source   = self::get_toggles_from_source( $source_node, $source_dynamic_fields );
		$merged_dynamic_fields = array_merge( $source_dynamic_fields, $toggles_from_source );

		foreach ( $target_node_settings as $key => $setting ) {
			/**
			 * Node label is always merged regardless if it's in dynamic fields or not.
			 * This is because it can be set in the outline panel.
			 */
			$is_node_label      = 'node_label' == $key;
			$is_dynamic_setting = ( property_exists( $target_node_settings, $key ) && in_array( $key, $merged_dynamic_fields ) );

			if ( $is_node_label || $is_dynamic_setting ) {
				$new_settings->{ $key } = $setting;

				// Add photo _src if it exists.
				if ( isset( $target_node_settings->{ $key . '_src' } ) ) {
					$new_settings->{ $key . '_src' } = $target_node_settings->{ $key . '_src' };
				}
			}
		}

		if ( isset( $orig_node_settings->connections ) ) {
			$new_settings->connections = $orig_node_settings->connections;
		}

		$new_settings->dynamic_node_settings = $orig_node_settings->dynamic_node_settings;
		return self::merge_dynamic_query_settings( $node, $new_settings );
	}

	/**
	 * Merge Query Settings of component.
	 * @since 2.10.0.1
	 */
	static public function merge_dynamic_query_settings( $node, $settings ) {

		if ( ! isset( $node->settings->dynamic_node_settings ) ) {
			return $settings;
		}

		$new_settings = clone $settings;
		$form         = self::get_node_form( $node );

		if ( ! empty( $form ) && self::is_query_module( $node, $form ) ) {
			$parts                 = explode( '__', $node->node );
			$target_node_id        = ( count( $parts ) >= 0 ) ? $parts[0] : $node->node;
			$dynamic_node_settings = $new_settings->dynamic_node_settings;
			$query_settings        = [];

			if ( isset( $dynamic_node_settings->child->{ $target_node_id } ) ) {
				$query_settings = $dynamic_node_settings->child->{ $target_node_id };
			}

			foreach ( $query_settings as $field_name => $field_value ) {
				if ( property_exists( $new_settings, $field_name ) ) {
					$new_settings->{ $field_name } = $field_value;
				}
			}
		}

		return $new_settings;
	}

	/**
	 * Get toggle fields and sections from source component.
	 *
	 * @since 2.10.0.1
	 * @param object $node
	 * @param array $dynamic_fields
	 * @return array
	 */
	static private function get_toggles_from_source( $node, $dynamic_fields = [] ) {

		$form = self::get_node_form( $node );

		if ( empty( $form ) || empty( $dynamic_fields ) ) {
			return [];
		}

		$toggle_fields   = [];
		$toggle_sections = [];

		foreach ( $form as $tab_key => $tab ) {

			if ( empty( $tab['sections'] ) ) {
				continue;
			}

			foreach ( $tab['sections'] as $section_key => $section ) {

				if ( empty( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field_key => $field ) {

					if ( ! in_array( $field_key, $dynamic_fields ) ) {
						continue;
					}

					if ( empty( $field['toggle'] ) ) {
						continue;
					}

					foreach ( $field['toggle'] as $tk => $toggle ) {
						if ( ! empty( $toggle['sections'] ) ) {
							$toggle_sections = array_merge( $toggle_sections, $toggle['sections'] );
						}
						if ( ! empty( $toggle['fields'] ) ) {
							$toggle_fields = array_merge( $toggle_fields, $toggle['fields'] );
						}
					}
				}
			}
		}

		$section_fields = [];
		if ( ! empty( $toggle_sections ) ) {
			$section_fields = self::get_section_fields( $form, $toggle_sections );
		}

		return array_merge( $toggle_fields, $toggle_sections, $section_fields );
	}

	/**
	 * Get fields from reference sections.
	 *
	 * @since 2.10.0.1
	 * @param array $form
	 * @param array $reference_sections
	 * @return array
	 */
	static private function get_section_fields( $form = [], $reference_sections = [] ) {
		$section_fields = [];

		foreach ( $form as $tab_key => $tab ) {

			if ( empty( $tab['sections'] ) ) {
				continue;
			}

			foreach ( $tab['sections'] as $section_key => $section ) {

				if ( ! in_array( $section_key, $reference_sections ) ) {
					continue;
				}

				if ( empty( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field_key => $field ) {
					$section_fields[] = $field_key;
				}
			}
		}

		return $section_fields;
	}

	/**
	 * Update the Themer cache key with a suffix for dynamic nodes since
	 * it's possible for more than one of the same node can be on the page.
	 * Without this, Themer settings caching will cause all nodes to use
	 * the settings from the first one rendered.
	 *
	 * @since 2.10
	 * @param string $cache_key
	 * @param object $settings
	 * @param object $node
	 * @return string
	 */
	static public function maybe_update_themer_cache_key( $cache_key, $settings ) {
		if ( isset( $settings->dynamic_node_settings ) ) {
			$cache_key .= '_' . md5( json_encode( $settings->dynamic_node_settings ) );
		}

		return $cache_key;
	}

	/**
	 * Get the Dynamic Node Settings. This is to be saved as a hidden field ( 'dynamic_node_settings' )
	 * in the Form Settings.
	 *
	 * @since 2.10
	 * @param object $node
	 * @return array
	 */
	static private function get_dynamic_node_settings( $node ) {
		$root_node_settings  = array();
		$child_settings      = array();
		$template_data       = [];
		$template_root_node  = null;
		$template_post_title = '';
		$template_post_id    = FLBuilderModel::is_node_global( $node );
		if ( $template_post_id ) {
			$template_post       = get_post( $template_post_id );
			$template_post_title = isset( $template_post->post_title ) ? $template_post->post_title : '';

			$template_node_id   = $node->template_node_id;
			$template_data      = FLBuilderModel::get_layout_data( 'published', $template_post_id );
			$template_root_node = $template_data[ $template_node_id ] ?? '';

			$root_settings          = [];
			$template_root_settings = self::get_dynamic_node_fields( $template_root_node );
			foreach ( $template_root_settings as $key => $setting ) {
				$root_settings[ $key ] = $node->settings->{ $key } ?? '';
			}

			$root_node_settings[ $node->node ] = $root_settings;
		}

		$categorized_nodes = FLBuilderModel::get_categorized_child_nodes( $template_root_node );
		foreach ( $categorized_nodes as $cat_key => $cat ) {
			foreach ( $cat as $node_key => $node_item ) {
				if ( empty( $node_item->settings->dynamic_fields->fields ) ) {
					continue;
				}
				$child_node_settings = self::get_dynamic_node_fields( $node_item );

				foreach ( $child_node_settings as $key => $setting ) {
					$child_settings[ '__' . $node_key . '__' . $key ] = $node_item->settings->{ $key } ?? '';
				}
			}
		}

		return [
			'root_node_id'     => $node->node,
			'template_post_id' => $template_post_id,
			'title'            => $template_post_title,
			'root'             => $root_node_settings,
			'child'            => $child_settings,
		];
	}

	/**
	 * Get the Dynamic Node Settings that's already saved in the root node.
	 *
	 * @since 2.10
	 * @param object $node
	 * @return array
	 */
	static private function get_saved_dynamic_node_settings( $node ) {

		if ( empty( $node->settings->dynamic_node_settings ) ) {
			return [];
		}

		$dynamic_node_settings = $node->settings->dynamic_node_settings;
		$template_post_title   = '';
		$template_post_id      = FLBuilderModel::is_node_global( $node );

		if ( $template_post_id ) {
			$template_post       = get_post( $template_post_id );
			$template_post_title = isset( $template_post->post_title ) ? $template_post->post_title : '';
		}

		$root_obj      = $dynamic_node_settings->root;
		$root_settings = [];
		foreach ( $root_obj as $root_key => $root_node ) {
			$root_settings[ $root_key ] = [];
			foreach ( $root_node as $key => $value ) {
				if ( 'connections' === $key ) {
					continue;
				}
				$root_settings[ $root_key ][ $key ] = $value;
			}
		}

		$categorized_nodes = FLBuilderModel::get_categorized_child_nodes( $node );

		$child_obj      = $dynamic_node_settings->child;
		$child_settings = [];
		foreach ( $child_obj as $child_node_key => $child_node ) {
			if ( ! is_object( $child_node ) ) {
				continue;
			}
			foreach ( $child_node as $field_key => $field_value ) {
				if ( 'connections' === $field_key ) {
					continue;
				}

				// Get Hashed Key and Value from the Categorized node?
				$hashed_setting = self::get_hashed_child_setting( $categorized_nodes, $child_node_key, $field_key );
				if ( ! empty( $hashed_setting ) ) {
					$child_settings[ $hashed_setting['hashed_field'] ] = $hashed_setting['value'];
				}
			}
		}

		return [
			'root_node_id'     => $node->node,
			'template_post_id' => $template_post_id,
			'title'            => $template_post_title,
			'root'             => $root_settings,
			'child'            => $child_settings,
		];
	}

	/**
	 * Get the Dynamic Node Settings from empty root and child settings.
	 *
	 * @since 2.10
	 * @param object $node
	 * @return array
	 */
	static private function get_empty_rc_dynamic_node_settings( $node ) {
		$dynamic_node_settings = self::get_dynamic_node_settings( $node );
		$child_node_keys       = array_keys( self::get_dynamic_child_nodes( $node ) );

		// Pick the node keys from the child nodes and use them
		// as child keys in the dynamic node settings.
		$new_child = [];
		foreach ( $dynamic_node_settings['child'] as $key => $value ) {
			foreach ( $child_node_keys as $cn_key ) {
				$parts = explode( '__', $cn_key );
				if ( strpos( $key, $parts[0] ) !== false ) {
					$new_key               = str_replace( $parts[0], $cn_key, $key );
					$new_child[ $new_key ] = $value;
				}
			}
		}
		$dynamic_node_settings['child'] = $new_child;

		return $dynamic_node_settings;
	}

	/**
	*
	* Get the field value from the child node settings.
	*
	* @since 2.10
	* @param object $categorized_nodes
	* @param object $target_node_key
	* @param object $target_field_key
	* @return array
	*/
	static private function get_hashed_child_setting( $categorized_nodes, $target_node_key, $target_field_key ) {
		$field_data           = null;
		$found_hashed_node_id = '';
		$found_field_value    = null;

		foreach ( $categorized_nodes as $cat_key => $cat ) {
			foreach ( $cat as $hashed_node_id => $node_item ) {
				$node_key  = '';
				$node_hash = '';
				$parts     = explode( '__', $hashed_node_id );

				if ( count( $parts ) >= 2 ) {
					$node_key  = $parts[0];
					$node_hash = $parts[1];
				} else {
					$node_key = $hashed_node_id;
				}

				if ( $node_key === $target_node_key ) {
					$found_hashed_node_id = $hashed_node_id;
					if ( isset( $node_item->settings->{ $target_field_key } ) ) {
						$field_data = [
							'hashed_field' => '__' . $found_hashed_node_id . '__' . $target_field_key,
							'value'        => $node_item->settings->{ $target_field_key },
						];
						return $field_data;
					}
				}
			}
		}

		return $field_data;
	}

	/**
	*
	* Get the dynamic fields of the target node.
	*
	* @since 2.10
	* @param object $node
	* @return array $field_data
	*/
	static private function get_dynamic_node_fields( $node ) {
		$field_data = [];
		$fields     = $node->settings->dynamic_fields->fields ?? [];

		foreach ( $fields as $index => $field ) {
			if ( is_string( $field ) ) {
				$field_data[ $field ] = $node->settings->{ $field } ?? null;
			}
		}

		return $field_data;
	}

	/**
	 *
	 * If already set to hidden, keep dynamic node hidden even when
	 * the builder is active when editing a layout.
	 *
	 * @since 2.10
	 * @param object $target_node
	 * @return boolean
	 */
	static public function hide_dynamic_node( $target_node ) {
		global $wp_the_query;

		$hide_target = false;
		$post_id     = FLBuilderModel::get_post_id();
		$active      = FLBuilderModel::is_builder_active() && $post_id == $wp_the_query->post->ID;
		$visible     = FLBuilderModel::is_node_visible( $target_node );

		if ( get_post_type( $post_id ) === 'fl-builder-template' ) {
			return false;
		}

		if ( $active && ! $visible ) {
			$data = FLBuilderModel::get_layout_data( 'published', $post_id );
			foreach ( $data as $node_id => $node ) {
				if ( isset( $target_node->template_id ) && isset( $node->template_id ) && $target_node->template_id === $node->template_id ) {
					$hide_target = true;
					break;
				}
			}
		}

		return $hide_target;
	}

	/**
	 * Get all dynamic child nodes of a parent node.
	 *
	 * @since 2.10
	 * @param object $parent_node
	 * @return array
	 */
	static public function get_dynamic_child_nodes( $parent_node ) {

		if ( empty( $parent_node->dynamic ) ) {
			return [];
		}

		$categorized_nodes = FLBuilderModel::get_categorized_child_nodes( $parent_node );
		return array_merge( $categorized_nodes['rows'], $categorized_nodes['columns'], $categorized_nodes['modules'] );
	}

	static public function row_actions( $actions = array() ) {
		global $post;

		if ( isset( $_GET['post_type'] ) && 'fl-builder-template' === $_GET['post_type'] ) {
			$is_global  = FLBuilderModel::is_post_global_node_template( $post->ID );
			$is_dynamic = FLBuilderModel::is_post_dynamic_editing_node_template( $post->ID );

			if ( $is_global && ! $is_dynamic ) {
				$url = add_query_arg( array(
					'post_type'            => $post->post_type,
					'post_id'              => $post->ID,
					'template_type'        => isset( $_GET['fl-builder-template-type'] ) ? $_GET['fl-builder-template-type'] : 'layout',
					'convert_to_component' => true,
					'component_nonce'      => wp_create_nonce( 'component_nonce' ),
				), admin_url() );

				$message                            = __( 'Do you really want to convert this Global to a Component? This cannot be undone.', 'fl-builder' );
				$actions['fl-convert-to-component'] = '<a href="' . $url . '" onclick="return confirm(\'' . $message . '\')">' . __( 'Convert to Component', 'fl-builder' ) . '</a>';
			}
		}

		return $actions;
	}

	static public function convert_to_component() {
		if ( isset( $_GET['convert_to_component'] ) ) {
			$post_id       = absint( $_GET['post_id'] );
			$post_type     = $_GET['post_type'];
			$template_type = $_GET['template_type'];
			$nonce         = $_GET['component_nonce'];

			if ( wp_verify_nonce( $nonce, 'component_nonce' ) ) {
				update_post_meta( $post_id, '_fl_builder_template_dynamic_editing', true );
				$url = add_query_arg( array(
					'post_type'                => $post_type,
					'fl-builder-template-type' => $template_type,
				), admin_url( 'edit.php' ) );
				wp_redirect( $url );
				exit;
			} else {
				wp_die( 'Unauthorized' );
			}
		}
	}

	/**
	 * Check if the Component's root node is empty.
	 * Note: Root node is empty if there's no settings or only the node label setting.
	 *
	 * @since 2.10
	 * @param object $dn_settings
	 * @return boolean
	 */
	static private function is_empty_component_root( $dn_settings = null ) {

		if ( empty( $dn_settings ) && ! is_object( $dn_settings ) ) {
			return true;
		}

		$root_node_id = $dn_settings->root_node_id;
		$root_empty   = empty( $dn_settings->root->{ $root_node_id } );

		if ( ! $root_empty && count( (array) $dn_settings->root->{ $root_node_id } ) <= 1 ) {
			return true;
		}
		return $root_empty;
	}

	/**
	 * Check if the Component's child node is empty.
	 *
	 * @since 2.10
	 * @param object $dn_settings
	 * @return boolean
	 */
	static private function is_empty_component_child( $dn_settings ) {

		if ( empty( $dn_settings ) && ! is_object( $dn_settings ) ) {
			return true;
		}

		$child_empty = empty( $dn_settings->child );

		if ( ! $child_empty && count( (array) $dn_settings->child ) <= 0 ) {
			return true;
		}
		return $child_empty;
	}

	/**
	 * Checks if a node module has Query Settings.
	 *
	 * @since 2.10
	 * @param object $node
	 * @param array $form
	 * @return bool
	 */
	static private function is_query_module( $node, $form = [] ) {
		if ( ! isset( $node->type ) ) {
			return false;
		}

		if ( 'module' !== $node->type ) {
			return false;
		}

		if ( ! isset( $node->form ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( isset( $node->moduleType ) && 'loop' === $node->moduleType ) {
				return true;
			}
		}

		$query_setting_found = false;
		foreach ( $form as $tab_key => $tab ) {
			if ( isset( $tab['file'] ) && FL_BUILDER_DIR . 'includes/loop-settings.php' === $tab['file'] ) {
				return true;
			}
		}
		return $query_setting_found;
	}
}

FLBuilderDynamicGlobal::init();
