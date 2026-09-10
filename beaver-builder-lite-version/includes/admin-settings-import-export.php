<div id="fl-import-export-form" class="fl-settings-form">

	<h3 class="fl-settings-form-header"><?php _e( 'Import / Export Settings', 'fl-builder' ); ?></h3>
	<?php // translators: %s: Branding name ?>
	<p><?php printf( __( 'Export your %s settings to transfer them to another site, or import a previously exported settings file.', 'fl-builder' ), esc_html( FLBuilderModel::get_branding() ) ); ?></p>

	<div class="fl-settings-warning">
		<span class="dashicons dashicons-warning"></span>
		<div>
			<strong><?php _e( 'Compatibility Notice', 'fl-builder' ); ?></strong>
			<p><?php _e( 'Exports completed with versions prior to 2.8.1 are not compatible due to a change in format of export data.', 'fl-builder' ); ?></p>
		</div>
	</div>

	<div class="fl-import-export-row">
		<div class="fl-import-export-card">
			<div class="fl-import-export-card-header">
				<h3><?php _e( 'Export Settings', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Download your current settings as a file that can be imported on another site.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-import-export-card-body">
				<label class="fl-toggle-switch">
					<input type="checkbox" class="global_all" checked name="global_all" />
					<span><?php _e( 'All Settings', 'fl-builder' ); ?></span>
				</label>
				<div class="extra" style="display:none">
					<label class="fl-toggle-switch">
						<input type="checkbox" class="admin" checked name="admin" />
						<span><?php _e( 'Admin Settings', 'fl-builder' ); ?></span>
					</label>
					<label class="fl-toggle-switch">
						<input type="checkbox" class="global" checked name="global" />
						<span><?php _e( 'Global Settings', 'fl-builder' ); ?></span>
					</label>
					<label class="fl-toggle-switch">
						<input type="checkbox" class="styles" checked name="styles" />
						<span><?php _e( 'Global Styles', 'fl-builder' ); ?></span>
					</label>
					<label class="fl-toggle-switch">
						<input type="checkbox" class="colors" checked name="colors" />
						<span><?php _e( 'Global Colors', 'fl-builder' ); ?></span>
					</label>
				</div>
				<p class="submit">
					<input type="button" class="button button-primary export" value="<?php esc_attr_e( 'Export Settings', 'fl-builder' ); ?>" />
				</p>
			</div>
		</div>

		<div class="fl-import-export-card">
			<div class="fl-import-export-card-header">
				<h3><?php _e( 'Import Settings', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Upload a previously exported settings file to apply those settings to this site.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-import-export-card-body">
				<p class="submit">
					<input type="button" class="button button-primary import" value="<?php esc_attr_e( 'Import Settings', 'fl-builder' ); ?>" />
				</p>
			</div>
		</div>
	</div>

	<div class="fl-import-export-card">
		<div class="fl-import-export-card-header">
			<h3><?php esc_html_e( 'Settings Snapshots', 'fl-builder' ); ?></h3>
			<p><?php esc_html_e( 'Save up to 5 snapshots of your current settings. Snapshots are stored on the server and can be restored at any time.', 'fl-builder' ); ?></p>
			<details class="fl-icons-note">
				<summary><span class="dashicons dashicons-info-outline"></span><?php esc_html_e( 'When are snapshots automatically offered?', 'fl-builder' ); ?></summary>
				<p><?php esc_html_e( 'You will be prompted to create a backup snapshot before importing settings, restoring a snapshot, or resetting settings. If you already have 5 snapshots, the oldest will be removed to make room.', 'fl-builder' ); ?></p>
			</details>
		</div>
		<div class="fl-import-export-card-body">
			<div id="fl-snapshots-section">
				<p>
					<input type="text" id="snapshot-name" placeholder="<?php esc_attr_e( 'Snapshot name (optional)', 'fl-builder' ); ?>" maxlength="100" style="width:300px;" />
					<input type="button" class="button button-primary snapshot-save" value="<?php esc_attr_e( 'Save Snapshot', 'fl-builder' ); ?>" />
				</p>
				<div id="fl-snapshots-list"></div>
			</div>
		</div>
	</div>

	<details class="fl-import-export-danger-accordion" style="margin-top:20px;">
		<summary class="fl-import-export-danger-toggle"><?php _e( 'Reset Settings', 'fl-builder' ); ?><span class="dashicons dashicons-arrow-down-alt2"></span></summary>
		<div class="fl-import-export-card fl-import-export-card-danger">
			<div class="fl-import-export-card-header">
				<?php // translators: %s: Branding name ?>
			<p><?php printf( __( 'Reset all %s settings to their defaults. This cannot be undone.', 'fl-builder' ), esc_html( FLBuilderModel::get_branding() ) ); ?></p>
			</div>
			<div class="fl-import-export-card-body">
				<p class="submit">
					<input type="button" class="button button-primary fl-import-export-btn-danger reset" value="<?php esc_attr_e( 'Reset Settings', 'fl-builder' ); ?>" />
				</p>
			</div>
		</div>
	</details>

	<?php wp_nonce_field( 'fl_builder_import_export' ); ?>

	<?php if ( 'Beaver Builder' === FLBuilderModel::get_branding() ) : ?>
	<p class="fl-import-export-docs-link">
		<?php
		$link = sprintf( '<a target="_blank" href="https://docs.wpbeaverbuilder.com/beaver-builder/management-migration/import-export-settings">%s</a>', esc_attr__( 'documentation', 'fl-builder' ) );
		// translators: %s: Link to documentation
		printf( __( 'See %s for more information.', 'fl-builder' ), $link );
		?>
	</p>
	<?php endif; ?>

</div>
