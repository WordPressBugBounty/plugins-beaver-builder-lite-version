<?php

/**
 * Manages remote updates for all Beaver Builder products.
 *
 * @since 1.0
 */
final class FLUpdater {

	/**
	 * The API URL for the Beaver Builder update server.
	 *
	 * @since 1.0
	 * @access private
	 * @var string $_updates_api_url
	 */
	static private $_updates_api_url = 'https://updates.wpbeaverbuilder.com/';

	/**
	 * An internal array of data for each product.
	 *
	 * @since 1.0
	 * @access private
	 * @var array $_products
	 */
	static private $_products = array();

	/**
	 * An internal array of remote responses with
	 * update data for each product.
	 *
	 * @since 1.8.4
	 * @access private
	 * @var array $_responses
	 */
	static private $_responses = array();

	/**
	 * An internal array of settings for the updater instance.
	 *
	 * @since 1.0
	 * @access private
	 * @var array $settings
	 */
	private $settings = array();

	/**
	 * Updater constructor method.
	 *
	 * @since 1.0
	 * @param array $settings An array of settings for this instance.
	 * @return void
	 */
	public function __construct( $settings = array() ) {
		$this->settings = $settings;

		if ( 'plugin' == $settings['type'] ) {
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
			add_filter( 'site_transient_update_plugins', array( $this, 'site_transient_update_plugins' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_info' ), 99, 3 );
			add_action( 'in_plugin_update_message-' . self::get_plugin_file( $settings['slug'] ), array( $this, 'update_message' ), 1, 2 );
			add_action( 'admin_init', array( $this, 'wp_update_notice' ) );
		} elseif ( 'theme' == $settings['type'] ) {
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_check' ) );
		}
		add_action( 'fl_builder_cache_cleared', function () {
			delete_transient( 'fl_get_subscription_info' );
		} );
	}

	/**
	 * Get the update data response from the API.
	 *
	 * @since 1.7.7
	 * @return object
	 */
	public function get_response() {
		$slug = $this->settings['slug'];

		if ( isset( FLUpdater::$_responses[ $slug ] ) ) {
			return FLUpdater::$_responses[ $slug ];
		}
		if ( function_exists( 'wp_get_wp_version' ) ) {
			$wp_version = wp_get_wp_version();
		} else {
			require ABSPATH . WPINC . '/version.php';
		}
		FLUpdater::$_responses[ $slug ] = FLUpdater::api_request(
			FLUpdater::$_updates_api_url,
			array(
				'fl-api-method' => 'update_info',
				'license'       => FLUpdater::get_subscription_license(),
				'domain'        => FLUpdater::validate_domain( network_home_url() ),
				'product'       => $this->settings['name'],
				'slug'          => $this->settings['slug'],
				'version'       => self::verify_version( $this->settings['version'] ),
				'php'           => phpversion(),
				'wp'            => $wp_version,
			)
		);

		return FLUpdater::$_responses[ $slug ];
	}

	/**
	 * @since 2.10
	 */
	public function site_transient_update_plugins( $response ) {

		if ( ! is_object( $response ) ) {
			return $response;
		}

		$slug         = $this->settings['slug'];
		$version      = $this->settings['version'];
		$response_obj = isset( $response->response ) ? (array) $response->response : new StdClass();

		foreach ( $response_obj as $k => $obj ) {
			if ( isset( $obj->slug, $obj->new_version ) && $slug === $obj->slug && $version === $obj->new_version ) {
				unset( $response->response[ $k ] );
			}
		}
		return $response;
	}

	/**
	 * Checks to see if an update is available for the current product.
	 *
	 * @since 1.0
	 * @param object $transient A WordPress transient object with update data.
	 * @return object
	 */
	public function update_check( $transient ) {
		global $pagenow, $wp_version;

		if ( 'plugins.php' == $pagenow && is_multisite() ) {
			return $transient;
		}
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}
		if ( ! isset( $transient->checked ) ) {
			$transient->checked = array();
		}

		$response = $this->get_response();
		if ( ! isset( $response->error ) || ( isset( $response->error ) && 'No update available.' === $response->error ) ) {

			$transient->last_checked                       = time();
			$transient->checked[ $this->settings['slug'] ] = $this->settings['version'];

			if ( 'plugin' == $this->settings['type'] ) {

				$plugin = self::get_plugin_file( $this->settings['slug'] );

				$plugin_version_check = self::verify_version( $this->settings['version'] );
				if ( isset( $response->new_version ) ) {
					if ( false === strpos( $response->new_version, 'alpha' ) ) {
						$plugin_version_check = rtrim( $plugin_version_check, '-alpha' );
					}
					if ( false === strpos( $response->new_version, 'beta' ) ) {
						$plugin_version_check = rtrim( $plugin_version_check, '-beta' );
					}
				}

				if ( isset( $response->new_version ) && version_compare( $response->new_version, $plugin_version_check, '>' ) ) {

					$transient->response[ $plugin ]               = new stdClass();
					$transient->response[ $plugin ]->slug         = $response->slug;
					$transient->response[ $plugin ]->plugin       = $plugin;
					$transient->response[ $plugin ]->new_version  = $response->new_version;
					$transient->response[ $plugin ]->url          = $response->homepage;
					$transient->response[ $plugin ]->package      = $response->package;
					$transient->response[ $plugin ]->tested       = $response->tested;
					$transient->response[ $plugin ]->requires_php = $response->requires_php;
					$transient->response[ $plugin ]->icons        = apply_filters(
						'fl_updater_icon',
						array(
							'1x'      => FLBuilder::plugin_url() . 'img/beaver-128.png',
							'2x'      => FLBuilder::plugin_url() . 'img/beaver-256.png',
							'default' => FLBuilder::plugin_url() . 'img/beaver-256.png',
						),
						$response,
						$this->settings
					);

					if ( empty( $response->package ) ) {
						$transient->response[ $plugin ]->upgrade_notice = FLUpdater::get_update_error_message( false, $this->settings );
					}
					if ( version_compare( $wp_version, '6.7', '<' ) ) {
						$transient->response[ $plugin ]->upgrade_notice = $this->get_wp_66_text();
					}
				} else {
					// no update, for wp 5.5 we have to add a mock item.
					$item = (object) array(
						'id'            => $plugin,
						'slug'          => $this->settings['slug'],
						'plugin'        => $plugin,
						'new_version'   => $this->settings['version'],
						'url'           => '',
						'package'       => '',
						'icons'         => array(),
						'banners'       => array(),
						'banners_rtl'   => array(),
						'tested'        => '',
						'requires_php'  => '',
						'compatibility' => new stdClass(),
					);
					// Adding the "mock" item to the `no_update` property is required
					// for the enable/disable auto-updates links to correctly appear in UI.
					$transient->no_update[ $plugin ] = $item;
				}
			} elseif ( 'theme' == $this->settings['type'] ) {
				$theme_version_check = self::verify_version( $this->settings['version'] );
				if ( isset( $response->new_version ) ) {
					if ( false === strpos( $response->new_version, 'alpha' ) ) {
						$theme_version_check = rtrim( $theme_version_check, '-alpha' );
					}
					if ( false === strpos( $response->new_version, 'beta' ) ) {
						$theme_version_check = rtrim( $theme_version_check, '-beta' );
					}
				}
				if ( isset( $response->new_version ) && version_compare( $response->new_version, $theme_version_check, '>' ) ) {

					$transient->response[ $this->settings['slug'] ] = array(
						'new_version'  => $response->new_version,
						'theme'        => $this->settings['slug'],
						'url'          => $response->homepage,
						'package'      => $response->package,
						'tested'       => $response->tested,
						'requires_php' => $response->requires_php,
					);
				} else {
					// no update, for wp 5.5 we have to add a mock item.
					$item = array(
						'theme'        => $this->settings['slug'],
						'new_version'  => $this->settings['version'],
						'url'          => '',
						'package'      => '',
						'requires'     => '',
						'requires_php' => '',
					);
					// Adding the "mock" item to the `no_update` property is required
					// for the enable/disable auto-updates links to correctly appear in UI.
					$transient->no_update[ $this->settings['slug'] ] = $item;
				}
			}
		}

		return $transient;
	}

	/**
	 * Retrieves the data for the plugin info lightbox.
	 *
	 * @since 1.0
	 * @param bool $result
	 * @param string $action
	 * @param object $args
	 * @return object|bool
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' != $action ) {
			return $result;
		}
		if ( ! isset( $args->slug ) || $args->slug != $this->settings['slug'] ) {
			return $result;
		}

		$response  = $this->get_response();
		$changelog = __( 'Could not locate changelog.txt', 'fl-builder' );

		if ( ! isset( $response->error ) ) {

			$info                = new stdClass();
			$info->name          = $this->settings['name'];
			$info->version       = $response->new_version;
			$info->slug          = $response->slug;
			$info->plugin_name   = $response->plugin_name;
			$info->author        = $response->author;
			$info->homepage      = $response->homepage;
			$info->requires      = $response->requires;
			$info->requires_php  = $response->requires_php;
			$info->tested        = $response->tested;
			$info->last_updated  = $response->last_updated;
			$info->download_link = $response->package;
			$info->sections      = (array) $response->sections;
			/**
			 * Plugin information data array used to populate the plugin details popup.
			 */
			return apply_filters( 'fl_plugin_info_data', $info, $response );
		} else {
			if ( 'bb-plugin' === $this->settings['slug'] && file_exists( trailingslashit( plugin_dir_path( FL_BUILDER_FILE ) ) . '/changelog.txt' ) ) {
				$changelog = file_get_contents( trailingslashit( plugin_dir_path( FL_BUILDER_FILE ) ) . '/changelog.txt' );
			}
			if ( 'bb-theme-builder' === $this->settings['slug'] && file_exists( trailingslashit( plugin_dir_path( FL_THEME_BUILDER_FILE ) ) . '/changelog.txt' ) ) {
				$changelog = file_get_contents( trailingslashit( plugin_dir_path( FL_THEME_BUILDER_FILE ) ) . '/changelog.txt' );
			}
			$info              = new stdClass();
			$info->name        = $this->settings['name'];
			$info->version     = $this->settings['version'];
			$info->slug        = $this->settings['slug'];
			$info->plugin_name = $this->settings['name'];
			$info->homepage    = 'https://www.wpbeaverbuilder.com/';

			$info->sections              = array();
			$info->sections['changelog'] = $changelog;
			/**
			 * Plugin information data array used to populate the plugin details popup.
			 */
			return apply_filters( 'fl_plugin_info_data', $info, $response );
		}

