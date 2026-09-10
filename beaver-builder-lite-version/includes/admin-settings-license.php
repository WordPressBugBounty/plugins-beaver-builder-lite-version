<div id="fl-license-form" class="fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'License', 'fl-builder' ); ?></h3>

	<?php
	/**
	 * Fires inside the License settings form, allowing other BB products to inject their own license fields.
	 *
	 * @since 1.0
	 */
	do_action( 'fl_themes_license_form' );
	?>

	<?php
	$alpha   = get_option( 'fl_alpha_updates', false );
	$beta    = get_option( 'fl_beta_updates', false );
	$channel = 'stable';
	if ( $alpha ) {
		$channel = 'alpha';
	} elseif ( $beta ) {
		$channel = 'beta';
	}
	?>
	<?php
	$subscription   = class_exists( 'FLUpdater' ) ? FLUpdater::get_subscription_info() : null;
	$license_active = $subscription && ! empty( $subscription->active );
	?>
	<?php if ( true !== FL_BUILDER_LITE && $license_active ) : ?>
	<div class="fl-prerelease-card">
		<div class="fl-prerelease-header">
			<h3><?php _e( 'Release Channel', 'fl-builder' ); ?></h3>
			<?php // translators: %s: Branding name ?>
		<p><?php printf( __( 'Choose which updates you receive. This applies to all %s products.', 'fl-builder' ), esc_html( FLBuilderModel::get_branding() ) ); ?></p>
		</div>

		<div class="fl-release-channel-options">
			<label class="fl-release-channel-card <?php echo ( 'stable' === $channel ) ? 'fl-release-channel-active' : ''; ?>">
				<input type="radio" name="release-channel" value="stable" <?php checked( $channel, 'stable' ); ?> />
				<span class="fl-release-channel-name"><?php _e( 'Stable', 'fl-builder' ); ?></span>
				<span class="fl-release-channel-desc"><?php _e( 'Production-ready releases only.', 'fl-builder' ); ?></span>
			</label>
			<label class="fl-release-channel-card <?php echo ( 'beta' === $channel ) ? 'fl-release-channel-active' : ''; ?>">
				<input type="radio" name="release-channel" value="beta" <?php checked( $channel, 'beta' ); ?> />
				<span class="fl-release-channel-name"><?php _e( 'Beta', 'fl-builder' ); ?></span>
				<span class="fl-release-channel-desc"><?php _e( 'Early access to upcoming features.', 'fl-builder' ); ?></span>
			</label>
			<label class="fl-release-channel-card <?php echo ( 'alpha' === $channel ) ? 'fl-release-channel-active' : ''; ?>">
				<input type="radio" name="release-channel" value="alpha" <?php checked( $channel, 'alpha' ); ?> />
				<span class="fl-release-channel-name"><?php _e( 'Alpha', 'fl-builder' ); ?></span>
				<span class="fl-release-channel-desc"><?php _e( 'Bleeding edge, may be unstable.', 'fl-builder' ); ?></span>
			</label>
		</div>
		<?php if ( 'Beaver Builder' === FLBuilderModel::get_branding() ) : ?>
		<p class="fl-prerelease-docs">
			<?php // translators: %s: Link to Docs ?>
			<?php printf( 'Please be sure to read our %s.', sprintf( "<a target='_blank' href='https://docs.wpbeaverbuilder.com/beaver-builder/introduction/releases-versioning#alpha-beta--dev-releases'>%s</a>", __( 'Prerelease Documentation', 'fl-builder' ) ) ); ?>
		</p>
		<?php endif; ?>
		<?php wp_nonce_field( 'beta', 'fl-beta-nonce' ); ?>
	</div>
	<?php endif; ?>
</div>

