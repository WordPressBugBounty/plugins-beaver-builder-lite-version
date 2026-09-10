<<?php echo $module->get_tag(); ?> <?php $module->render_attributes( $module->link_attributes() ); ?>>
	<?php $module->render_children(); ?>
	<?php echo FLBuilderModuleUtils::get_link_notice( $settings, 'link' ); ?>
</<?php echo $module->get_tag(); ?>>
