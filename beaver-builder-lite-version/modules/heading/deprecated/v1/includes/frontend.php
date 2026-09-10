<<?php echo $settings->tag; ?> class="fl-heading">
	<?php if ( ! empty( $settings->link ) ) : ?>
	<a <?php echo FLBuilderModuleUtils::get_link_attributes( $settings, 'link' ); ?>>
	<?php endif; ?>
		<span class="fl-heading-text"><?php echo $settings->heading; ?></span>
	<?php if ( ! empty( $settings->link ) ) : ?>
		<?php echo FLBuilderModuleUtils::get_link_notice( $settings, 'link' ); ?>
	</a>
	<?php endif; ?>
</<?php echo $settings->tag; ?>>
