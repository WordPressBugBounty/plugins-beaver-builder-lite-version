<div id="fl-tools-form" class="fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'Tools', 'fl-builder' ); ?></h3>

	<?php
	$debug = get_transient( 'fl_debug_mode' );
	if ( $debug ) {
		$expire_opt = get_option( '_transient_timeout_fl_debug_mode' );
		$datetime1  = new DateTime( 'now' );
		$datetime2  = new DateTime( gmdate( 'Y-m-d H:i:s', $expire_opt ) );
		$interval   = $datetime1->diff( $datetime2 );
	}
	?>

	<div class="fl-tools-row">
		<div class="fl-tools-card">
			<div class="fl-tools-card-header">
				<h3><?php _e( 'Cache', 'fl-builder' ); ?></h3>
				<p><?php _e( 'A CSS and JavaScript file is dynamically generated and cached each time you create a new layout. Sometimes the cache needs to be refreshed when you migrate your site to another server or update to the latest version.', 'fl-builder' ); ?></p>
			</div>
			<?php if ( is_network_admin() ) : ?>
			<p class="fl-tools-note"><span class="dashicons dashicons-info-outline"></span><?php _e( 'This applies to all sites on the network.', 'fl-builder' ); ?></p>
			<?php elseif ( ! is_network_admin() && is_multisite() ) : ?>
			<p class="fl-tools-note"><span class="dashicons dashicons-info-outline"></span><?php _e( 'This only applies to this site. Please visit the Network Admin Settings to clear the cache for all sites on the network.', 'fl-builder' ); ?></p>
			<?php endif; ?>
			<form id="cache-form" action="<?php FLBuilderAdminSettings::render_form_action( 'tools' ); ?>" method="post">
				<p class="submit">
					<input type="submit" name="update" class="button-primary" value="<?php esc_attr_e( 'Clear Cache', 'fl-builder' ); ?>" />
					<?php wp_nonce_field( 'cache', 'fl-cache-nonce' ); ?>
				</p>
			</form>
		</div>

		<?php require FL_BUILDER_CACHE_HELPER_DIR . 'includes/admin-settings-cache-plugins.php'; ?>
	</div>

	<div class="fl-tools-row">
		<div class="fl-tools-card">
			<div class="fl-tools-card-header">
				<div class="fl-tools-card-title-row">
					<div>
						<h3><?php _e( 'Debug Mode', 'fl-builder' ); ?></h3>
						<p><?php _e( 'Enable debug mode to generate a unique support URL.', 'fl-builder' ); ?></p>
					</div>
					<label class="fl-toggle-switch">
						<input type="checkbox" class="fl-debug-toggle" value="1" <?php checked( ! empty( $debug ) ); ?> />
						<span></span>
					</label>
				</div>
			</div>
			<?php
			$debug_url    = '';
			$debug_expiry = '';
			if ( $debug ) {
				$debug_url    = add_query_arg( array( 'fldebug' => $debug ), site_url() );
				$debug_expiry = $interval->format( '%d days %h hours %i minutes' );
			}
			?>
			<div class="fl-tools-debug-info" <?php echo empty( $debug ) ? 'style="display:none"' : ''; ?>>
				<p class="fl-tools-debug-label"><?php _e( 'Share this URL with support:', 'fl-builder' ); ?></p>
				<code class="fl-tools-debug-url" data-url="<?php echo esc_attr( $debug_url ); ?>" title="<?php esc_attr_e( 'Click to copy', 'fl-builder' ); ?>">
					<span class="fl-debug-url-text"><?php echo esc_html( $debug_url ); ?></span>
					<span class="fl-debug-url-copy"><span class="dashicons dashicons-clipboard"></span> <?php _e( 'Copy', 'fl-builder' ); ?></span>
				</code>
				<p class="fl-tools-debug-expiry">
					<span class="dashicons dashicons-clock"></span>
					<?php
					// translators: %s: Time until expiry
					printf( esc_html__( 'Expires in %s', 'fl-builder' ), '<strong class="fl-debug-expiry-text">' . esc_html( $debug_expiry ) . '</strong>' );
					?>
				</p>
			</div>
			<?php wp_nonce_field( 'debug', 'fl-debug-nonce' ); ?>
		</div>

		<?php
		if ( FLBuilderUsage::show_settings() ) {
			$usage = get_site_option( 'fl_builder_usage_enabled', false );

			?>
		<div class="fl-tools-card">
			<div class="fl-tools-card-header">
				<div class="fl-tools-card-title-row">
					<div>
						<h3><?php _e( 'Send Usage Data', 'fl-builder' ); ?></h3>
						<p><?php _e( 'Send anonymous usage stats to help improve the plugin.', 'fl-builder' ); ?></p>
					</div>
					<label class="fl-toggle-switch">
						<input type="checkbox" class="fl-usage-toggle" value="1" <?php checked( '1', $usage ); ?> />
						<span></span>
					</label>
				</div>
			</div>
			<?php echo FLBuilderUsage::data_demo(); ?>
			<?php wp_nonce_field( 'fl-usage', 'fl-usage-nonce' ); ?>
		</div>
		<?php } ?>
	</div>

	<?php if ( get_transient( 'fl_debug_mode' ) || ( defined( 'FL_ENABLE_META_CSS_EDIT' ) && FL_ENABLE_META_CSS_EDIT ) ) : ?>
	<div class="fl-tools-row">
		<?php
		$data = get_option( '_fl_builder_settings' );
		if ( ! isset( $data->css ) ) {
			$css = '';
		} else {
			$css = $data->css;
		}
		if ( ! isset( $data->js ) ) {
			$js = '';
		} else {
			$js = $data->js;
		}
		?>

		<div class="fl-tools-card">
			<div class="fl-tools-card-header">
				<h3><?php _e( 'Global CSS / JS', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Edit the global CSS and JavaScript that applies to all pages.', 'fl-builder' ); ?></p>
			</div>
			<form id="css-js-form" action="<?php FLBuilderAdminSettings::render_form_action( 'tools' ); ?>" method="post">
				<div class="fl-tools-code-section">
					<label class="fl-tools-code-label"><?php _e( 'CSS', 'fl-builder' ); ?></label>
					<textarea class="fl-tools-textarea" rows="10" name="css"><?php echo esc_attr( $css ); ?></textarea>
				</div>
				<div class="fl-tools-code-section">
					<label class="fl-tools-code-label"><?php _e( 'JavaScript', 'fl-builder' ); ?></label>
					<textarea class="fl-tools-textarea" rows="10" name="js"><?php echo esc_attr( $js ); ?></textarea>
				</div>
				<p class="submit">
					<input type="submit" name="update-css-js" class="button-primary" value="<?php echo esc_attr__( 'Update Global CSS/JS', 'fl-builder' ); ?>" />
				</p>
				<?php wp_nonce_field( 'debug', 'fl-css-js-nonce' ); ?>
			</form>
		</div>

		<?php if ( defined( 'FL_THEME_VERSION' ) ) : ?>
		<div class="fl-tools-card">
			<div class="fl-tools-card-header">
				<h3><?php _e( 'Theme Code Settings', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Edit the custom code settings from your theme.', 'fl-builder' ); ?></p>
			</div>
			<form id="theme-opts-form" action="<?php FLBuilderAdminSettings::render_form_action( 'tools' ); ?>" method="post">
				<?php
				$theme_opts  = get_theme_mods();
				$theme_codes = array(
					'fl-js-code'     => __( 'JS Code', 'fl-builder' ),
					'fl-head-code'   => __( 'Head Code', 'fl-builder' ),
					'fl-header-code' => __( 'Header Code', 'fl-builder' ),
					'fl-footer-code' => __( 'Footer Code', 'fl-builder' ),
				);
				foreach ( $theme_codes as $key => $label ) {
					$code = isset( $theme_opts[ $key ] ) ? $theme_opts[ $key ] : '';
					printf( '<div class="fl-tools-code-section">' );
					printf( '<label class="fl-tools-code-label">%s</label>', esc_html( $label ) );
					printf( '<textarea class="fl-tools-textarea" rows="10" name="%s">%s</textarea>', esc_attr( $key ), esc_attr( $code ) );
					printf( '</div>' );
				}
				?>
				<p class="submit">
					<input type="submit" name="update-theme-opts" class="button-primary" value="<?php echo esc_attr__( 'Update Theme Code Settings', 'fl-builder' ); ?>" />
				</p>
				<?php wp_nonce_field( 'debug', 'fl-theme-opts-nonce' ); ?>
			</form>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ( is_network_admin() || ! self::multisite_support() ) : ?>

	<details class="fl-tools-danger-accordion">
		<summary class="fl-tools-danger-toggle"><?php _e( 'Uninstall', 'fl-builder' ); ?><span class="dashicons dashicons-arrow-down-alt2"></span></summary>
		<div class="fl-tools-card fl-tools-card-danger">
			<div class="fl-tools-card-header">
				<p><?php _e( 'Uninstall the page builder plugin and delete all associated data. You can deactivate from the plugins page instead if you want to keep your data.', 'fl-builder' ); ?></p>
			</div>
			<p class="fl-tools-note"><span class="dashicons dashicons-info-outline"></span><?php _e( 'Post meta <code>_fl_builder_data</code>, <code>_fl_builder_draft</code> and <code>_fl_builder_enabled</code> will be preserved for reinstallation.', 'fl-builder' ); ?></p>
			<?php if ( is_multisite() ) : ?>
			<p class="fl-tools-note"><span class="dashicons dashicons-info-outline"></span><?php _e( 'This applies to all sites on the network.', 'fl-builder' ); ?></p>
			<?php endif; ?>
			<form id="uninstall-form" action="<?php FLBuilderAdminSettings::render_form_action( 'tools' ); ?>" method="post">
				<p class="submit">
					<input type="submit" name="uninstall-submit" class="button button-primary fl-tools-btn-danger" value="<?php esc_attr_e( 'Uninstall', 'fl-builder' ); ?>">
					<?php wp_nonce_field( 'uninstall', 'fl-uninstall' ); ?>
				</p>
			</form>
		</div>
	</details>

	<?php endif; ?>

</div>

<script>
( function() {
	var el = document.querySelector( '.fl-tools-debug-url' );
	if ( ! el ) return;

	function copyText( text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			return navigator.clipboard.writeText( text );
		}
		var textarea = document.createElement( 'textarea' );
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild( textarea );
		textarea.select();
		document.execCommand( 'copy' );
		document.body.removeChild( textarea );
		return Promise.resolve();
	}

	var copyLabel = el.querySelector( '.fl-debug-url-copy' );
	var originalHTML = copyLabel.innerHTML;

	el.addEventListener( 'click', function() {
		copyText( el.getAttribute( 'data-url' ) ).then( function() {
			el.classList.add( 'fl-tools-debug-url-copied' );
			copyLabel.innerHTML = '<span class="dashicons dashicons-yes"></span> <?php echo esc_js( __( 'Copied!', 'fl-builder' ) ); ?>';
			setTimeout( function() {
				el.classList.remove( 'fl-tools-debug-url-copied' );
				copyLabel.innerHTML = originalHTML;
			}, 2000 );
		} );
	} );
} )();
</script>
