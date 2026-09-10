<?php

$categories     = FLBuilderModuleBlocks::get_categorized_block_editor_modules();
$enabled_blocks = FLBuilderModuleBlocks::get_enabled_block_editor_modules();

?>
<div id="fl-blocks-form" class="fl-settings-form">
	<h3 class="fl-settings-form-header"><?php _e( 'Blocks', 'fl-builder' ); ?></h3>
	<p><?php _e( 'Enable or disable blocks available in the block editor.', 'fl-builder' ); ?></p>

	<form id="blocks-form" action="<?php FLBuilderAdminSettings::render_form_action( 'blocks' ); ?>" method="post">

		<?php if ( FLBuilderAdminSettings::multisite_support() && ! is_network_admin() ) : ?>
		<label>
			<input class="fl-override-ms-cb" type="checkbox" name="fl-override-ms" value="1" <?php echo ( get_option( '_fl_builder_enabled_blocks' ) ) ? 'checked="checked"' : ''; ?> />
			<?php _e( 'Override network settings?', 'fl-builder' ); ?>
		</label>
		<?php endif; ?>

		<div class="fl-settings-form-content">

			<p><?php _e( 'Toggle blocks below to enable or disable them in the block editor.', 'fl-builder' ); ?></p>

			<?php $checked = in_array( 'all', $enabled_blocks ) ? 'checked' : ''; ?>
			<label class="fl-toggle-switch fl-modules-all-toggle">
				<input class="fl-module-all-cb" type="checkbox" name="fl-blocks[]" value="all" <?php echo $checked; ?> />
				<span><?php _ex( 'All', 'Plugin setup page: Blocks.', 'fl-builder' ); ?></span>
			</label>

			<?php
			foreach ( $categories as $title => $modules ) :
				$all_in_group = true;
				foreach ( $modules as $m ) {
					if ( ! in_array( $m->slug, $enabled_blocks ) ) {
						$all_in_group = false;
						break;
					}
				}
				$group_checked = ( in_array( 'all', $enabled_blocks ) || $all_in_group ) ? 'checked' : '';
				?>
			<div class="fl-toggle-group">
				<h3 class="fl-toggle-group-header">
					<label class="fl-toggle-switch">
						<input type="checkbox" class="fl-group-toggle-cb" <?php echo $group_checked; ?> />
						<span><?php echo $title; ?> <span class="fl-toggle-group-count"><?php echo count( $modules ); ?></span></span>
					</label>
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</h3>
				<div class="fl-toggle-group-content">
				<?php foreach ( $modules as $module ) : ?>
					<?php $checked = in_array( $module->slug, $enabled_blocks ) ? 'checked' : ''; ?>
					<p>
						<label class="fl-toggle-switch">
							<input class="fl-module-cb" type="checkbox" name="fl-blocks[]" value="<?php echo $module->slug; ?>" <?php echo $checked; ?> />
							<span><?php echo $module->name; ?></span>
						</label>
					</p>
				<?php endforeach; ?>
				</div>
			</div>
			<?php endforeach; ?>

		</div>
		<p class="submit">
			<input type="submit" name="update" class="button-primary" value="<?php esc_attr_e( 'Save Block Settings', 'fl-builder' ); ?>" />
			<?php wp_nonce_field( 'blocks', 'fl-blocks-nonce' ); ?>
		</p>
	</form>
</div>
