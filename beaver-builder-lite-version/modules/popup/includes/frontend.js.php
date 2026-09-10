<?php if ( FLBuilderModel::is_builder_active() ) {
	return;
} ?>
(function() {

	let popup = null;
	document.querySelectorAll('.fl-popup.fl-node-<?php echo $id; ?>').forEach((element) => {
	popup = new FLPopup({
		id: element.id,
		element: element,
		once: {
		mode: <?php echo $module->get_trigger_configuration( 'once' ); ?>,
		key: `fl_popup_once_${element.id}`,
		shown: false,
		},
		triggers: {
		exit: <?php echo $module->get_trigger_configuration( 'exit' ); ?>,
		delay: <?php echo $module->get_trigger_configuration( 'delay' ); ?>,
		scroll: <?php echo $module->get_trigger_configuration( 'scroll' ); ?>,
		},
		schedule: {
		start: <?php echo $module->get_trigger_schedule( 'start' ); ?>,
		end: <?php echo $module->get_trigger_schedule( 'end' ); ?>,
		},
		loop: {
		prev: popup,
		next: null,
		},
	});
	});

})();
