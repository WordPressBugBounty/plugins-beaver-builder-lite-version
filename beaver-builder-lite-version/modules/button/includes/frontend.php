<?php

$modern   = $module->get_version_flag( 'unwrapped' );
$wrapper  = $module->get_wrapper_attributes();
$button   = $module->build_button_output();
$lightbox = $module->build_lightbox_output();

printf( '<div %s>', $wrapper );
echo $button;
if ( $modern ) :
	echo $lightbox . '</div>';
else :
	echo '</div>' . $lightbox;
endif;
