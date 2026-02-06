<?php

// Default Settings
$defaults = array(
	'data_source' => 'custom_query',
	'post_type'   => 'post',
	'order_by'    => 'date',
	'order'       => 'DESC',
	'offset'      => 0,
	'users'       => '',
	'type'        => '',
);

$tab_group     = isset( $tab['group'] ) ? $tab['group'] : '';
$tab_defaults  = [];
$query_node_id = '';
$prefix        = '';

if ( 'dynamic' === $tab_group ) {
	$orig_tab_defaults = isset( $tab['defaults'] ) ? (array) $tab['defaults'] : array();
	$query_node_id     = isset( $tab['query_node_id'] ) ? $tab['query_node_id'] : '';
	$prefix            = empty( $query_node_id ) ? '' : '__' . $query_node_id . '__';

	foreach ( $orig_tab_defaults as $k => $v ) {
		$tab_defaults[ $prefix . $k ] = $v;
	}
}

$settings = (object) array_merge( $defaults, $tab_defaults, (array) $settings );
/**
 * Allow extension of default Values
 * @see fl_builder_loop_settings
 */
$settings = apply_filters( 'fl_builder_loop_settings', $settings );

/**
 * e.g Add custom FLBuilder::render_settings_field()
 * @see fl_builder_loop_settings_before_form
 */
do_action( 'fl_builder_loop_settings_before_form', $settings );

?>
<div id="fl-builder-settings-section-source" class="fl-loop-data-source-select fl-builder-settings-section">
	<table class="fl-form-table">
	<?php
	// Data Source
	$options = array(
		'custom_query' => __( 'Custom Query', 'fl-builder' ),
		'main_query'   => __( 'Main Query', 'fl-builder' ),
	);

	if ( 'loop' === $settings->type ) {
		$options['taxonomy_query'] = __( 'Taxonomy Query', 'fl-builder' );
	}
	FLBuilder::render_settings_field( $prefix . 'data_source', array(
		'type'    => 'select',
		'label'   => __( 'Source', 'fl-builder' ),
		'default' => 'custom_query',
		'options' => $options,
		'toggle'  => array(
			'custom_query' => array(
				'fields' => array( 'posts_per_page' ),
			),
		),
	), $settings, $data );

	?>
	</table>
</div>

<div class="fl-custom-query fl-loop-data-source" data-source="acf_repeater">
	<div id="fl-builder-settings-section-general" class="fl-builder-settings-section">
		<div class="fl-builder-settings-section-header">
			<button class="fl-builder-settings-title">
				<svg width="20" height="20">
					<use xlink:href="#fl-builder-forms-down-caret"></use>
				</svg>
				<?php _e( 'ACF Repeater', 'fl-builder' ); ?>
			</button>
		</div>

		<div class="fl-builder-settings-section-content">
			<table class="fl-form-table">
			<?php
				// ACF Repeater Key
				FLBuilder::render_settings_field( $prefix . 'acf_repeater_key', array(
					'type'  => 'text',
					'label' => __( 'Key', 'fl-builder' ),
				), $settings, $data );
				?>
			</table>
		</div>
	</div>
</div>

