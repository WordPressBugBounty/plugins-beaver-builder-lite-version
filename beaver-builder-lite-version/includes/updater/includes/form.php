<?php // @codingStandardsIgnoreFile ?>
<div class="wrap">

	<?php if ( isset( $subscription->error ) && 'connection' == $subscription->error ) : ?>
	<div class="fl-license-notice fl-license-notice-error">
		<span class="dashicons dashicons-warning"></span>
		<div class="fl-license-notice-content">
			<strong><?php _e( 'Connection Error', 'fl-builder' ); ?></strong>
			<p><?php _e( 'We were unable to connect to the update server. If the issue persists, please contact your host and let them know your website cannot connect to updates.wpbeaverbuilder.com.', 'fl-builder' ); ?></p>
		</div>
	</div>
	<?php elseif ( ! empty( $license ) && ( isset( $subscription->error ) || ! $subscription->active ) ) : ?>
	<?php // Only when a key is on file. With no license, the entry form below is
	// the priority, so we skip the alarming notice rather than nag on the page
	// that exists to add a key. ?>
	<div class="fl-license-notice fl-license-notice-error">
		<span class="dashicons dashicons-warning"></span>
		<div class="fl-license-notice-content">
			<strong><?php _e( 'Your Beaver Builder license expired', 'fl-builder' ); ?></strong>
			<p><?php _e( 'Beaver Builder will keep running. The updates and support that keep this site stable are paused until the license is renewed.', 'fl-builder' ); ?></p>
			<ul style="margin:8px 0 12px; padding:0; list-style:none;">
				<?php
				$fl_renewal_bullets = array(
					__( 'Updates for new WordPress and PHP versions', 'fl-builder' ),
					__( "Bug fixes that won't break the rest of the site", 'fl-builder' ),
					__( 'Real-human support when something needs untangling', 'fl-builder' ),
				);
				foreach ( $fl_renewal_bullets as $fl_renewal_bullet ) :
				?>
					<li style="padding:2px 0 2px 16px; position:relative; font-size:13px;">
						<span style="position:absolute; left:0; color:#d63638; font-weight:700;">&bull;</span>
						<?php echo esc_html( $fl_renewal_bullet ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<p style="margin:0 0 8px;">
				<a href="<?php echo FLBuilderModel::get_store_url( '', array(
					'utm_medium' => 'bb-pro',
					'utm_source' => 'license-settings-page',
					'utm_campaign' => 'license-expired',
				) ); ?>" target="_blank" style="display:inline-block; background:#EE521F; color:#fff; text-decoration:none; font-weight:600; font-size:13px; padding:7px 16px; border-radius:6px; border:1px solid #d4471a; line-height:1.4;"><?php _e( 'Renew License', 'fl-builder' ); ?></a>
			</p>
			<p style="margin:8px 0 0; color:#787c82; font-size:12.5px;">
				<?php _e( 'Not your license? Contact whoever set up Beaver Builder to renew it.', 'fl-builder' ); ?>
			</p>
		</div>
	</div>
	<?php elseif ( ! empty( $license ) && ! $subscription->domain->active ) : ?>
	<div class="fl-license-notice fl-license-notice-error">
		<span class="dashicons dashicons-warning"></span>
		<div class="fl-license-notice-content">
			<strong><?php _e( 'Domain Deactivated', 'fl-builder' ); ?></strong>
			<p>
				<?php _e( 'Your subscription is active but this domain has been deactivated. Please reactivate this domain in your account to enable automatic updates.', 'fl-builder' ); ?>
				<a href="<?php echo FLBuilderModel::get_store_url( 'my-account', array(
					'utm_medium' => 'bb-pro',
					'utm_source' => 'license-settings-page',
					'utm_campaign' => 'license-deactivated',
				) ); ?>" target="_blank"><?php _e( 'Visit Account', 'fl-builder' ); ?> &raquo;</a>
			</p>
		</div>
	</div>
	<?php endif; ?>

	<div class="fl-license-status-card">
		<div class="fl-license-status-header">
			<h3><?php _e( 'Updates &amp; Support Subscription', 'fl-builder' ); ?></h3>
			<?php if ( isset( $subscription->error ) || ! $subscription->active ) : ?>
				<span class="fl-license-badge fl-license-badge-inactive"><?php _e( 'Not Active', 'fl-builder' ); ?></span>
			<?php elseif ( ! $subscription->domain->active ) : ?>
				<span class="fl-license-badge fl-license-badge-inactive"><?php _e( 'Deactivated', 'fl-builder' ); ?></span>
			<?php else : ?>
				<span class="fl-license-badge fl-license-badge-active"><?php _e( 'Active', 'fl-builder' ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( isset( $_POST['fl-updater-nonce'] ) ) : ?>
		<div class="updated">
			<p><?php _e( 'License key saved!', 'fl-builder' ); ?></p>
		</div>
		<?php endif; ?>

		<?php if ( isset( $subscription->subscriptions ) && ! empty( $subscription->subscriptions ) ) : ?>
		<div class="fl-subscription-list">
			<?php
			foreach ( $subscription->subscriptions as $sub ) {
				$name = '';
				if ( stristr( $sub->name, 'Beaver Builder' ) ) {
					if ( class_exists( 'FLBuilderWhiteLabel' ) && FLBuilderWhiteLabel::is_white_labeled() ) {
						$name = FLBuilderWhiteLabel::get_branding();
					} else {
						foreach( (array) $subscription->downloads as $possible ) {
							if ( stristr( $possible, 'Beaver Builder Plugin' ) ) {
								$name = $possible;
							}
						}
					}
				} else {
					$name = $sub->name;
					if ( stristr( $sub->name, 'Beaver Themer' ) && class_exists( 'FLBuilderWhiteLabel' ) && FLBuilderWhiteLabel::is_white_labeled() ) {
						// translators: %s: Builder brand name
					$name = sprintf( __( '%s - Themer Add-On', 'fl-builder' ), FLBuilderWhiteLabel::get_branding() );
					}
				}
				$expires = date_i18n( get_option( 'date_format' ), strtotime( $sub->expires ) );
				printf(
					'<div class="fl-subscription-item"><span class="fl-subscription-name">%s</span><span class="fl-subscription-expires">%s %s</span></div>',
					esc_html( $name ),
					esc_html__( 'Expires', 'fl-builder' ),
					esc_html( $expires )
				);
			}
			?>
		</div>
		<?php endif; ?>

		<?php if ( ! $subscription->active ) : ?>
		<p class="fl-license-help-text">
			<?php echo sprintf( __( 'Enter your <a%s>license key</a> to enable remote updates and support.', 'fl-builder' ), ' href="' . FLBuilderModel::get_store_url( 'my-account', array(
				'utm_medium' => 'bb-pro',
				'utm_source' => 'license-settings-page',
				'utm_campaign' => 'license-key-link',
			) ) . '" target="_blank"' ) ?>
		</p>
		<?php endif; ?>

		<?php if ( is_multisite() ) : ?>
		<p class="fl-license-multisite-note">
			<span class="dashicons dashicons-info-outline"></span>
			<?php _e( 'This applies to all sites on the network.', 'fl-builder' ); ?>
		</p>
		<?php endif; ?>

		<form class="fl-license-form" action="" method="post" <?php if ( ! empty( $license ) ) { echo 'style="display:none;"';} ?>>
			<div class="fl-license-input-group">
				<input type="password" name="license" value="" class="regular-text" placeholder="<?php esc_attr_e( 'Enter your license key...', 'fl-builder' ); ?>" />
				<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save License Key', 'fl-builder' ); ?>">
			</div>
			<?php wp_nonce_field( 'updater-nonce', 'fl-updater-nonce' ); ?>
		</form>

		<div class="fl-new-license-form" <?php if ( empty( $license ) ) { echo 'style="display:none;"';} ?>>
			<input type="button" class="button button-primary" value="<?php ( $subscription->active ) ? esc_attr_e( 'Change License Key', 'fl-builder' ) : esc_attr_e( 'Enter License Key', 'fl-builder' ); ?>">
		</div>
	</div>

	<?php
	/**
	 * Fires after the license form is rendered.
	 *
	 * @since 1.0
	 */
	do_action( 'fl_after_license_form'); ?>
	<?php FLUpdater::render_subscriptions( $subscription ); ?>

</div>
