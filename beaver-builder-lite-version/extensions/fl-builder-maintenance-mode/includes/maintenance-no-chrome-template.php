<?php
/**
 * Bare maintenance document rendered when the "hide theme header & footer"
 * option is on. It deliberately loads no theme template, so no theme chrome
 * (header/footer) appears — only the maintenance layout. wp_head()/wp_footer()
 * still fire so Beaver Builder assets and block styles load normally.
 *
 * The caller (FLBuilderMaintenanceMode::maybe_maintenance_mode) has already
 * overridden the main query to the maintenance post and run setup_postdata(),
 * so the_content and body_class resolve against that post here.
 *
 * @package BeaverBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<?php
	// Render inside a real loop, exactly as a theme template would. Beaver
	// Builder only renders its layout (FLBuilder::render_content) when it runs
	// through the_content() while in_the_loop() on the main query — calling the
	// the_content filter directly returns the raw, unstyled stored content.
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
	<?php wp_footer(); ?>
</body>
</html>
