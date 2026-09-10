<?php

$text_class = array( 'fl-icon-text' );
if ( empty( $settings->link ) ) {
	$text_class[] = 'fl-icon-text-wrap';
}
if ( empty( $settings->text ) ) {
	$text_class[] = 'fl-icon-text-empty';
}

$text_link_class = empty( $settings->link ) ? 'fl-icon-text-link' : 'fl-icon-text-link fl-icon-text-wrap';

?>
<?php if ( ! isset( $settings->exclude_wrapper ) || ( isset( $settings->exclude_wrapper ) && ! $settings->exclude_wrapper ) ) : ?>
<div class="fl-icon-wrap">
<?php endif; ?>
	<span class="fl-icon">
		<?php if ( ! empty( $settings->link ) ) : ?>
			<?php
			if ( ! empty( $settings->text ) ) :
				$options = array(
					'tabindex'    => '-1',
					'aria-hidden' => 'true',
				);
				?>
			<a <?php echo FLBuilderModuleUtils::get_link_attributes( $settings, 'link', $options ); ?>>
			<?php else : ?>
			<a <?php echo FLBuilderModuleUtils::get_link_attributes( $settings, 'link' ); ?>>
			<?php endif; ?>
		<?php endif; ?>
		<i class="<?php echo esc_attr( FLBuilderModuleUtils::get_icon_classes( $settings ) ); ?>" aria-hidden="true"></i>
		<?php if ( isset( $settings->sr_text ) && '' !== $settings->sr_text ) : ?>
		<span class="sr-only"><?php echo $settings->sr_text; ?></span>
		<?php endif; ?>
		<?php if ( empty( $settings->text ) ) : ?>
			<?php echo FLBuilderModuleUtils::get_link_notice( $settings, 'link' ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $settings->link ) ) : ?>
		</a>
		<?php endif; ?>
	</span>
	<?php if ( ! empty( $settings->text ) ) : ?>
		<div id="fl-icon-text-<?php echo ( isset( $module->node ) ? $module->node : esc_attr( $settings->id ) ); ?>" class="<?php echo join( ' ', $text_class ); ?>">
			<?php if ( ! empty( $settings->link ) ) : ?>
			<a <?php echo FLBuilderModuleUtils::get_link_attributes( $settings, 'link', [ 'class' => $text_link_class ] ); ?>>
			<?php endif; ?>
			<?php echo $settings->text; ?>
			<?php if ( ! empty( $settings->link ) ) : ?>
				<?php echo FLBuilderModuleUtils::get_link_notice( $settings, 'link' ); ?>
			</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php if ( ! isset( $settings->exclude_wrapper ) || ( isset( $settings->exclude_wrapper ) && ! $settings->exclude_wrapper ) ) : ?>
</div>
<?php endif; ?>
