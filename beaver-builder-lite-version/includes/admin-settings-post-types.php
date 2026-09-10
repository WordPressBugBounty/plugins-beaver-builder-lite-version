<div id="fl-post-types-form" class="fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'Post Types', 'fl-builder' ); ?></h3>
	<p><?php _e( 'Choose which post types the builder can be used on.', 'fl-builder' ); ?></p>
	<div class="fl-post-types-notice">
		<p><span class="dashicons dashicons-info-outline"></span><?php _e( 'Not all custom post types may be supported.', 'fl-builder' ); ?></p>
	</div>

	<form id="post-types-form" action="<?php FLBuilderAdminSettings::render_form_action( 'post-types' ); ?>" method="post">

		<?php if ( FLBuilderAdminSettings::multisite_support() && ! is_network_admin() ) : ?>
		<label>
			<input class="fl-override-ms-cb" type="checkbox" name="fl-override-ms" value="1" <?php echo ( get_option( '_fl_builder_post_types' ) ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'Override network settings?', 'fl-builder' ); ?>
		</label>
		<?php endif; ?>

		<div class="fl-settings-form-content">

			<?php if ( is_network_admin() ) : ?>

				<p><?php _e( 'Enter a comma separated list of the post types you would like the builder to work with.', 'fl-builder' ); ?></p>
				<p><?php _e( 'NOTE: Not all custom post types may be supported.', 'fl-builder' ); ?></p>
				<?php

				$saved_post_types = FLBuilderModel::get_post_types();

				foreach ( $saved_post_types as $key => $post_type ) {
					if ( 'fl-builder-template' == $post_type ) {
						unset( $saved_post_types[ $key ] );
					}
				}

				$saved_post_types = implode( ', ', $saved_post_types );

				?>
				<input type="text" name="fl-post-types" value="<?php echo esc_html( $saved_post_types ); ?>" class="regular-text" />
				<p class="description"><?php _e( 'Example: page, post, product', 'fl-builder' ); ?></p>

			<?php else : ?>

				<?php

				$saved_post_types = FLBuilderModel::get_post_types();
				$post_types       = get_post_types( array(
					'public' => true,
				), 'objects' );
				/**
				 * Use this filter to modify the post types that are shown in the admin settings for enabling and disabling post types.
				 * @link https://docs.wpbeaverbuilder.com/beaver-builder/developer/tutorials-guides/common-beaver-builder-filter-examples
				 * @see fl_builder_admin_settings_post_types
				 */
				$post_types = apply_filters( 'fl_builder_admin_settings_post_types', $post_types );

				?>
				<div class="fl-toggle-group">
					<h3 class="fl-toggle-group-header"><?php _e( 'Available Post Types', 'fl-builder' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></h3>
					<div class="fl-toggle-group-content fl-post-types-grid">
					<?php
					foreach ( $post_types as $post_type ) :

						$checked = in_array( $post_type->name, $saved_post_types ) ? 'checked' : '';

						if ( 'attachment' == $post_type->name ) {
							continue;
						}
						if ( 'fl-builder-template' == $post_type->name ) {
							continue;
						}

						?>
						<p>
							<label class="fl-toggle-switch">
								<input class="fl-module-cb" type="checkbox" name="fl-post-types[]" value="<?php echo $post_type->name; ?>" <?php echo $checked; ?> />
								<span><?php echo $post_type->labels->name; ?></span>
							</label>
						</p>
					<?php endforeach; ?>
					</div>
				</div>

			<?php endif; ?>

		</div>
		<?php if ( is_network_admin() ) : ?>
		<p class="submit">
			<input type="submit" name="update" class="button-primary" value="<?php esc_attr_e( 'Save Post Types', 'fl-builder' ); ?>" />
		</p>
		<?php endif; ?>
		<?php wp_nonce_field( 'post-types', 'fl-post-types-nonce' ); ?>
	</form>
</div>
