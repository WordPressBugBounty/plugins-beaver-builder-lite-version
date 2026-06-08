<?php if ( $module->check_linking() ) { ?>
(($) => {
$(() => {
	$('.fl-node-<?php echo $id; ?>').on('click keydown', ( event ) => {
	if ( event.target.closest( 'a, button, input, select, textarea' ) ) return;
	if ( event.type === 'keydown' && event.key !== 'Enter' ) return;
	event.stopPropagation();
	const link = event.currentTarget.dataset.url;
	const target = '<?php echo $settings->link_target; ?>';
	const download = <?php echo ( 'yes' === $settings->link_download ) ? 'true' : 'false'; ?>;
	const attributes = '<?php echo $module->get_rel_attr(); ?>';
	if ( download ) {
		const anchor = document.createElement('a');
		anchor.href = link;
		anchor.download = '';
		anchor.target = target;
		anchor.rel = attributes;
		document.body.appendChild(anchor);
		anchor.click();
		document.body.removeChild(anchor);
	}	else if ( target === '_blank' ) {
		window.open(link, target, attributes.replace('nofollow', '').trim());
	} else {
		window.location.assign(link);
	}
	});
});
})(jQuery);
<?php } ?>