<div class="fl-custom-query fl-loop-data-source" data-source="taxonomy_query">
	<div id="fl-builder-settings-section-general" class="fl-builder-settings-section">
		<div class="fl-builder-settings-section-header">
			<button class="fl-builder-settings-title">
				<svg width="20" height="20">
					<use xlink:href="#fl-builder-forms-down-caret"></use>
				</svg>
				<?php _e( 'Taxonomy', 'fl-builder' ); ?>
			</button>
		</div>

		<div class="fl-builder-settings-section-content">
			<table class="fl-form-table">
			<?php
				$terms_taxonomy = isset( $settings->terms_taxonomy ) ? $settings->terms_taxonomy : 'category';
				// Taxonomy
				FLBuilder::render_settings_field( $prefix . 'terms_taxonomy', array(
					'type'    => 'select',
					'label'   => __( 'Taxonomy', 'fl-builder' ),
					'default' => 'category',
					'options' => FLBuilderLoop::get_taxonomy_options(),
				), $settings, $data );

				FLBuilder::render_settings_field( $prefix . 'select_terms', array(
					'label'       => __( 'Select Terms to Display', 'fl-builder' ),
					'type'        => 'button-group',
					'fill_space'  => true,
					'allow_empty' => false,
					'default'     => 'all',
					'help'        => __( 'Only hierarchical taxonomies allow for creating child terms.', 'fl-builder' ),
					'options'     => array(
						'all'   => __( 'All Terms', 'fl-builder' ),
						'top'   => __( 'Top Level Terms', 'fl-builder' ),
						'child' => __( 'Child Terms', 'fl-builder' ),
					),
					'toggle'      => array(
						'child' => array(
							'fields' => [ 'term_parent' ],
						),
					),
				), $settings);

				// Parent term
				FLBuilder::render_settings_field( $prefix . 'term_parent', array(
					'type'    => 'select',
					'label'   => __( 'Parent Term', 'fl-builder' ),
					'help'    => __( 'Selecting None will show all terms.', 'fl-builder' ),
					'default' => 0,
					'options' => FLBuilderLoop::get_term_options( $terms_taxonomy ),
				), $settings, $data );

				// Order
				FLBuilder::render_settings_field( $prefix . 'term_order', array(
					'type'    => 'select',
					'label'   => __( 'Order', 'fl-builder' ),
					'default' => 'ASC',
					'options' => array(
						'ASC'  => __( 'Ascending', 'fl-builder' ),
						'DESC' => __( 'Descending', 'fl-builder' ),
					),
				), $settings, $data );

				FLBuilder::render_settings_field( $prefix . 'term_hide_empty', array(
					'type'    => 'select',
					'label'   => __( 'Hide Empty', 'fl-builder' ),
					'default' => '1',
					'help'    => __( 'Hide terms that don\'t have any posts.', 'fl-builder' ),
					'options' => array(
						'1' => __( 'Yes', 'fl-builder' ),
						'0' => __( 'No', 'fl-builder' ),
					),
				), $settings, $data );

				FLBuilder::render_settings_field( $prefix . 'term_order_by', array(
					'type'    => 'select',
					'label'   => __( 'Order By', 'fl-builder' ),
					'default' => 'name',
					'options' => array(
						'name'           => __( 'Name', 'fl-builder' ),
						'count'          => __( 'Term Count', 'fl-builder' ),
						'id'             => __( 'ID', 'fl-builder' ),
						'meta_value'     => __( 'Meta Value (Alphabetical)', 'fl-builder' ),
						'meta_value_num' => __( 'Meta Value (Numeric)', 'fl-builder' ),
						'parent'         => __( 'Parent', 'fl-builder' ),
					),
					'toggle'  => array(
						'meta_value'     => array(
							'fields' => array( 'term_order_by_meta_key' ),
						),
						'meta_value_num' => array(
							'fields' => array( 'term_order_by_meta_key' ),
						),
					),
				), $settings, $data );

				FLBuilder::render_settings_field( $prefix . 'term_order_by_meta_key', array(
					'type'  => 'text',
					'label' => __( 'Meta Key', 'fl-builder' ),
				), $settings, $data );
				?>
			</table>
		</div>
	</div>
</div>

