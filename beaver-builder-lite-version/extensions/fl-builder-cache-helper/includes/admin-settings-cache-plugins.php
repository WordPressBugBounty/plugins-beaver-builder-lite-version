<?php

$settings = \FLCacheClear\Plugin::get_settings();
$plugins  = \FLCacheClear\Plugin::get_plugins();
?>

	<div class="fl-tools-card">
		<div class="fl-tools-card-header">
			<h3><?php _e( 'Cache Clearing Tool', 'fl-builder' ); ?></h3>
			<p>
			<?php
			/* translators: %s: page builder name */
			printf( __( 'Automatically clear third-party caches when layouts are saved and prevent page caching while the %s editor is active.', 'fl-builder' ), FLBuilderModel::get_branding() );
			?>
			</p>
		</div>
		<div class="fl-tools-card-body">
			<label class="fl-toggle-switch">
				<input type="checkbox" class="fl-cache-plugins-toggle" value="1" <?php checked( $settings['enabled'], 1 ); ?> />
				<span><?php _e( 'Enable the Cache Clearing Tool', 'fl-builder' ); ?></span>
			</label>

			<div class="fl-tools-sub-option" <?php echo empty( $settings['enabled'] ) ? 'style="display:none"' : ''; ?>>
				<label class="fl-toggle-switch">
					<input type="checkbox" class="fl-cache-varnish-toggle" value="1" <?php checked( $settings['varnish'], 1 ); ?> />
					<span><?php _e( 'Enable proxy cache clearing (Varnish / Litespeed)', 'fl-builder' ); ?></span>
				</label>
			</div>

			<details class="fl-tools-details-info">
				<summary class="fl-tools-detail-small"><?php _e( 'More info', 'fl-builder' ); ?></summary>
				<p>
					<?php /* translators: %s: branded builder name */ ?>
					<?php printf( __( 'If enabled, cache clearing occurs when layouts and templates are saved and when WordPress finishes updating plugins and themes. This setting also defines the DONOTCACHEPAGE constant, which is respected by most cache plugins, to keep the page from being cached when the %s editor is active.', 'fl-builder' ), FLBuilderModel::get_branding() ); ?>
				</p>
			</details>

			<details class="fl-cache-plugins-list">
				<summary class="fl-tools-detail-small"><?php _e( 'Supported caches', 'fl-builder' ); ?></summary>
				<?php echo $plugins; ?>
			</details>
		</div>
		<?php wp_nonce_field( 'cache-plugins', 'fl-cache-plugins-nonce' ); ?>
	</div>
