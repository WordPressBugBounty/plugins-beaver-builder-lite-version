<?php
$enabled          = get_option( '_fl_builder_maintenance_enabled' );
$status_503       = get_option( '_fl_builder_maintenance_503_enabled', '1' );
$layout_id        = (int) get_option( '_fl_builder_maintenance_layout_id', 0 );
$bypass_roles     = FLBuilderMaintenanceMode::get_bypass_roles();
$schedule_enabled = get_option( '_fl_builder_maintenance_schedule_enabled' );
$schedule_start   = get_option( '_fl_builder_maintenance_start', '' );
$schedule_end     = get_option( '_fl_builder_maintenance_end', '' );
$all_roles        = wp_roles()->get_names();
$timezone_string  = wp_timezone_string();
$is_active        = FLBuilderMaintenanceMode::is_enabled();
$is_scheduled     = FLBuilderMaintenanceMode::is_scheduled();

if ( $is_active ) {
	$badge_class = 'fl-tools-badge-active';
	$badge_label = __( 'Active', 'fl-builder' );
} elseif ( $is_scheduled ) {
	$badge_class = 'fl-maintenance-badge-scheduled';
	$badge_label = __( 'Scheduled', 'fl-builder' );
} else {
	$badge_class = 'fl-tools-badge-inactive';
	$badge_label = __( 'Inactive', 'fl-builder' );
}