<div class="fl-custom-query fl-loop-data-source" data-source="custom_query">
	<div id="fl-builder-settings-section-general" class="fl-builder-settings-section">
		<div class="fl-builder-settings-section-header">
			<button class="fl-builder-settings-title">
				<svg width="20" height="20">
					<use href="#fl-builder-forms-down-caret" />
				</svg>
				<?php _e( 'Custom Query', 'fl-builder' ); ?>
			</button>
		</div>

		<div class="fl-builder-settings-section-content">
			<table class="fl-form-table">
			<?php

			// Post type
			FLBuilder::render_settings_field( $prefix . 'post_type', array(
				'type'         => 'post-type',
				'label'        => __( 'Post Type', 'fl-builder' ),
				'multi-select' => true,
			), $settings, $data );

			// Order
			FLBuilder::render_settings_field( $prefix . 'order', array(
				'type'    => 'select',
				'label'   => __( 'Order', 'fl-builder' ),
				'options' => array(
					'DESC' => __( 'Descending', 'fl-builder' ),
					'ASC'  => __( 'Ascending', 'fl-builder' ),
				),
			), $settings, $data );

			// Order by
			FLBuilder::render_settings_field( $prefix . 'order_by', array(
				'type'    => 'select',
				'label'   => __( 'Order By', 'fl-builder' ),
				'options' => array(
					'author'         => __( 'Author', 'fl-builder' ),
					'comment_count'  => __( 'Comment Count', 'fl-builder' ),
					'date'           => __( 'Date', 'fl-builder' ),
					'modified'       => __( 'Date Last Modified', 'fl-builder' ),
					'ID'             => __( 'ID', 'fl-builder' ),
					'menu_order'     => __( 'Menu Order', 'fl-builder' ),
					'meta_value'     => __( 'Meta Value (Alphabetical)', 'fl-builder' ),
					'meta_value_num' => __( 'Meta Value (Numeric)', 'fl-builder' ),
					'rand'           => __( 'Random', 'fl-builder' ),
					'title'          => __( 'Title', 'fl-builder' ),
					'post__in'       => __( 'Selection Order', 'fl-builder' ),
				),
				'toggle'  => array(
					'meta_value'     => array(
						'fields' => array( 'order_by_meta_key' ),
					),
					'meta_value_num' => array(
						'fields' => array( 'order_by_meta_key' ),
					),
				),
			), $settings, $data );

			// Meta Key
			FLBuilder::render_settings_field( $prefix . 'order_by_meta_key', array(
				'type'  => 'text',
				'label' => __( 'Meta Key', 'fl-builder' ),
			), $settings, $data );

			// Offset
			FLBuilder::render_settings_field( $prefix . 'offset', array(
				'type'        => 'unit',
				'label'       => _x( 'Offset', 'How many posts to skip.', 'fl-builder' ),
				'default'     => '0',
				'placeholder' => '0',
				'sanitize'    => 'absint',
				'slider'      => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 2,
				),
				'help'        => __( 'Skip this many posts that match the specified criteria.', 'fl-builder' ),
			), $settings, $data );

			FLBuilder::render_settings_field( $prefix . 'exclude_self', array(
				'type'    => 'select',
				'label'   => __( 'Exclude Current Post', 'fl-builder' ),
				'default' => 'no',
				'help'    => __( 'Exclude the current post from the query.', 'fl-builder' ),
				'options' => array(
					'yes' => __( 'Yes', 'fl-builder' ),
					'no'  => __( 'No', 'fl-builder' ),
				),
			), $settings, $data );
			?>
			</table>
		</div>
	</div>
	<div id="fl-builder-settings-section-filter" class="fl-builder-settings-section">
		<div class="fl-builder-settings-section-header">
			<button class="fl-builder-settings-title">
				<svg width="20" height="20">
					<use href="#fl-builder-forms-down-caret" />
				</svg>
				<?php _e( 'Filter', 'fl-builder' ); ?>
			</button>
		</div>
		<div class="fl-builder-settings-section-content">
		<?php foreach ( FLBuilderLoop::post_types() as $slug => $type ) : ?>
			<table class="fl-form-table fl-custom-query-filter fl-custom-query-<?php echo $slug; ?>-filter"<?php echo ( $slug == $settings->post_type ) ? ' style="display:table;"' : ''; ?>>
			<?php

			// Posts
			FLBuilder::render_settings_field(  $prefix . 'posts_' . $slug, array(
				'type'     => 'suggest',
				'action'   => 'fl_as_posts',
				'data'     => $slug,
				'label'    => $type->label,
				/* translators: %s: type label */
				'help'     => sprintf( __( 'Enter a list of %1$s.', 'fl-builder' ), $type->label ),
				'matching' => true,
			), $settings, $data );

			// Taxonomies
			$taxonomies = FLBuilderLoop::taxonomies( $slug );

			$field_settings = new stdClass();
			foreach ( $settings as $k => $setting ) {
				if ( false !== strpos( $k, 'tax_' . $slug ) ) {
					$field_settings->$k = $setting;
				}
			}
			foreach ( $taxonomies as $tax_slug => $tax ) {

				$field_key = 'tax_' . $slug . '_' . $tax_slug;

				if ( isset( $settings->$field_key ) ) {
					$field_settings->$field_key = $settings->$field_key;
				}

				FLBuilder::render_settings_field( $prefix . $field_key, array(
					'type'     => 'suggest',
					'action'   => 'fl_as_terms',
					'data'     => $tax_slug,
					'label'    => $tax->label,
					/* translators: %s: tax label */
					'help'     => sprintf( __( 'Enter a list of %1$s.', 'fl-builder' ), $tax->label ),
					'matching' => true,
				), $field_settings, $data );
			}
			?>
			</table>
			<?php endforeach; ?>
			<table class="fl-form-table">
			<?php
			// Author
			FLBuilder::render_settings_field( $prefix . 'users', array(
				'type'     => 'suggest',
				'action'   => 'fl_as_users',
				'label'    => __( 'Authors', 'fl-builder' ),
				'help'     => __( 'Enter a list of authors usernames.', 'fl-builder' ),
				'matching' => true,
			), $settings, $data );
			?>
			</table>
		</div>
	</div>

	<div id="fl-builder-settings-section-filter" class="fl-builder-settings-section">
		<div class="fl-builder-settings-section-header">
			<button class="fl-builder-settings-title">
				<svg width="20" height="20">
					<use href="#fl-builder-forms-down-caret" />
				</svg>
				<?php _e( 'Custom Field Filter', 'fl-builder' ); ?>
			</button>
		</div>

		<div class="fl-builder-settings-section-content">
			<table class="fl-form-table">
			<?php
				FLBuilder::render_settings_field( $prefix . 'custom_field_relation', array(
					'type'    => 'select',
					'label'   => __( 'Relation', 'fl-builder' ),
					'default' => 'AND',
					'options' => array(
						'AND' => __( 'AND', 'fl-builder' ),
						'OR'  => __( 'OR', 'fl-builder' ),
					),
				), $settings, $data );
				?>
			</table>
			<table class="fl-form-table">
			<?php
				FLBuilder::render_settings_field( $prefix . 'custom_field', array(
					'type'         => 'form',
					'help'         => __( 'Custom field key.', 'fl-builder' ),
					'label'        => __( 'Custom Field', 'fl-builder' ),
					'form'         => 'custom_fields_form',
					'default'      => array( 0 => '' ),
					'preview_text' => 'filter_meta_label',
					'multiple'     => true,
				), $settings, $data );
				?>
			</table>
		</div>
	</div>
</div>
<?php
do_action( 'fl_builder_loop_settings_after_form', $settings ); // e.g Add custom FLBuilder::render_settings_field()
