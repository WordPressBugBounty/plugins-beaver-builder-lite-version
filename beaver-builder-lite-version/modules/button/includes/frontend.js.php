<?php
$button_node_id = "fl-node-$id";

if ( isset( $settings->click_action ) ) : ?>
(function($){
	<?php if ( 'button' == $settings->click_action && ! isset( $_GET['fl_builder'] ) ) : ?>
	$('.<?php echo $button_node_id; ?> .fl-button').on('click', function(){
		<?php echo $settings->button; ?>
	});
	<?php elseif ( 'lightbox' == $settings->click_action ) : ?>
	$('.<?php echo $button_node_id; ?>').each(function(){
		var $this = $(this);
		$this.find('.fl-button-lightbox').magnificPopup({
			<?php if ( 'video' == $settings->lightbox_content_type ) : ?>
			type: 'iframe',
			mainClass: 'fl-button-lightbox-wrap',
			<?php endif; ?>

			<?php if ( 'html' == $settings->lightbox_content_type ) : ?>
			type: 'inline',
			items: {
				src: $this.find('.fl-button-lightbox-content')[0]
			},
			callbacks: {
				open: function() {
					var content = $(this.content),
						divWrap = $(content[0]).find('> div');

					divWrap.css('display', 'block');

					// Triggers select change in we have multiple forms in a page
					if ( divWrap.find('form select').length > 0 ) {
						divWrap.find('form select').trigger('change');
					}

					// reload sliders.
					FLBuilderLayout.reloadSlider(content);
					FLBuilderLayout.resizeSlideshow();
				},
			},
			<?php endif; ?>
			closeBtnInside: true,
			fixedContentPos: true,
			tLoading: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>',
		});
	});
	<?php elseif ( 'copy_text' == $settings->click_action ) : ?>
	$('.<?php echo $button_node_id; ?> .fl-button').on('click', function(e){
		e.preventDefault();

		var $btn           = $(this);
		var textToCopy     = $btn.data('copy-text');
		var successMessage = $btn.data('copy-success-message');
		var $label         = $btn.find('.fl-button-text');

		if (!textToCopy || !successMessage) return;

		var originalHtml = $label.html();
		$btn.prop('disabled', true);
		$btn.addClass('disabled');

		function showSuccess() {
			$label.text(successMessage);
		}

		function showError() {
			$label.text('Failed to copy');
		}

		function showFinish() {
			setTimeout(function() {
				$btn.prop('disabled', false);
				$btn.removeClass('disabled');
				$label.html(originalHtml);
				$btn.focus();
			}, 1500);
		}

		if (!navigator.clipboard) {
			showError();
			showFinish();
			return;
		}

		navigator.clipboard.writeText(textToCopy).then(showSuccess).catch(showError).finally(showFinish);
	});
	<?php endif; ?>
})(jQuery);
<?php endif; ?>