		return $result;
	}

	/**
	 * Shows an update message on the plugins page if an update
	 * is available but there is no active subscription.
	 *
	 * @since 1.0
	 * @param array $plugin_data An array of data for this plugin.
	 * @param object $response An object with update data for this plugin.
	 * @return void
	 */
	public function update_message( $plugin_data, $response ) {
		global $wp_version;
		if ( empty( $response->package ) ) {
			echo FLUpdater::get_update_error_message( $plugin_data );
		}
		if ( version_compare( $wp_version, '6.7', '<' ) ) {
			printf( '<span style="display:block;margin:10px 0;">%s</span>', $this->get_wp_66_text() );
		}
	}

	/**
	 * Static method for initializing an instance of the updater
	 * for each active product.
	 *
	 * @since 1.0
	 * @return void
	 */
	static public function init() {
		include FL_UPDATER_DIR . 'includes/config.php';

		foreach ( $config as $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}

	/**
	 * Static method for adding a product to the updater and
	 * creating the new instance.
	 *
	 * @since 1.0
	 * @param array $args An array of settings for the product.
	 * @return void
	 */
	static public function add_product( $args = array() ) {
		if ( is_array( $args ) && isset( $args['slug'] ) ) {

			if ( 'plugin' == $args['type'] ) {
				if ( file_exists( trailingslashit( WP_PLUGIN_DIR ) . $args['slug'] ) ) {
					$args['version']                  = self::verify_version( $args['version'] );
					self::$_products[ $args['name'] ] = $args;
					new FLUpdater( self::$_products[ $args['name'] ] );
				}
			}
			if ( 'theme' == $args['type'] ) {
				if ( file_exists( WP_CONTENT_DIR . '/themes/' . $args['slug'] ) ) {
					$args['version']                  = self::verify_version( $args['version'] );
					self::$_products[ $args['name'] ] = $args;
					new FLUpdater( self::$_products[ $args['name'] ] );
				}
			}
		}
	}

	/**
	 * Static method for rendering the license form.
	 *
	 * @since 1.0
	 * @return void
	 */
	static public function render_form() {
		// Activate a subscription?
		if ( isset( $_POST['fl-updater-nonce'] ) ) {
			if ( wp_verify_nonce( $_POST['fl-updater-nonce'], 'updater-nonce' ) ) {
				$response = self::save_subscription_license( $_POST['license'] );
				if ( '' == $_POST['license'] ) {
					$response->error = __( 'License Removed', 'fl-builder' );
				}
				if ( isset( $response->error ) ) {
					unset( $_POST['fl-updater-nonce'] );
					FLBuilderAdminSettings::add_error( $response->error );
					FLBuilderAdminSettings::render_update_message();
				}
			}
		}

		$license      = self::get_subscription_license();
		$subscription = self::get_subscription_info();

		// Include the form ui.
		include FL_UPDATER_DIR . 'includes/form.php';
	}

	/**
	 * Renders available subscriptions and downloads.
	 *
	 * @since 1.10
	 * @param object $subscription
	 * @return void
	 */
	static public function render_subscriptions( $subscription ) {
		if ( isset( $subscription->error ) || ! $subscription->active || ! $subscription->domain->active || ! isset( $subscription->downloads ) ) {
			return;
		}
		if ( ! FLBuilderModel::is_white_labeled() ) {
			include FL_UPDATER_DIR . 'includes/subscriptions.php';
		}
	}

	/**
	 * Static method for getting the subscription license key.
	 *
	 * @since 1.0
	 * @return string
	 */
	static public function get_subscription_license() {
		$value = get_site_option( 'fl_themes_subscription_email' );

		return $value ? $value : '';
	}

	/**
	 * Static method for updating the subscription license.
	 *
	 * @since 1.0
	 * @param string $license The new license key.
	 * @return $response mixed
	 */
	static public function save_subscription_license( $license ) {

		if ( preg_match( '/[^a-zA-Z\d\s@\.\-_]/', $license ) ) {
			$response        = new StdClass();
			$response->error = __( 'You submitted an invalid license. Non alphanumeric characters found.', 'fl-builder' );
			return $response;
		}
		$response = FLUpdater::api_request(
			self::$_updates_api_url,
			array(
				'fl-api-method' => 'activate_domain',
				'license'       => $license,
				'domain'        => FLUpdater::validate_domain( network_home_url() ),
				'products'      => json_encode( self::$_products ),
			)
		);
		if ( isset( $response->error ) && ! isset( $response->code ) ) {
			$license = '';
		}
		update_site_option( 'fl_themes_subscription_email', $license );
		delete_transient( 'fl_get_subscription_info' );
		if ( $license ) {
			delete_site_transient( 'update_plugins' );
			delete_site_transient( 'update_themes' );
		}
		return $response;
	}


	/**
	 * Static method for retrieving the subscription info.
	 *
	 * @since 1.0
	 * @return bool
	 */
	static public function get_subscription_info() {
		$license = FLUpdater::get_subscription_license();
		if ( ! $license ) {
			$subscription_info         = new StdClass();
			$subscription_info->active = false;
			return $subscription_info;
		}
		//phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
		if ( false === ( $subscription_info = get_transient( 'fl_get_subscription_info' ) ) ) {
			$subscription_info = self::api_request(
				self::$_updates_api_url,
				array(
					'fl-api-method' => 'subscription_info',
					'domain'        => FLUpdater::validate_domain( network_home_url() ),
					'license'       => $license,
				)
			);
			if ( is_object( $subscription_info ) && ! isset( $subscription_info->error ) ) {
				set_transient( 'fl_get_subscription_info', $subscription_info );
			}
		}
		if ( is_object( $subscription_info ) && ! isset( $subscription_info->active ) ) {
			$subscription_info->active = false;
		}
		return $subscription_info;
	}

	/**
	 * Returns an update message for if an update
	 * is available but there is no active subscription.
	 *
	 * @since 1.6.4.3
	 * @param array $plugin_data An array of data for this plugin.
	 * @return string
	 */
	static public function get_update_error_message( $plugin_data = null, $settings = false ) {

		$subscription    = FLUpdater::get_subscription_info();
		$license         = get_site_option( 'fl_themes_subscription_email' );
		$message         = '';
		$check_downloads = self::check_downloads( $subscription, $plugin_data, $settings );

		// updates-core.php
		if ( ! $plugin_data ) {

			if ( ! $license ) {
				$message = __( 'Please enter a valid license key to enable automatic updates.', 'fl-builder' );

			} else {
				$message = __( 'Please subscribe to enable automatic updates for this plugin.', 'fl-builder' );
				if ( $check_downloads ) {
					$message = $check_downloads;
				}

				if ( isset( $subscription->error ) && '' !== $subscription->error ) {
					$message .= sprintf( ' The following error was encountered: %s', $subscription->error );
				}
			}
		} else { // plugins.php
			// Tier-downgrade warning keeps the legacy banner — separate problem from renewal.
			if ( $check_downloads ) {
				$message .= '<span style="display:block;padding:10px 20px;margin:10px 0; background: #d54e21; color: #fff;">';
				$message .= $check_downloads;
				$message .= '</span>';
				return $message;
			}
			$message = self::render_renewal_notice( $plugin_data, $subscription, $license );
		}
		return $message;
	}

	/**
	 * Renders the redesigned renewal notice shown on plugins.php
	 * when a license is missing or its subscription is inactive.
	 *
	 * @since 2.10
	 * @param array    $plugin_data
	 * @param stdClass $subscription
	 * @param string   $license
	 * @return string
	 */
	static private function render_renewal_notice( $plugin_data, $subscription, $license ) {
		$is_admin     = current_user_can( 'manage_options' );
		$has_license  = ! empty( $license );
		$settings_url = admin_url( 'options-general.php?page=fl-builder-settings#license' );

		// Identify the product so the copy and store routing match it.
		$is_themer = isset( $plugin_data['plugin'] ) && 'bb-theme-builder/bb-theme-builder.php' === $plugin_data['plugin'];
		$product   = $is_themer ? __( 'Beaver Themer', 'fl-builder' ) : __( 'Beaver Builder', 'fl-builder' );

		// Renewal/subscribe destination — match the existing per-product routing.
		if ( $is_themer ) {
			$store_url = FLBuilderModel::get_store_url( 'beaver-themer', array(
				'utm_medium'   => 'bb-theme-builder',
				'utm_source'   => 'plugins-admin-page',
				'utm_campaign' => $has_license ? 'renew' : 'subscribe',
			) );
		} else {
			$store_url = FLBuilderModel::get_store_url( '', array(
				'utm_medium'   => 'bb',
				'utm_source'   => 'plugins-admin-page',
				'utm_campaign' => $has_license ? 'renew' : 'subscribe',
			) );
		}

		// Copy and CTA destination vary based on whether a license is on file.
		// With no key, the action is to enter one, so point at the in-app license
		// settings page rather than the external store.
		if ( $has_license ) {
			// translators: %s: product name (Beaver Builder or Beaver Themer).
			$headline     = sprintf( __( 'Your %s license expired', 'fl-builder' ), $product );
			$cta_text     = __( 'Renew License', 'fl-builder' );
			$cta_url      = $store_url;
			$cta_external = true;
		} else {
			// translators: %s: product name (Beaver Builder or Beaver Themer).
			$headline     = sprintf( __( "%s isn't licensed on this site", 'fl-builder' ), $product );
			$cta_text     = __( 'Enter License Key', 'fl-builder' );
			$cta_url      = $settings_url;
			$cta_external = false;
		}

		// Themer typically appears right below the Beaver Builder notice, so keep it
		// short and skip the shared bullet list rather than repeating it on one screen.
		if ( $is_themer ) {
			$lede    = $has_license
				? __( 'Beaver Themer keeps working. Updates and support are paused until you renew.', 'fl-builder' )
				: __( 'Beaver Themer keeps working. Updates and support are paused until a license is added.', 'fl-builder' );
			$bullets = array();
		} else {
			$lede    = $has_license
				? __( 'Beaver Builder will keep running. The updates and support that keep this site stable are paused until the license is renewed.', 'fl-builder' )
				: __( 'Beaver Builder will keep running. The updates and support that keep this site stable are paused until a license is added.', 'fl-builder' );
			$bullets = array(
				__( 'Updates for new WordPress and PHP versions', 'fl-builder' ),
				__( "Bug fixes that won't break the rest of the site", 'fl-builder' ),
				__( 'Real-human support when something needs untangling', 'fl-builder' ),
			);
		}

		// translators: %s: product name (Beaver Builder or Beaver Themer).
		$forward_note = sprintf( __( 'Not your license? Contact whoever set up %s to renew it.', 'fl-builder' ), $product );

		// All-span structure: WP wraps this hook's output in a <p>, so we stay inside it
		// and use display:block spans. Avoids the orphan </p> that browsers turn into an
		// empty <p></p> — and WP's `.update-message p::before` would stamp a refresh icon
		// on every such paragraph.
		// Color palette mirrors the .fl-license-notice-error styles on the license settings
		// page so the two surfaces feel like the same notice in two contexts.
		$styles = array(
			'wrap'         => 'display:block; padding:14px 18px; margin:14px 20px 8px; background:#fef2f0; border:1px solid #f0b8b1; border-radius:8px; box-sizing:border-box;',
			'headline_row' => 'display:flex; align-items:center; gap:8px; margin:0 0 6px;',
			'headline_ico' => 'flex:0 0 auto; color:#d63638; font-size:20px; width:20px; height:20px; line-height:1;',
			'headline'     => 'font-size:15px; font-weight:600; color:#1d2327; line-height:1.3;',
			'lede'         => 'display:block; color:#8a1708; margin:0 0 10px; line-height:1.5; font-size:13.5px;',
			'list'         => 'display:block; margin:0 0 12px;',
			'item'         => 'display:block; padding:3px 0 3px 16px; position:relative; line-height:1.5; color:#8a1708; font-size:13px;',
			'bullet'       => 'position:absolute; left:0; top:3px; color:#d63638; font-weight:700;',
			'actions'      => 'display:flex; flex-wrap:wrap; align-items:center; gap:14px;',
			'button'       => 'display:inline-block; background:#EE521F; color:#fff !important; text-decoration:none; font-weight:600; font-size:13px; padding:7px 16px; border-radius:6px; border:1px solid #d4471a; line-height:1.4;',
			'link'         => 'font-size:13px; color:#8a1708; text-decoration:underline; font-weight:600;',
			'nonadmin'     => 'display:block; color:#8a1708; font-size:13px; line-height:1.5; max-width:60ch;',
			'secondary'    => 'display:block; margin-top:10px; color:#787c82; font-size:12.5px; line-height:1.5;',
		);

		ob_start();
		?>
		<span class="bb-renewal-notice" data-bb-notice="renewal" style="<?php echo esc_attr( $styles['wrap'] ); ?>">
			<span style="<?php echo esc_attr( $styles['headline_row'] ); ?>">
				<span class="dashicons dashicons-warning" aria-hidden="true" style="<?php echo esc_attr( $styles['headline_ico'] ); ?>"></span>
				<span style="<?php echo esc_attr( $styles['headline'] ); ?>"><?php echo esc_html( $headline ); ?></span>
			</span>
			<span style="<?php echo esc_attr( $styles['lede'] ); ?>"><?php echo esc_html( $lede ); ?></span>
			<?php if ( ! empty( $bullets ) ) : ?>
			<span style="<?php echo esc_attr( $styles['list'] ); ?>">
				<?php foreach ( $bullets as $bullet ) : ?>
					<span style="<?php echo esc_attr( $styles['item'] ); ?>">
						<span style="<?php echo esc_attr( $styles['bullet'] ); ?>">&bull;</span>
						<?php echo esc_html( $bullet ); ?>
					</span>
				<?php endforeach; ?>
			</span>
			<?php endif; ?>
			<span style="<?php echo esc_attr( $styles['actions'] ); ?>">
				<?php if ( $is_admin ) : ?>
					<a href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_external ? ' target="_blank" rel="noopener"' : ''; ?> style="<?php echo esc_attr( $styles['button'] ); ?>">
						<?php echo esc_html( $cta_text ); ?>
					</a>
					<?php if ( $has_license ) : ?>
						<a href="<?php echo esc_url( $settings_url ); ?>" style="<?php echo esc_attr( $styles['link'] ); ?>">
							<?php esc_html_e( 'License settings', 'fl-builder' ); ?>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<span style="<?php echo esc_attr( $styles['nonadmin'] ); ?>">
						<?php echo esc_html( $forward_note ); ?>
					</span>
				<?php endif; ?>
			</span>
			<?php if ( $is_admin ) : ?>
				<span style="<?php echo esc_attr( $styles['secondary'] ); ?>"><?php echo esc_html( $forward_note ); ?></span>
			<?php endif; ?>
		</span>
		<?php
		return ob_get_clean();
	}

	static private function check_downloads( $subscription, $plugin_data, $settings ) {

		$plugin_name = ( ! $plugin_data ) ? $settings['name'] : $plugin_data['Name'];
		$out         = '';

		if ( '{FL_BUILDER_NAME}' !== $plugin_name && isset( $subscription->downloads ) && ! in_array( $plugin_name, $subscription->downloads, true ) ) {

			$show_warning = false;
			$version      = '';

			// find available plugin Version
			foreach ( $subscription->downloads as $ver ) {
				if ( stristr( $ver, 'Beaver Builder Plugin' ) ) {
					preg_match( '#\((.*)\sVersion\)$#', $ver, $match );
					$version = ( isset( $match[1] ) ) ? $match[1] : false;
					break;
				}
			}

			switch ( $plugin_name ) {
				// pro - show warning if standard is pnly available version
				case 'Beaver Builder Plugin (Pro Version)':
					$show_warning = ( 'Standard' === $version ) ? true : false;
					break;
				// agency show warning if available is NOT agency
				case 'Beaver Builder Plugin (Agency Version)':
					$show_warning = ( 'Agency' !== $version ) ? true : false;
					break;
				case 'Beaver Themer':
					$show_warning = true;
					break;
			}

			if ( ! $version ) {
				$show_warning = true;
			}

			if ( $show_warning ) {
				if ( ! $version ) {
					// translators: %1$s: Plugin name
					$out .= sprintf( __( 'Updates for Beaver Builder will not work as you appear to have %1$s activated but you have no active subscription.', 'fl-builder' ), '<strong>' . $plugin_name . '</strong>', $version );
				} else {
					// translators: %2$s: Plugin name
					$out .= sprintf( __( 'Updates for Beaver Builder will not work as you appear to have %1$s activated but your license is for %2$s version.', 'fl-builder' ), '<strong>' . $plugin_name . '</strong>', $version );
				}

				if ( 'Beaver Themer' === $plugin_name ) {
					$out = __( 'Updates for Themer will not work as you do not have a valid subscription for this plugin.', 'fl-builder' );
				}
			}
		}
		return $out;
	}

	/**
	 * Static method for retrieving the plugin file path for a
	 * product relative to the plugins directory.
	 *
	 * @since 1.0
	 * @access private
	 * @param string $slug The product slug.
	 * @return string
	 */
	static private function get_plugin_file( $slug ) {
		if ( 'bb-plugin' == $slug ) {
			$file = $slug . '/fl-builder.php';
		} else {
			$file = $slug . '/' . $slug . '.php';
		}

		return $file;
	}

	/**
	 * Static method for sending a request to the store
	 * or update API.
	 *
	 * @since 1.0
	 * @access private
	 * @param string $api_url The API URL to use.
	 * @param array $args An array of args to send along with the request.
	 * @return mixed The response or false if there is an error.
	 */
	static private function api_request( $api_url = false, $args = array() ) {
		if ( $api_url ) {

			$params = array();

			foreach ( $args as $key => $val ) {
				$params[] = $key . '=' . urlencode( $val );
			}

			return self::remote_get( $api_url . '?' . implode( '&', $params ) );
		}

		return false;
	}

	/**
	 * Get a remote response.
	 *
	 * @since 1.0
	 * @access private
	 * @param string $url The URL to get.
	 * @param array $args
	 * @return mixed The response or false if there is an error.
	 */
	static private function remote_get( $url, $args = array( 'timeout' => 25 ) ) {
		$request      = wp_remote_get( $url, $args );
		$error        = new stdClass();
		$error->error = 'Unknown Error';
		$error->code  = true;

		if ( is_wp_error( $request ) ) {
			$error->error = $request->get_error_message();
			return $error;
		}

		$response = wp_remote_retrieve_response_code( $request );

		if ( 200 !== $response ) {
			$error->error = sprintf( '%s response from server', $response );
			return $error;
		}

		$body = wp_remote_retrieve_body( $request );

		if ( is_wp_error( $body ) ) {
			$error->error = $body->get_error_message();
			return $error;
		}

		$body_decoded = json_decode( $body );

		if ( ! is_object( $body_decoded ) ) {
			return $error;
		}

		return $body_decoded;
	}

	/**
	 * Validate domain and strip any query params
	 * @since 2.3
	 */
	private static function validate_domain( $url ) {
		$pos = strpos( $url, '?' );
		$url = ( $pos ) ? untrailingslashit( substr( $url, 0, $pos ) ) : $url;
		return $url;
	}

	static public function verify_version( $version ) {

		if ( get_option( 'fl_beta_updates', false ) ) {
			// if version already is beta strip -beta.
			$version = rtrim( $version, '-beta' );
			if ( false === strpos( $version, 'beta' ) ) {
				$version .= '-beta';
			}
		}

		if ( get_option( 'fl_alpha_updates', false ) ) {
			// if version already is beta strip -beta.
			$version = rtrim( $version, '-beta' );
			$version = rtrim( $version, '-alpha' );
			if ( false === strpos( $version, 'alpha' ) ) {
				$version .= '-alpha';
			}
		}
		return $version;
	}

	/**
	 * Add notice for users of WP 6.6 and lower
	 * @since 2.9
	 */
	public function wp_update_notice() {
		global $wp_version;
		if ( version_compare( $wp_version, '6.6', '<' ) ) {
			$args = array(
				'id'      => 'required-version',
				'cap'     => 'edit_posts',
				'content' => $this->get_wp_66_text(),
				'class'   => 'notice-warning',
			);
			FLBuilderAdminNotices::register_notice( $args );
		}
	}

	/**
	 * Get notice text
	 * @since 2.9
	 */
	private function get_wp_66_text() {
		return sprintf( '<span class="dashicons dashicons-warning"></span>&nbsp;<strong>%s</strong>', __( 'In version 2.10 of Beaver Builder, the required version of WordPress will be raised to 6.6', 'fl-builder' ) );
	}
}
