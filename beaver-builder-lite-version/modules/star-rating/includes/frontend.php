<?php $attributes = [
	'class'        => [ $settings->icon ],
	'data-icon'    => $settings->icon,
	'data-rating'  => $settings->rating,
	'dir'          => is_rtl() ? 'rtl' : 'ltr',
	'role'         => 'img',
	/* Translators: %1$s and %2$s stand for the (rating/total) numbers, respectively */
	'aria-label'   => sprintf( __( 'Rating is %1$s out of %2$s', 'fl-builder' ), $settings->rating, $settings->total ),
	'data-content' => str_repeat( $settings->unicode, $settings->total ),
]; ?>
<div <?php $module->render_attributes( $attributes ); ?>></div>
