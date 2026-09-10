<?php
// first check we have a download for the current version.
$plugin_data = get_plugin_data( FL_BUILDER_FILE );
$plugin_name = $plugin_data['Name'];
$themer      = false;

foreach ( $subscription->downloads as $ver ) {
	if ( stristr( $ver, 'Themer' ) ) {
		$themer = true;
	}
}

if ( '{FL_BUILDER_NAME}' !== $plugin_data['Name'] && ! in_array( $plugin_name, $subscription->downloads, true ) ) {

	$show_warning = false;
	$version      = '';

	// find available plugin Version
	foreach ( $subscription->downloads as $ver ) {
		if ( stristr( $ver, 'Beaver Builder Plugin' ) ) {
			preg_match( '#\((.*)\sVersion\)$#', $ver, $match );
			$version = ( isset( $match[1] ) ) ? $match[1] : false;
		}
	}

	switch ( $plugin_data['Name'] ) {
		// pro - show warning if standard is pnly available version
		case 'Beaver Builder Plugin (Pro Version)':
			$show_warning = ( 'Standard' === $version ) ? true : false;
			break;
		// agency show warning if available is NOT agency
		case 'Beaver Builder Plugin (Agency Version)':
			$show_warning = ( 'Agency' !== $version ) ? true : false;
			break;
	}

	if ( ! $version ) {
		$show_warning = true;
	}

	if ( $show_warning ) {
		$header_txt = __( 'Beaver Builder updates issue!!', 'fl-builder' );
		// translators: %s: Product name
		$txt = sprintf( __( 'Updates for Beaver Builder will not work as you appear to have %s activated but it is not in your available downloads.', 'fl-builder' ), '<strong>' . $plugin_name . '</strong>' );
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong></p><p>%s</p></div>',
			$header_txt,
			$txt
		);
	}
}
// themer installed but no licence?
if ( ! $themer && defined( 'FL_THEME_BUILDER_VERSION' ) ) {
	echo( '<div class="notice notice-error"><p><strong>Beaver Themer updates issue!</strong></p><p>Updates for Beaver Themer will not work as you appear to have Beaver Themer activated but it is not in your available downloads</p></div>' );
}

?>
<div class="fl-downloads-card">
	<div class="fl-downloads-header">
		<h3><?php _e( 'Available Downloads', 'fl-builder' ); ?></h3>
		<p><?php _e( 'Products available with your current license.', 'fl-builder' ); ?></p>
	</div>
	<div class="fl-downloads-list">
		<?php
		/**
		 * Filters the array of subscription download items shown in the account downloads section.
		 *
		 * @since 1.0
		 * @param array $downloads Array of download name strings from the subscription.
		 */
		$downloads = apply_filters( 'fl_builder_subscription_downloads', $subscription->downloads );
		foreach ( $downloads as $download ) {
			echo '<div class="fl-download-card">' . $download . '</div>';
		}
		/**
		 * Fires after the subscription downloads list is rendered.
		 *
		 * @since 1.0
		 */
		do_action( 'fl_builder_after_subscription_downloads' );
		?>
	</div>
</div>

<?php if ( ! $themer ) : ?>
	<div class="fl-themer-upsell">
		<div class="fl-themer-upsell-header">
			<h3><?php _e( 'Take Beaver Builder Even Further', 'fl-builder' ); ?></h3>
			<p><?php _e( 'Unlock the full power of Beaver Builder with Beaver Themer.', 'fl-builder' ); ?></p>
		</div>
		<ul class="fl-themer-upsell-features">
			<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Create custom headers and footer layouts that override your theme.', 'fl-builder' ); ?></li>
			<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Design unique page layouts for index, archive, search, single posts and 404 pages.', 'fl-builder' ); ?></li>
			<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Customize WooCommerce Shop, Checkout, Cart and My Account pages.', 'fl-builder' ); ?></li>
			<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Create layout "parts" to insert above or below headers, footers, or the content area.', 'fl-builder' ); ?></li>
		</ul>
		<?php
		$themer_upsell_url = FLBuilderModel::get_store_url(
			'beaver-themer',
			array(
				'utm_medium'   => 'bb-pro',
				'utm_source'   => 'license-settings-page',
				'utm_campaign' => 'themer-upsell',
			)
		);
		?>
		<a class="fl-themer-upsell-btn" target="_blank" href="<?php echo $themer_upsell_url; ?>"><?php _e( 'Learn More About Beaver Themer', 'fl-builder' ); ?> <span class="dashicons dashicons-external"></span></a>
	</div>
<?php endif; ?>
