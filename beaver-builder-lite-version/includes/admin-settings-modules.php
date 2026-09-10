<?php
if ( FLBuilder::is_module_disable_enabled() ) {
	$used_modules = array();

	$args = array(
		'post_type'      => FLBuilderModel::get_post_types(),
		'post_status'    => 'publish',
		'meta_key'       => '_fl_builder_enabled',
		'meta_value'     => '1',
		'posts_per_page' => -1,
	);

	$query           = new WP_Query( $args );
	$data['enabled'] = count( $query->posts );

	/**
	* Using the array of pages/posts using builder get a list of all used modules
	*/
	if ( is_array( $query->posts ) && ! empty( $query->posts ) ) {
		foreach ( $query->posts as $post ) {
			$meta = get_post_meta( $post->ID, '_fl_builder_data', true );
			foreach ( (array) $meta as $node_id => $node ) {
				if ( @isset( $node->type ) && 'module' === $node->type ) { // @codingStandardsIgnoreLine
					if ( ! isset( $used_modules[ $node->settings->type ][ $post->post_type ] ) ) {
						$used_modules[ $node->settings->type ][ $post->post_type ] = array();
					}

					if ( ! isset( $used_modules[ $node->settings->type ][ $post->post_type ][ $post->ID ] ) ) {
						$used_modules[ $node->settings->type ][ $post->post_type ][ $post->ID ] = 1;
					} else {
						$used_modules[ $node->settings->type ][ $post->post_type ][ $post->ID ]++;
					}


					if ( ! isset( $used_modules[ $node->settings->type ][ $post->post_type ]['total'] ) ) {
						$used_modules[ $node->settings->type ][ $post->post_type ]['total'] = 1;
					} else {
						$used_modules[ $node->settings->type ][ $post->post_type ]['total']++;
					}
				}
			}
		}
	}
}

