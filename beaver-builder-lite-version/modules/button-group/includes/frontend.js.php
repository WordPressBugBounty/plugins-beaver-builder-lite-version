<?php

for ( $i = 0; $i < count( $settings->items ); $i++ ) :
	if ( ! is_object( $settings->items[ $i ] ) ) {
		continue;
	}

	$button_group_id = "fl-node-$id";
	if ( isset( $settings->items[ $i ]->click_action ) && 'link' !== $settings->items[ $i ]->click_action ) :
		$button_item_id = '#' . $button_group_id . '-' . $i;
		?>
		(function($){
			$('.<?php echo $button_group_id; ?>').each(function(){
				var $this = $(this);
				<?php if ( 'button' == $settings->items[ $i ]->click_action ) : ?>
				$this.find('<?php echo $button_item_id; ?> .fl-button').on('click', function(){
					<?php echo $settings->items[ $i ]->button; ?>
				});
				<?php elseif ( 'lightbox' == $settings->items[ $i ]->click_action ) : ?>
				$this.find('.fl-button-lightbox').magnificPopup({
					<?php if ( 'video' == $settings->items[ $i ]->lightbox_content_type ) : ?>
					type: 'iframe',
					mainClass: 'fl-button-lightbox-wrap',
					<?php endif; ?>

					<?php if ( 'html' == $settings->items[ $i ]->lightbox_content_type ) : ?>
					type: 'inline',
					callbacks: {
						elementParse: function(item) {
							item.src = $( item.el ).closest('.fl-button-wrap').find('.fl-button-lightbox-content');
						},
						open: function() {
							var divWrap = $( $(this.content)[0] ).find('> div');
							divWrap.css('display', 'block');

							// Triggers select change in we have multiple forms in a page
							if ( divWrap.find('form select').length > 0 ) {
								divWrap.find('form select').trigger('change');
							}
						}
					},
					<?php endif; ?>
					closeBtnInside: true,
					fixedContentPos: true,
					tLoading: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>'
				});
				<?php endif; ?>
			});
		})(jQuery);
		<?php
	endif;
endfor;
