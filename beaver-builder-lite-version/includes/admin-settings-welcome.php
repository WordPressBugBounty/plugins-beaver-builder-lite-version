<?php

function fl_welcome_utm( $campaign ) {
	return array(
		'utm_medium'   => true === FL_BUILDER_LITE ? 'bb-lite' : 'bb-pro',
		'utm_source'   => 'welcome-settings-page',
		'utm_campaign' => $campaign,
	);
}
$blog_post_url   = FLBuilderModel::get_store_url( 'beaver-builder-2-11', fl_welcome_utm( 'settings-welcome-blog-post' ) );
$change_logs_url = FLBuilderModel::get_store_url( 'change-logs', fl_welcome_utm( 'settings-welcome-change-logs' ) );
$upgrade_url     = FLBuilderModel::get_upgrade_url( fl_welcome_utm( 'settings-welcome-upgrade' ) );
$support_url     = FLBuilderModel::get_store_url( 'beaver-builder-support', fl_welcome_utm( 'settings-welcome-support' ) );
$faqs_url        = FLBuilderModel::get_store_url( 'frequently-asked-questions', fl_welcome_utm( 'settings-welcome-faqs' ) );
$forums_url      = FLBuilderModel::get_store_url( 'go/forum', fl_welcome_utm( 'settings-welcome-forums' ) );
$docs_url        = FLBuilderModel::get_store_url( 'go/docs', fl_welcome_utm( 'settings-welcome-docs' ) );
$fb_url          = 'https://www.facebook.com/groups/beaverbuilders/';
$release_ver     = '2.11';
$release_name    = '&#8220;Kariba&#8221;';
?>
<div id="fl-welcome-form" class="fl-settings-form">

	<h2 class="fl-settings-form-header"><?php _e( 'Welcome to Beaver Builder!', 'fl-builder' ); ?></h2>

	<div class="fl-settings-form-content fl-welcome-page-content">

		<p class="welcome-intro"><?php _e( 'Thank you for choosing Beaver Builder and welcome to the colony! Find some helpful information below.', 'fl-builder' ); ?>

			<?php if ( true === FL_BUILDER_LITE ) : ?>
				<?php /* translators: %s: upgrade url */ ?>
				<?php printf( __( 'For more time-saving features and access to our expert support team, <a href="%s" target="_blank">upgrade today</a>.', 'fl-builder' ), $upgrade_url ); ?>
			<?php else : ?>
				<?php _e( 'Be sure to add your license key for access to updates and new features.', 'fl-builder' ); ?>
			<?php endif; ?>

		</p>

		<!-- Getting Started -->
		<div class="fl-welcome-section">
			<div class="fl-welcome-section-header">
				<h3><?php _e( 'Getting Started', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Build your first page in minutes.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-welcome-cols">
				<div class="fl-welcome-col">
					<h4><?php _e( 'Building Your First Page', 'fl-builder' ); ?></h4>
					<p><?php _e( 'Ready to start building? Add a new page and jump into Beaver Builder by clicking the Launch Beaver Builder button.', 'fl-builder' ); ?></p>
					<a href="<?php echo admin_url(); ?>post-new.php?post_type=page" class="fl-button"><?php _e( 'Create a New Page', 'fl-builder' ); ?></a>
				</div>
				<div class="fl-welcome-col">
					<img role="presentation" class="fl-welcome-img" src="<?php echo FLBuilder::plugin_url(); ?>img/welcome-add_new.jpg" alt="" />
				</div>
			</div>
		</div>

		<!-- What's New -->
		<div class="fl-welcome-section">
			<div class="fl-welcome-section-header">
				<h3>
				<?php
				/* translators: %s: version number and release name */
				printf( __( "What's New in %s", 'fl-builder' ), $release_ver . ' ' . $release_name );
				?>
				</h3>
				<?php /* translators: 1: version: 2: codename*/ ?>
				<p><?php printf( __( 'Beaver Builder %1$s brings a number of workflow enhancements.', 'fl-builder' ), $release_ver ); ?></p>
			</div>
			<div class="fl-welcome-cols">
				<div class="fl-welcome-col">
					<ul class="fl-welcome-features">
						<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'NEW: Popup module and Popup layout type in the companion Beaver Themer release (1.6) with 9 Popup module templates. Build popups without the need for another plugin!', 'fl-builder' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'NEW: Edit Page settings without leaving the Builder.', 'fl-builder' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'Action Overlays in the UI have been redesigned to improve both performance and usability.', 'fl-builder' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span><?php _e( 'A Redesigned settings area with the addition of built-in Maintenance Mode and Settings Snapshots.', 'fl-builder' ); ?></li>
					</ul>
					<?php /* translators: 1: blog post url: 2: changelog url */ ?>
					<p class="fl-welcome-links"><?php printf( __( 'Read the full <a href="%1$s" target="_blank">update post</a> or view the <a href="%2$s" target="_blank">change logs</a>.', 'fl-builder' ), $blog_post_url, $change_logs_url ); ?></p>
				</div>
				<div class="fl-welcome-col">
					<a href="https://youtu.be/AHwjZFcxFns" target="_blank"><img class="fl-welcome-img" src="<?php echo FLBuilder::plugin_url(); ?>img/welcome-video_thumb--2.11.png" alt="" /></a>
				</div>
			</div>
		</div>

		<!-- Products -->
		<div class="fl-welcome-section">
			<div class="fl-welcome-section-header">
				<h3><?php _e( 'Even More Power', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Extend Beaver Builder with our companion products.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-welcome-products">
				<div class="fl-welcome-product">
					<a href="https://www.youtube.com/watch?v=KNpGTrCguEA" target="_blank"><img class="fl-welcome-img" src="<?php echo FLBuilder::plugin_url(); ?>img/video-beaver_themer.jpg" alt="" /></a>
					<h4><?php _e( 'Beaver Themer', 'fl-builder' ); ?></h4>
					<p><?php _e( 'Take full control of your entire website &mdash; headers, footers, archives, and more.', 'fl-builder' ); ?></p>
					<a href="https://www.wpbeaverbuilder.com/beaver-themer/" target="_blank" class="fl-button"><?php _e( 'Learn More', 'fl-builder' ); ?> <span class="dashicons dashicons-external"></span></a>
				</div>
				<div class="fl-welcome-product">
					<a href="https://www.youtube.com/watch?v=JtPeN_9Ns9o" target="_blank"><img class="fl-welcome-img" src="<?php echo FLBuilder::plugin_url(); ?>img/video-assistant.jpg" alt="" /></a>
					<h4><?php _e( 'Assistant', 'fl-builder' ); ?></h4>
					<p><?php _e( 'Access your design assets across all sites from a single dashboard.', 'fl-builder' ); ?></p>
					<a href="https://assistant.pro" target="_blank" class="fl-button"><?php _e( 'Learn More', 'fl-builder' ); ?> <span class="dashicons dashicons-external"></span></a>
				</div>
			</div>
		</div>

		<!-- Community & Help -->
		<div class="fl-welcome-section">
			<div class="fl-welcome-section-header">
				<h3><?php _e( 'Community & Support', 'fl-builder' ); ?></h3>
				<p><?php _e( 'Connect with other Beaver Builders and get help when you need it.', 'fl-builder' ); ?></p>
			</div>
			<div class="fl-welcome-cols">
				<div class="fl-welcome-col">
					<h4><?php _e( 'Join the Community', 'fl-builder' ); ?></h4>
					<p><?php _e( 'There\'s a wonderful community of Beaver Builders out there and we\'d love it if you joined us!', 'fl-builder' ); ?></p>
					<div class="fl-welcome-community-links">
						<a href="https://www.wpbeaverbuilder.com/go/bb-facebook" target="_blank" class="fl-welcome-community-link">
							<span class="dashicons dashicons-facebook"></span>
							<?php _e( 'Facebook', 'fl-builder' ); ?>
						</a>
						<a href="https://www.wpbeaverbuilder.com/go/bb-slack" target="_blank" class="fl-welcome-community-link">
							<span class="dashicons dashicons-format-chat"></span>
							<?php _e( 'Slack', 'fl-builder' ); ?>
						</a>
						<a href="https://www.wpbeaverbuilder.com/go/forum" target="_blank" class="fl-welcome-community-link">
							<span class="dashicons dashicons-admin-comments"></span>
							<?php _e( 'Forums', 'fl-builder' ); ?>
						</a>
						<a href="https://www.wpbeaverbuilder.com/discord" target="_blank" class="fl-welcome-community-link">
							<span class="dashicons dashicons-groups"></span>
							<?php _e( 'Discord', 'fl-builder' ); ?>
						</a>
					</div>
				</div>
				<div class="fl-welcome-col">
					<h4><?php _e( 'Need Some Help?', 'fl-builder' ); ?></h4>
					<p><?php _e( 'We take pride in offering outstanding support. The fastest way to find an answer is to check our knowledge base.', 'fl-builder' ); ?></p>
					<div class="fl-welcome-help-links">
						<a href="<?php echo $docs_url; ?>" target="_blank"><span class="dashicons dashicons-book-alt"></span> <?php _e( 'Knowledge Base', 'fl-builder' ); ?></a>
						<?php if ( true === FL_BUILDER_LITE ) : ?>
							<a href="<?php echo $upgrade_url; ?>" target="_blank"><span class="dashicons dashicons-star-filled"></span> <?php _e( 'Upgrade for Support', 'fl-builder' ); ?></a>
						<?php else : ?>
							<a href="<?php echo $support_url; ?>" target="_blank"><span class="dashicons dashicons-sos"></span> <?php _e( 'Contact Support', 'fl-builder' ); ?></a>
						<?php endif; ?>
						<a href="<?php echo $forums_url; ?>" target="_blank"><span class="dashicons dashicons-admin-comments"></span> <?php _e( 'Community Forums', 'fl-builder' ); ?></a>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