?>
<div id="fl-modules-form" class="fl-settings-form">
	<h3 class="fl-settings-form-header"><?php _e( 'Modules', 'fl-builder' ); ?></h3>
	<p><?php _e( 'Enable or disable modules available in the builder interface.', 'fl-builder' ); ?></p>

	<form id="modules-form" action="<?php FLBuilderAdminSettings::render_form_action( 'modules' ); ?>" method="post">

		<?php if ( FLBuilderAdminSettings::multisite_support() && ! is_network_admin() ) : ?>
		<label>
			<input class="fl-override-ms-cb" type="checkbox" name="fl-override-ms" value="1" <?php echo ( get_option( '_fl_builder_enabled_modules' ) ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'Override network settings?', 'fl-builder' ); ?>
		</label>
		<?php endif; ?>

		<div class="fl-settings-form-content">

			<p><?php _e( 'Toggle modules below to enable or disable them.', 'fl-builder' ); ?></p>
			<?php

			$categories              = FLBuilderModel::get_categorized_modules( true );
			$enabled_modules         = FLBuilderModel::get_enabled_modules();
			$deprecated              = FLBuilderModel::get_deprecated_modules();
			$checked                 = in_array( 'all', $enabled_modules ) ? 'checked' : '';
			$deprecated_modules_html = array();
			$usage_enabled           = FLBuilder::is_module_disable_enabled();

			?>
			<div class="fl-modules-controls">
				<label class="fl-toggle-switch fl-modules-all-toggle">
					<input class="fl-module-all-cb" type="checkbox" name="fl-modules[]" value="all" <?php echo $checked; ?> />
					<span><?php _ex( 'All', 'Plugin setup page: Modules.', 'fl-builder' ); ?></span>
				</label>
				<?php if ( $usage_enabled ) : ?>
				<div class="fl-module-filter-bar">
					<button type="button" class="fl-module-filter active" data-filter="all"><?php _e( 'All', 'fl-builder' ); ?></button>
					<button type="button" class="fl-module-filter" data-filter="in-use"><?php _e( 'In Use', 'fl-builder' ); ?></button>
					<button type="button" class="fl-module-filter" data-filter="not-used"><?php _e( 'Not Used', 'fl-builder' ); ?></button>
				</div>
				<?php endif; ?>
				<div class="fl-module-search-wrap">
					<span class="dashicons dashicons-search"></span>
					<input type="text" class="fl-module-search" placeholder="<?php esc_attr_e( 'Search modules...', 'fl-builder' ); ?>" />
				</div>
			</div>
			<?php
			foreach ( $categories as $title => $modules ) :

				if ( __( 'WordPress Widgets', 'fl-builder' ) == $title ) :
					// WordPress Widgets is deprecated — collect for the deprecated section
					$checked     = in_array( 'widget', $enabled_modules ) ? 'checked' : '';
					$module_name = esc_html( $title );
					$usage_html  = '';
					$usage_attr  = '';
					if ( $usage_enabled ) {
						$text = 'Not used';
						if ( isset( $used_modules['widget'] ) ) {
							$txt = array();
							foreach ( $used_modules['widget'] as $type => $used ) {
								$type  = str_replace( 'fl-theme-layout', 'Themer Layout', $type );
								$type  = str_replace( 'fl-builder-template', 'Builder Template', $type );
								$txt[] = sprintf( '%s times on %s %ss', $used['total'], count( $used ) - 1, ucfirst( $type ) );
							}
							$text = implode( ', ', $txt );
						}
						$not_used_class = ( 'Not used' === $text ) ? ' fl-module-not-used' : '';
						$usage_html     = sprintf( '<span class="fl-module-usage%s">%s</span>', $not_used_class, esc_html( $text ) );
						$usage_attr     = ( 'Not used' === $text ) ? ' data-module-usage="not-used"' : ' data-module-usage="in-use"';
					}
					$deprecated_modules_html[] = sprintf(
						'<p%s><label class="fl-toggle-switch"><input class="fl-module-cb" type="checkbox" name="fl-modules[]" value="widget" %s /><span>%s%s</span></label></p>',
						$usage_attr,
						$checked,
						$module_name,
						$usage_html
					);
					continue;
				endif;

				// Separate deprecated and active modules within this category
				$active_modules    = array();
				$deprecated_in_cat = array();

				foreach ( $modules as $module ) {
					if ( in_array( $module->slug, $deprecated ) ) {
						$deprecated_in_cat[] = $module;
					} else {
						$active_modules[] = $module;
					}
				}

				// Only show category if there are active modules
				if ( ! empty( $active_modules ) ) :
					$all_in_group = true;
					foreach ( $active_modules as $m ) {
						if ( ! in_array( $m->slug, $enabled_modules ) ) {
							$all_in_group = false;
							break;
						}
					}
					$group_checked = ( in_array( 'all', $enabled_modules ) || $all_in_group ) ? 'checked' : '';
					?>
			<div class="fl-toggle-group">
				<h3 class="fl-toggle-group-header">
					<label class="fl-toggle-switch">
						<input type="checkbox" class="fl-group-toggle-cb" <?php echo $group_checked; ?> />
						<span><?php echo $title; ?> <span class="fl-toggle-group-count"><?php echo count( $active_modules ); ?></span></span>
					</label>
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</h3>
				<div class="fl-toggle-group-content">
					<?php
					foreach ( $active_modules as $module ) :
						$checked    = in_array( $module->slug, $enabled_modules ) ? 'checked' : '';
						$text       = '';
						$usage_attr = '';
						if ( $usage_enabled ) {
							$text = 'Not used';
							if ( isset( $used_modules[ $module->slug ] ) ) {
								$txt = array();
								foreach ( $used_modules[ $module->slug ] as $type => $used ) {
									$type  = str_replace( 'fl-theme-layout', 'Themer Layout', $type );
									$type  = str_replace( 'fl-builder-template', 'Builder Template', $type );
									$txt[] = sprintf( '%s times on %s %ss', $used['total'], count( $used ) - 1, ucfirst( $type ) );
								}
								$text = implode( ', ', $txt );
							}
							$usage_attr = ( 'Not used' === $text ) ? ' data-module-usage="not-used"' : ' data-module-usage="in-use"';
						}
						?>
				<p<?php echo $usage_attr; ?>>
					<label class="fl-toggle-switch">
						<input class="fl-module-cb" type="checkbox" name="fl-modules[]" value="<?php echo $module->slug; ?>" <?php echo $checked; ?> />
						<span>
							<?php echo esc_html( $module->name ); ?>
							<?php if ( $usage_enabled ) : ?>
								<span class="fl-module-usage <?php echo ( 'Not used' === $text ) ? 'fl-module-not-used' : ''; ?>"><?php echo esc_html( $text ); ?></span>
							<?php endif; ?>
						</span>
					</label>
				</p>
					<?php endforeach; ?>
				</div>
			</div>
				<?php endif; ?>

				<?php
				// Collect deprecated modules for the bottom section
				foreach ( $deprecated_in_cat as $module ) {
					$checked    = in_array( $module->slug, $enabled_modules ) ? 'checked' : '';
					$text       = 'Not used';
					$usage_attr = '';
					if ( isset( $used_modules[ $module->slug ] ) ) {
						$txt = array();
						foreach ( $used_modules[ $module->slug ] as $type => $used ) {
							$type  = str_replace( 'fl-theme-layout', 'Themer Layout', $type );
							$type  = str_replace( 'fl-builder-template', 'Builder Template', $type );
							$txt[] = sprintf( '%s times on %s %ss', $used['total'], count( $used ) - 1, ucfirst( $type ) );
						}
						$text = implode( ', ', $txt );
					}
					$module_name = esc_html( $module->name );
					$usage_html  = '';
					if ( $usage_enabled ) {
						$not_used_class = ( 'Not used' === $text ) ? ' fl-module-not-used' : '';
						$usage_html     = sprintf( '<span class="fl-module-usage%s">%s</span>', $not_used_class, esc_html( $text ) );
						$usage_attr     = ( 'Not used' === $text ) ? ' data-module-usage="not-used"' : ' data-module-usage="in-use"';
					}
					$deprecated_modules_html[] = sprintf(
						'<p%s><label class="fl-toggle-switch"><input class="fl-module-cb" type="checkbox" name="fl-modules[]" value="%s" %s /><span>%s%s</span></label></p>',
						$usage_attr,
						esc_attr( $module->slug ),
						$checked,
						$module_name,
						$usage_html
					);
				}
				?>
			<?php endforeach; ?>

			<?php if ( ! empty( $deprecated_modules_html ) ) : ?>
			<div class="fl-toggle-group fl-modules-deprecated">
				<h3 class="fl-toggle-group-header"><?php _e( 'Deprecated', 'fl-builder' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
				<div class="fl-toggle-group-content">
				<p class="fl-modules-deprecated-desc"><?php _e( 'These modules are no longer actively maintained and may be removed in a future release.', 'fl-builder' ); ?></p>
				<?php echo implode( "\n", $deprecated_modules_html ); ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<p class="submit">
			<input type="submit" name="update" class="button-primary" value="<?php esc_attr_e( 'Save Module Settings', 'fl-builder' ); ?>" />
			<?php wp_nonce_field( 'modules', 'fl-modules-nonce' ); ?>
		</p>
	</form>
</div>
