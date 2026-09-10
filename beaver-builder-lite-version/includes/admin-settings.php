<div class="wrap <?php FLBuilderAdminSettings::render_page_class(); ?>">

	<div class="fl-settings-heading-wrap">
		<div class="fl-settings-heading-text">
			<h1 class="fl-settings-heading">
				<?php FLBuilderAdminSettings::render_page_heading(); ?>
			</h1>
			<?php if ( 'Beaver Builder' === FLBuilderModel::get_branding() ) : ?>
			<div class="fl-settings-heading-links">
				<a href="https://docs.wpbeaverbuilder.com/" target="_blank"><i class="dashicons dashicons-book-alt"></i> <?php esc_html_e( 'Documentation', 'fl-builder' ); ?> <i class="dashicons dashicons-external"></i></a>
				<span class="fl-heading-link-sep">&middot;</span>
				<a href="https://www.wpbeaverbuilder.com/beaver-builder-support/" target="_blank"><i class="dashicons dashicons-sos"></i> <?php esc_html_e( 'Support', 'fl-builder' ); ?> <i class="dashicons dashicons-external"></i></a>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<?php FLBuilderAdminSettings::render_update_message(); ?>

	<div class="fl-settings-nav">
		<ul>
			<?php FLBuilderAdminSettings::render_nav_items(); ?>
		</ul>
	</div>

	<div class="fl-settings-content">
		<?php FLBuilderAdminSettings::render_forms(); ?>
	</div>
</div>
