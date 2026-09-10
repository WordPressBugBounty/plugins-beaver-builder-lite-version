<?php $raw_settings = FLBuilderUserAccess::get_raw_settings(); ?>
<div id="fl-user-access-form" class="fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'User Access Settings', 'fl-builder' ); ?></h3>
	<p class="fl-user-access-intro"><?php _e( 'Use these settings to limit which builder features users can access.', 'fl-builder' ); ?></p>

	<?php wp_nonce_field( 'user-access', 'fl-user-access-nonce' ); ?>

	<div class="fl-settings-form-content">
		<?php foreach ( FLBuilderUserAccess::get_grouped_registered_settings() as $group => $group_data ) : ?>

			<div class="fl-user-access-card">
				<div class="fl-user-access-card-header">
					<h3><?php echo esc_html( $group ); ?></h3>
					<p>
						<?php
						printf(
							/* translators: %s: group name */
							esc_html__( 'Configure which user roles can access %s features.', 'fl-builder' ),
							esc_html( $group )
						);
						?>
					</p>
				</div>
				<div class="fl-user-access-card-body">
					<div class="fl-user-access-settings-grid">
						<?php foreach ( $group_data as $cap => $cap_data ) : ?>
							<div class="fl-user-access-setting">
								<div class="fl-user-access-setting-header">
									<h4><?php echo esc_html( $cap_data['label'] ); ?></h4>
									<?php if ( ! empty( $cap_data['description'] ) ) : ?>
										<p class="fl-user-access-description"><?php echo wp_kses( $cap_data['description'], array( 'code' => array() ) ); ?></p>
									<?php endif; ?>
								</div>
								<?php if ( FLBuilderAdminSettings::multisite_support() && ! is_network_admin() ) : ?>
								<label class="fl-toggle-switch fl-ua-override-ms-label">
									<input class="fl-ua-override-ms-cb" type="checkbox" name="fl_ua_override_ms[<?php echo esc_attr( $cap ); ?>]" value="1" <?php echo ( isset( $raw_settings[ $cap ] ) ) ? 'checked' : ''; ?> />
									<span><?php _e( 'Override network settings?', 'fl-builder' ); ?></span>
								</label>
								<?php endif; ?>
								<select name="fl_user_access[<?php echo esc_attr( $cap ); ?>][]" class="fl-user-access-select" multiple data-capability="<?php echo esc_attr( $cap ); ?>"></select>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

		<?php endforeach; ?>
	</div>
</div>
