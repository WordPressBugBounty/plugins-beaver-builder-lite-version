<?php if ( ! FLBuilderModel::is_builder_active() && '' !== $settings->link && 1 !== $module->version ) { ?>
(($) => {
$(() => {
	$('.fl-node-<?php echo $id; ?>').on('click keydown', ( event ) => {
	if ( event.target.nodeName === 'A' ) return;
	if ( event.type === 'keydown' && event.key !== 'Enter' ) return;
	event.stopPropagation();
	const link = event.currentTarget.dataset.url;
	const target = '<?php echo $settings->link_target; ?>';
	const attributes = '<?php echo str_replace( ' ', ', ', $module->get_rel_attr() ); ?>';
	window.open(link, target, attributes);
	});
});
})(jQuery);
<?php } ?>
