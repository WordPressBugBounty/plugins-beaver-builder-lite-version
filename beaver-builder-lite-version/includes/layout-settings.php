<?php

FLBuilder::register_settings_form(
	'layout',
	array(
		'title' => __( 'Layout Settings', 'fl-builder' ),
		'tabs'  => array(
			'css'  => array(
				'title'    => __( 'CSS', 'fl-builder' ),
				'sections' => array(
					'css' => array(
						'title'  => '',
						'fields' => array(
							'css' => array(
								'type'    => 'code',
								'label'   => '',
								'editor'  => 'css',
								'rows'    => '18',
								'preview' => array(
									'type' => 'none',
								),
							),
						),
					),
				),
			),
			'js'   => array(
				'title'    => __( 'JavaScript', 'fl-builder' ),
				'sections' => array(
					'js' => array(
						'title'  => '',
						'fields' => array(
							'js' => array(
								'type'    => 'code',
								'label'   => '',
								'editor'  => 'javascript',
								'rows'    => '18',
								'preview' => array(
									'type' => 'none',
								),
							),
						),
					),
				),
			),
			'post' => array(
				'title'    => __( 'Post Settings', 'fl-builder' ),
				'sections' => array(
					'general'    => array(
						'title'  => __( 'General', 'fl-builder' ),
						'fields' => array(
							'title'   => array(
								'type'  => 'text',
								'label' => __( 'Title', 'fl-builder' ),
							),
							'excerpt' => array(
								'type'        => 'textarea',
								'label'       => __( 'Description / Excerpt', 'fl-builder' ),
								'rows'        => 4,
								'description' => __( 'Used by themes, RSS feeds, and SEO plugins as the page description.', 'fl-builder' ),
							),
							'slug'    => array(
								'type'        => 'text',
								'label'       => __( 'Slug', 'fl-builder' ),
								'description' => __( 'The URL-friendly version of the title. Changing this will reload the builder.', 'fl-builder' ),
							),
							'status'  => array(
								'type'    => 'select',
								'label'   => __( 'Status', 'fl-builder' ),
								'default' => 'publish',
								'options' => array(
									'publish' => __( 'Published', 'fl-builder' ),
									'draft'   => __( 'Draft', 'fl-builder' ),
									'pending' => __( 'Pending Review', 'fl-builder' ),
									'private' => __( 'Private', 'fl-builder' ),
								),
							),
						),
					),
					'featured'   => array(
						'title'  => __( 'Featured Image', 'fl-builder' ),
						'fields' => array(
							'featured_image' => array(
								'type'        => 'photo',
								'label'       => __( 'Featured Image', 'fl-builder' ),
								'show_remove' => true,
								'connections' => array( 'photo' ),
							),
						),
					),
					'attributes' => array(
						'title'  => __( 'Attributes', 'fl-builder' ),
						'fields' => array(
							'parent'        => array(
								'type'   => 'suggest',
								'action' => 'fl_as_posts',
								'data'   => 'page',
								'limit'  => 1,
								'label'  => __( 'Parent Page', 'fl-builder' ),
							),
							'page_template' => array(
								'type'    => 'select',
								'label'   => __( 'Page Template', 'fl-builder' ),
								'default' => 'default',
								'options' => FLBuilderLayoutPostSettings::get_page_templates(),
							),
							'menu_order'    => array(
								'type'    => 'unit',
								'label'   => __( 'Order', 'fl-builder' ),
								'default' => '0',
							),
						),
					),
					'discussion' => array(
						'title'  => __( 'Discussion', 'fl-builder' ),
						'fields' => array(
							'comment_status' => array(
								'type'    => 'select',
								'label'   => __( 'Allow Comments', 'fl-builder' ),
								'default' => 'closed',
								'options' => array(
									'open'   => __( 'Yes', 'fl-builder' ),
									'closed' => __( 'No', 'fl-builder' ),
								),
							),
							'ping_status'    => array(
								'type'    => 'select',
								'label'   => __( 'Allow Pingbacks & Trackbacks', 'fl-builder' ),
								'default' => 'closed',
								'options' => array(
									'open'   => __( 'Yes', 'fl-builder' ),
									'closed' => __( 'No', 'fl-builder' ),
								),
							),
						),
					),
				),
			),
		),
	)
);
