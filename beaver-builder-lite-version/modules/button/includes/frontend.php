<?php

$attrs = [
	'class' => explode( ' ', $module->get_classname() ),
];

$button_node_id = "fl-node-$id";

if ( isset( $settings->id ) && ! empty( $settings->id ) ) {
	$button_node_id = esc_attr( $settings->id );
}

$element_attributes = join( ' ', [ $module->get_tag(), $module->get_link(), $module->get_label(), $module->get_target() ] );
?>
<div <?php $module->render_attributes( $attrs ); ?>>
	<?php if ( isset( $settings->click_action ) && 'lightbox' == $settings->click_action ) : ?>
		<<?php echo $element_attributes; ?> class="fl-button <?php echo $button_node_id; ?> fl-button-lightbox<?php echo ( 'enable' == $settings->icon_animation ) ? ' fl-button-icon-animation' : ''; ?>">
	<?php else : ?>
		<<?php echo $element_attributes; ?> <?php echo ( isset( $settings->link_download ) && 'yes' === $settings->link_download ) ? ' download' : ''; ?> class="fl-button<?php echo ( 'enable' == $settings->icon_animation ) ? ' fl-button-icon-animation' : ''; ?>" <?php echo $module->get_rel(); ?>>
	<?php endif; ?>
		<?php
		if ( ! empty( $settings->icon ) && ( 'before' == $settings->icon_position || ! isset( $settings->icon_position ) ) ) :
			?>
		<i class="fl-button-icon fl-button-icon-before <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>
		<?php endif; ?>
		<?php if ( ! empty( $settings->text ) ) : ?>
		<span class="fl-button-text"><?php echo $settings->text; ?></span>
		<?php endif; ?>
		<?php
		if ( ! empty( $settings->icon ) && 'after' == $settings->icon_position ) :
			?>
		<i class="fl-button-icon fl-button-icon-after <?php echo esc_attr( $settings->icon ); ?>" aria-hidden="true"></i>
		<?php endif; ?>
	</<?php echo esc_attr( $module->get_tag() ); ?>>
	<?php if ( 'lightbox' == $settings->click_action && 'html' == $settings->lightbox_content_type && isset( $settings->lightbox_content_html ) ) : ?>
		<div class="<?php echo $button_node_id; ?> fl-button-lightbox-content mfp-hide">
			<?php echo $settings->lightbox_content_html; ?>
		</div>
	<?php endif; ?>
</div>