// Build grouped layout options.
$layout_groups = array(
	__( 'Pages', 'fl-builder' )     => FLBuilderMaintenanceMode::get_layout_options( 'page' ),
	__( 'Posts', 'fl-builder' )     => FLBuilderMaintenanceMode::get_layout_options( 'post' ),
	__( 'Templates', 'fl-builder' ) => FLBuilderMaintenanceMode::get_layout_options( 'fl-builder-template' ),
);
?>
<div id="fl-maintenance-mode-form" class="fl-settings-form">

	<h3 class="fl-settings-form-header"><?php esc_html_e( 'Maintenance Mode', 'fl-builder' ); ?></h3>

	<?php wp_nonce_field( 'fl-maintenance-save', 'fl-maintenance-nonce' ); ?>

	<div class="fl-maintenance-row">
		<div class="fl-maintenance-card">
			<div class="fl-maintenance-card-header">
				<div class="fl-maintenance-card-title-row">
					<h3><?php esc_html_e( 'Enable Maintenance Mode', 'fl-builder' ); ?></h3>
					<span class="fl-tools-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_label ); ?></span>
				</div>
				<p><?php esc_html_e( 'When enabled, visitors will see the selected layout instead of your site content.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-maintenance-card-body">
				<div class="fl-maintenance-item">
					<label class="fl-toggle-switch">
						<input type="checkbox" class="fl-maintenance-enabled-toggle" value="1" <?php checked( $enabled, '1' ); ?> />
						<span><?php esc_html_e( 'Enable Maintenance Mode', 'fl-builder' ); ?></span>
					</label>
				</div>
				<div class="fl-maintenance-item">
					<label class="fl-toggle-switch">
						<input type="checkbox" class="fl-maintenance-503-toggle" value="1" <?php checked( $status_503, '1' ); ?> />
						<span><?php esc_html_e( 'Protect search rankings', 'fl-builder' ); ?><span class="fl-maintenance-info-btn" role="button" tabindex="0" aria-expanded="false" aria-controls="fl-maintenance-503-info"><span class="dashicons dashicons-info-outline"></span></span></span>
					</label>
				</div>
				<p id="fl-maintenance-503-info" class="fl-maintenance-info-text" hidden><?php esc_html_e( 'When switched on, a &ldquo;503 temporarily unavailable&rdquo; signal is sent to Google and other search engines. This tells them the site will be back soon, so they hold onto your existing rankings and don&rsquo;t replace your pages with the maintenance page in search results. Leave it on unless you have a specific reason not to.', 'fl-builder' ); ?></p>
				<div class="fl-maintenance-field">
					<label for="fl-maintenance-layout-id"><?php esc_html_e( 'Maintenance Layout', 'fl-builder' ); ?></label>
					<p class="description">
					<?php
					$builder_name = class_exists( 'FLBuilderWhiteLabel' ) ? FLBuilderWhiteLabel::get_branding() : __( 'Beaver Builder', 'fl-builder' );
					// translators: %s: Builder name
					printf( esc_html__( 'Select the page, post, or %s template to display during maintenance.', 'fl-builder' ), esc_html( $builder_name ) );
					?>
					</p>
					<select name="fl-maintenance-layout-id" id="fl-maintenance-layout-id">
						<option value="0"><?php esc_html_e( '&mdash; Select a layout &mdash;', 'fl-builder' ); ?></option>
						<?php foreach ( $layout_groups as $group_label => $posts ) : ?>
							<?php if ( ! empty( $posts ) ) : ?>
							<optgroup label="<?php echo esc_attr( $group_label ); ?>">
								<?php foreach ( $posts as $layout_post ) : ?>
								<option value="<?php echo esc_attr( $layout_post->ID ); ?>" <?php selected( $layout_id, $layout_post->ID ); ?>>
									<?php echo esc_html( $layout_post->post_title ); ?>
								</option>
								<?php endforeach; ?>
							</optgroup>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="fl-maintenance-card">
			<div class="fl-maintenance-card-header">
				<h3><?php esc_html_e( 'Bypass Roles', 'fl-builder' ); ?></h3>
				<p><?php esc_html_e( 'Users with these roles will bypass maintenance mode and see the normal site.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-maintenance-card-body">
				<?php foreach ( $all_roles as $role_slug => $role_name ) : ?>
				<div class="fl-maintenance-item">
					<label class="fl-toggle-switch">
						<input
							type="checkbox"
							class="fl-maintenance-bypass-role"
							value="<?php echo esc_attr( $role_slug ); ?>"
							<?php checked( in_array( $role_slug, $bypass_roles, true ) ); ?>
						/>
						<span><?php echo esc_html( translate_user_role( $role_name ) ); ?></span>
					</label>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="fl-maintenance-row fl-maintenance-row--full">
		<div class="fl-maintenance-card">
			<div class="fl-maintenance-card-header">
				<div class="fl-maintenance-card-title-row">
					<h3><?php esc_html_e( 'Schedule', 'fl-builder' ); ?></h3>
					<span class="fl-tools-badge <?php echo $schedule_enabled ? 'fl-tools-badge-active' : 'fl-tools-badge-inactive'; ?>">
						<?php echo $schedule_enabled ? esc_html__( 'Enabled', 'fl-builder' ) : esc_html__( 'Disabled', 'fl-builder' ); ?>
					</span>
				</div>
				<p><?php esc_html_e( 'Automatically activate maintenance mode on a schedule.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-maintenance-card-body">
				<label class="fl-toggle-switch">
					<input type="checkbox" class="fl-maintenance-schedule-toggle" id="fl-maintenance-schedule-enabled" value="1" <?php checked( $schedule_enabled, '1' ); ?> />
					<span><?php esc_html_e( 'Enable Schedule', 'fl-builder' ); ?></span>
				</label>
				<div id="fl-maintenance-schedule-fields" <?php echo $schedule_enabled ? '' : 'style="display:none;"'; ?>>
					<div class="fl-maintenance-schedule-row">
						<div class="fl-maintenance-field">
							<label for="fl-maintenance-start"><?php esc_html_e( 'Start', 'fl-builder' ); ?></label>
							<input type="datetime-local" id="fl-maintenance-start" value="<?php echo esc_attr( $schedule_start ); ?>" />
						</div>
						<div class="fl-maintenance-field">
							<label for="fl-maintenance-end"><?php esc_html_e( 'End', 'fl-builder' ); ?></label>
							<input type="datetime-local" id="fl-maintenance-end" value="<?php echo esc_attr( $schedule_end ); ?>" />
						</div>
					</div>
					<p class="description">
						<?php
						printf(
							/* translators: %s: timezone name */
							__( 'Times are in your site timezone: %s', 'fl-builder' ),
							'<strong>' . esc_html( $timezone_string ) . '</strong>'
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

</div>
