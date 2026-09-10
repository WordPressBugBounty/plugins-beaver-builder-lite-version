<?php

$attrs = [
	'class' => [
		'fl-heading',
		'fl-heading-text', // Necessary for live preview/inline editing.
	],
];

?>
<<?php echo esc_attr( $settings->tag ); ?> <?php $module->render_attributes( $attrs ); ?>>
	<?php if ( ! empty( $settings->link ) ) : ?>
	<a <?php echo FLBuilderModuleUtils::get_link_attributes( $settings, 'link' ); ?>>
	<?php endif; ?>
		<?php echo $settings->heading; ?>
	<?php if ( ! empty( $settings->link ) ) : ?>
		<?php echo FLBuilderModuleUtils::get_link_notice( $settings, 'link' ); ?>
	</a>
	<?php endif; ?>
</<?php echo esc_attr( $settings->tag ); ?>>
