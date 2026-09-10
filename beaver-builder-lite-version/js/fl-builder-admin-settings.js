(function($){

	/**
	 * Helper class for dealing with the builder's admin
	 * settings page.
	 *
	 * @class FLBuilderAdminSettings
	 * @since 1.0
	 */
	FLBuilderAdminSettings = {

		/**
		 * An instance of wp.media used for uploading icons.
		 *
		 * @since 1.4.6
		 * @access private
		 * @property {Object} _iconUploader
		 */
		_iconUploader: null,

		/**
		 * Initializes the builder's admin settings page.
		 *
		 * @since 1.0
		 * @method init
		 */
		init: function()
		{
			this._bind();
			//this._maybeShowWelcome();
			this._initNav();
			this._initNetworkOverrides();
			this._initLicenseSettings();
			this._initMultiSelects();
			this._initUserAccessSelects();
			this._initUserAccessNetworkOverrides();
			this._templatesOverrideChange();
			this._iconPro();
			this._alphaSettings();
			this._initModuleUsageLayout();
		},

		/**
		 * Binds events for the builder's admin settings page.
		 *
		 * @since 1.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$('.fl-settings-nav a').on('click', FLBuilderAdminSettings._navClicked);
			$(window).on('hashchange', FLBuilderAdminSettings._initNav);
			$('.fl-override-ms-cb').on('click', FLBuilderAdminSettings._overrideCheckboxClicked);
			$('.fl-ua-override-ms-cb').on('click', FLBuilderAdminSettings._overrideUserAccessCheckboxClicked);
			$('.fl-module-all-cb').on('click', FLBuilderAdminSettings._moduleAllCheckboxClicked);
			$('.fl-module-cb').on('click', FLBuilderAdminSettings._moduleCheckboxClicked);
			$('input[name=fl-templates-override]').on('keyup click', FLBuilderAdminSettings._templatesOverrideChange);
			$('input[name=fl-upload-icon]').on('click', FLBuilderAdminSettings._showIconUploader);
			$('.fl-delete-icon-set').on('click', FLBuilderAdminSettings._deleteCustomIconSet);
			$('.fl-toggle-group-header').attr({ tabindex: '0', role: 'button', 'aria-expanded': 'false' })
				.on('click', FLBuilderAdminSettings._toggleGroupClicked)
				.on('keydown', FLBuilderAdminSettings._toggleGroupKeydown);
			$('.fl-group-toggle-cb').on('click', FLBuilderAdminSettings._groupToggleClicked);
			$('#cache-form').on('submit', FLBuilderAdminSettings._clearCacheSubmit);
			$('#uninstall-form').on('submit', FLBuilderAdminSettings._uninstallFormSubmit);
			$('.fl-module-filter').on('click', FLBuilderAdminSettings._moduleFilterClicked);
			$('.fl-module-search').on('input', FLBuilderAdminSettings._moduleSearchInput);
			$( '.fl-settings-form .dashicons-editor-help' ).tipTip();
			$( '.subscription-form .subscribe-button' ).on( 'click', FLBuilderAdminSettings._welcomeSubscribe);
			$( '.advanced-group input[type=checkbox]').on('change', FLBuilderAdminSettings._advancedToggle );
			$( '.advanced-group input[type=text]').on('keyup', FLBuilderAdminSettings._advancedText );
			$( '.advanced-group h3' ).on('click', FLBuilderAdminSettings._advancedShowHide );
			$( '.advanced-group button' ).on('click', FLBuilderAdminSettings._advancedTextSave );
			$( '#fl-icons-form .fl-icon-set-toggle' ).on( 'change', FLBuilderAdminSettings._iconSetToggleChanged );
			$( '#fl-icons-form .fl-fa-pro-toggle' ).on( 'change', FLBuilderAdminSettings._faProToggleChanged );
			$( '.fl-debug-toggle' ).on( 'change', FLBuilderAdminSettings._debugToggleChanged );
			$( '.fl-usage-toggle' ).on( 'change', FLBuilderAdminSettings._usageToggleChanged );
			$( '.fl-cache-plugins-toggle' ).on( 'change', FLBuilderAdminSettings._cachePluginsToggleChanged );
			$( '.fl-cache-varnish-toggle' ).on( 'change', FLBuilderAdminSettings._cacheVarnishToggleChanged );
			$( '.fl-release-channel-card input[type="radio"]' ).on( 'change', FLBuilderAdminSettings._releaseChannelChanged );
			$( '.fl-template-visibility-select' ).on( 'change', FLBuilderAdminSettings._templateVisibilityChanged );
			$( '.fl-templates-override-toggle' ).on( 'change', FLBuilderAdminSettings._templatesOverrideToggleChanged );
			$( '.fl-templates-override-input' ).on( 'change', FLBuilderAdminSettings._templatesOverrideInputChanged );
		},

		_welcomeSubscribe: function()
		{
			form  = $('.subscription-form')
			var error = form.find('.error')
			var spinner = form.find('.dashicons')

			if( error.css('display') != 'none' ) {
				error.hide()
			}

			name   = form.find( '.input-group-field.name').val()
			email  = form.find( '.input-group-field.email').val()
			nonce  = form.find( '#_wpnonce' ).val()

			if ( ! email || ! name ) {
				error.html('Please enter required fields').fadeIn()
				return false;
			}
			spinner.css('color', '#fff')
			spinner.addClass('spin')

			data = {
				'action'  : 'fl_welcome_submit',
				'name'    : name,
				'email'   : email,
				'_wpnonce': nonce
			}

			console.log(data)

			$.post(ajaxurl, data, function(response) {
				spinner.css( 'color', '#0a3c4b' );
				spinner.removeClass( 'spin' );
				if( response.success ) {
					$('.subscribe-button').hide()
					$('.input-group').remove()
					spinner.remove()
					$( error ).html( '<h2>' + response.data.message + '</h2>' ).fadeIn()
				} else {
					$( error ).html(response.data.message).fadeIn()
				}
			});
		},

		_advancedText: function() {
			button = $(this).parent().find('.save-button');
			button.css('display', 'contents')
		},

		_advancedTextSave: function(e) {
			e.preventDefault();
			var buttonwrap = $(this).parent()

			id = $(this).data('id');
			value = $('#' + id).val();
			data = {
				'action'  : 'fl_advanced_submit',
				'setting' : id,
				'type'    : 'text',
				'value'   : value,
				'_wpnonce': $('#fl-advanced-nonce').val()
			}
			$.post(ajaxurl, data, function(response) {
				if ( response.success ) {
					buttonwrap.fadeOut();
				new Notify({
					status: 'success',
					title: 'Saved',
					autoclose: true,
					autotimeout: 1000,
					distance: 20,
				});
			} else {
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: false,
					distance: 20,
				});
			}
			})
			.fail( function(){
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: false,
					distance: 20,
				});
			});
		},

		_advancedToggle: function(event) {
			checkbox = $(this);
			var depend = $(this).data('depend') || false;
			data = {
				'action'  : 'fl_advanced_submit',
				'setting' : checkbox.attr('name'),
				'value'   : checkbox.is(':checked'),
				'_wpnonce': $('#fl-advanced-nonce').val()
			}
			$.post(ajaxurl, data, function(response) {
				if ( response.success ) {
					$.when(
				new Notify({
					status: 'success',
					title: 'Saved',
					autoclose: true,
					autotimeout: 1000,
					distance: 20,
				})
			).done(function(){
				if ( depend ) {
					location.reload()
				}
			});

			} else {
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: false,
					distance: 20,
				});
			}
			})
			.fail( function(){
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: false,
					distance: 20,
				});
			});
		},

		_advancedShowHide: function() {
			$(this).parent().find('.advanced-option').toggle('fast');
		},

		/**
		 * Show the welcome page after the license has been saved.
		 *
		 * @since 1.7.4
		 * @access private
		 * @method _maybeShowWelcome
		 */
		_maybeShowWelcome: function()
		{
			var onLicense    = 'license' == window.location.hash.replace( '#', '' ),
				isUpdated    = $( '.wrap .updated' ).length,
				licenseError = $( '.fl-license-error' ).length;

			if ( onLicense && isUpdated && ! licenseError ) {
				window.location.hash = 'welcome';
			}
		},

		/**
		 * Initializes the nav for the builder's admin settings page.
		 *
		 * @since 1.0
		 * @access private
		 * @method _initNav
		 */
		_initNav: function()
		{
			var links  = $('.fl-settings-nav a'),
				hash   = window.location.hash,
				active = hash === '' ? [] : links.filter('[href~="'+ hash +'"]');

			$('a.fl-active').removeClass('fl-active');
			$('.fl-settings-form').hide();

			if(hash === '' || active.length === 0) {
				active = links.eq(0);
			}

			active.addClass('fl-active');
			$('#fl-'+ active.attr('href').split('#').pop() +'-form').fadeIn();
		},

		/**
		 * Fires when a nav item is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _navClicked
		 */
		_navClicked: function()
		{
			if($(this).attr('href').indexOf('#') > -1) {
				$('a.fl-active').removeClass('fl-active');
				$('.fl-settings-form').hide();
				$(this).addClass('fl-active');
				$('#fl-'+ $(this).attr('href').split('#').pop() +'-form').fadeIn();
			}
		},

		/**
		 * Initializes the checkboxes for overriding network settings.
		 *
		 * @since 1.0
		 * @access private
		 * @method _initNetworkOverrides
		 */
		_initNetworkOverrides: function()
		{
			$('.fl-override-ms-cb').each(FLBuilderAdminSettings._initNetworkOverride);
		},

		/**
		 * Initializes a checkbox for overriding network settings.
		 *
		 * @since 1.0
		 * @access private
		 * @method _initNetworkOverride
		 */
		_initNetworkOverride: function()
		{
			var cb      = $(this),
				content = cb.closest('.fl-settings-form').find('.fl-settings-form-content');

			if(this.checked) {
				content.show();
			}
			else {
				content.hide();
			}
		},

		/**
		 * Fired when a network override checkbox is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _overrideCheckboxClicked
		 */
		_overrideCheckboxClicked: function()
		{
			var cb      = $(this),
				content = cb.closest('.fl-settings-form').find('.fl-settings-form-content');

			if(this.checked) {
				content.show();
			}
			else {
				content.hide();
			}
		},

		/**
		 * Initializes custom multi-selects.
		 *
		 * @since 1.10
		 * @access private
		 * @method _initMultiSelects
		 */
		_initMultiSelects: function()
		{
			$( 'select[multiple]' ).multiselect( {
				selectAll: true,
				texts: {
					deselectAll     : FLBuilderAdminSettingsStrings.deselectAll,
					noneSelected    : FLBuilderAdminSettingsStrings.noneSelected,
					placeholder     : FLBuilderAdminSettingsStrings.select,
					selectAll       : FLBuilderAdminSettingsStrings.selectAll,
					selectedOptions : FLBuilderAdminSettingsStrings.selected
				}
			} );
		},

		/**
		 * Initializes user access select options.
		 *
		 * @since 1.10
		 * @access private
		 * @method _initUserAccessSelects
		 */
		_initUserAccessSelects: function()
		{
			var config  = FLBuilderAdminSettingsConfig,
				options = null,
				role    = null,
				select  = null,
				key     = null,
				hidden  = null;

			$( '.fl-user-access-select' ).each( function() {

				options = [];
				select  = $( this );
				key     = select.attr( 'name' ).replace( 'fl_user_access[', '' ).replace( '][]', '' );

				for( role in config.roles ) {
					options.push( {
						name    : config.roles[ role ],
						value   : role,
						checked : 'undefined' == typeof config.userAccess[ key ] ? false : config.userAccess[ key ][ role ]
					} );
				}

				select.multiselect( 'loadOptions', options );
			} );

			$( '.fl-user-access-select' ).on( 'change', FLBuilderAdminSettings._userAccessChanged );
		},

		/**
		 * Initializes the checkboxes for overriding user access
		 * network settings.
		 *
		 * @since 1.0
		 * @access private
		 * @method _initUserAccessNetworkOverrides
		 */
		_initUserAccessNetworkOverrides: function()
		{
			$('.fl-ua-override-ms-cb').each(FLBuilderAdminSettings._initUserAccessNetworkOverride);
		},

		/**
		 * Initializes a checkbox for overriding user access
		 * network settings.
		 *
		 * @since 1.0
		 * @access private
		 * @method _initUserAccessNetworkOverride
		 */
		_initUserAccessNetworkOverride: function()
		{
			var cb     = $(this),
				select = cb.closest('.fl-user-access-setting').find('.ms-options-wrap');

			if(this.checked) {
				select.show();
			}
			else {
				select.hide();
			}
		},

		/**
		 * Fired when a network override checkbox is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _overrideCheckboxClicked
		 */
		_overrideUserAccessCheckboxClicked: function()
		{
			var cb     = $(this),
				select = cb.closest('.fl-user-access-setting').find('.ms-options-wrap');

			if(this.checked) {
				select.show();
			}
			else {
				select.hide();
			}
		},

		/**
		 * Debounce timer for user access saves.
		 *
		 * @since 2.11
		 * @access private
		 * @property {Object} _userAccessTimers
		 */
		_userAccessTimers: {},

		/**
		 * Fires when a user access multi-select changes.
		 * Debounces and saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _userAccessChanged
		 */
		_userAccessChanged: function()
		{
			var select     = $( this ),
				capability = select.data( 'capability' ),
				roles      = select.val() || [];

			if ( ! capability ) {
				return;
			}

			// Debounce to batch rapid select-all clicks.
			if ( FLBuilderAdminSettings._userAccessTimers[ capability ] ) {
				clearTimeout( FLBuilderAdminSettings._userAccessTimers[ capability ] );
			}

			FLBuilderAdminSettings._userAccessTimers[ capability ] = setTimeout( function() {
				$.post( ajaxurl, {
					action:     'fl_user_access_save',
					capability: capability,
					roles:      roles,
					_wpnonce:   $( '#fl-user-access-nonce' ).val()
				}, function( response ) {
					if ( response.success ) {
						new Notify({
							status: 'success',
							title: 'Saved',
							autoclose: true,
							autotimeout: 1000,
							distance: 20,
						});
					} else {
						new Notify({
							status: 'error',
							title: 'Save Error',
							autoclose: true,
							autotimeout: 3000,
							distance: 20,
						});
					}
				});
			}, 300 );
		},

		/**
		 * Fires when the "all" checkbox in the list of enabled
		 * modules is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _moduleAllCheckboxClicked
		 */
		_moduleAllCheckboxClicked: function()
		{
			if($(this).is(':checked')) {
				$('.fl-module-cb:visible').prop('checked', true);
			} else {
				$('.fl-module-cb:visible').prop('checked', false);
			}
			FLBuilderAdminSettings._moduleToggleSave($(this), 'all', $(this).is(':checked'));
			FLBuilderAdminSettings._updateGroupToggles();
		},

		/**
		 * Fires when a checkbox in the list of enabled
		 * modules is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _moduleCheckboxClicked
		 */
		_moduleCheckboxClicked: function()
		{
			var allChecked = true;

			$('.fl-module-cb:visible').each(function() {

				if(!$(this).is(':checked')) {
					allChecked = false;
				}
			});

			if(allChecked) {
				$('.fl-module-all-cb:visible').prop('checked', true);
			}
			else {
				$('.fl-module-all-cb:visible').prop('checked', false);
			}

			FLBuilderAdminSettings._moduleToggleSave($(this), $(this).val(), $(this).is(':checked'));
			FLBuilderAdminSettings._updateGroupToggles();
		},

		/**
		 * Saves a module or block toggle via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _moduleToggleSave
		 */
		_moduleToggleSave: function(el, module, enabled, silent)
		{
			var isBlocks    = el.closest('#fl-blocks-form').length > 0;
			var isPostTypes = el.closest('#fl-post-types-form').length > 0;
			var action, nonce;

			if (isBlocks) {
				action = 'fl_block_toggle';
				nonce  = $('#fl-blocks-nonce').val();
			} else if (isPostTypes) {
				action = 'fl_post_type_toggle';
				nonce  = $('#fl-post-types-nonce').val();
			} else {
				action = 'fl_module_toggle';
				nonce  = $('#fl-modules-nonce').val();
			}

			return $.post(ajaxurl, {
				action:   action,
				module:   module,
				enabled:  enabled,
				_wpnonce: nonce
			}, function(response) {
				if (silent) return;
				if (response.success) {
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					var msg = response.data && response.data.message ? response.data.message : 'Save Error';
					new Notify({
						status: 'error',
						title: msg,
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			});
		},

		/**
		 * Toggles a collapsible group open/closed.
		 *
		 * @since 2.11
		 * @access private
		 * @method _toggleGroupClicked
		 */
		_toggleGroupClicked: function(e)
		{
			// Don't collapse when clicking the group toggle checkbox
			if ($(e.target).hasClass('fl-group-toggle-cb')) {
				return;
			}
			var $group = $(this).closest('.fl-toggle-group');
			$group.toggleClass('collapsed');
			var expanded = ! $group.hasClass('collapsed');
			$(this).attr('aria-expanded', expanded ? 'true' : 'false');
		},

		/**
		 * Handles keyboard navigation for toggle group headers.
		 *
		 * @since 2.11
		 * @access private
		 * @method _toggleGroupKeydown
		 */
		_toggleGroupKeydown: function(e)
		{
			// Enter or Space toggles the group
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				$(this).trigger('click');
			}
		},

		/**
		 * Toggles all modules/blocks within a group on or off.
		 *
		 * @since 2.11
		 * @access private
		 * @method _groupToggleClicked
		 */
		_groupToggleClicked: function(e)
		{
			e.stopPropagation();

			var checked = $(this).is(':checked');
			var $group  = $(this).closest('.fl-toggle-group');
			var $cbs    = $group.find('.fl-module-cb');
			var promises = [];

			$cbs.prop('checked', checked);

			// AJAX save each toggle silently
			$cbs.each(function() {
				promises.push(
					FLBuilderAdminSettings._moduleToggleSave($(this), $(this).val(), checked, true)
				);
			});

			// Show a single notification when all saves complete
			$.when.apply($, promises).then(function() {
				new Notify({
					status: 'success',
					title: 'Saved',
					autoclose: true,
					autotimeout: 1000,
					distance: 20,
				});
			}).fail(function() {
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});

			// Update "All" master toggle
			var allChecked = true;
			$('.fl-module-cb:visible').each(function() {
				if (!$(this).is(':checked')) {
					allChecked = false;
				}
			});
			$('.fl-module-all-cb:visible').prop('checked', allChecked);

			// Update other group toggles if "All" state changed
			FLBuilderAdminSettings._updateGroupToggles();
		},

		/**
		 * Updates all group toggle checkboxes to reflect
		 * the checked state of their child toggles.
		 *
		 * @since 2.11
		 * @access private
		 * @method _updateGroupToggles
		 */
		_updateGroupToggles: function()
		{
			$('.fl-toggle-group').each(function() {
				var $group = $(this);
				var $cbs   = $group.find('.fl-module-cb');
				var $groupCb = $group.find('.fl-group-toggle-cb');

				if ($cbs.length === 0 || $groupCb.length === 0) return;

				var allChecked = true;
				$cbs.each(function() {
					if (!$(this).is(':checked')) {
						allChecked = false;
					}
				});
				$groupCb.prop('checked', allChecked);
			});
		},

		/**
		 * Filters modules by usage when a filter button is clicked.
		 *
		 * @since 2.11
		 * @access private
		 * @method _moduleFilterClicked
		 */
		_moduleFilterClicked: function()
		{
			var filter = $(this).data('filter');

			// Update active button state
			$('.fl-module-filter').removeClass('active');
			$(this).addClass('active');

			// Hide "Not used" badges when filtering to Not Used (redundant)
			if ( 'not-used' === filter ) {
				$('#fl-modules-form .fl-module-usage').hide();
			} else {
				$('#fl-modules-form .fl-module-usage').show();
			}

			// Switch to single-column list when usage badges are visible
			if ( 'not-used' === filter ) {
				$('#fl-modules-form .fl-toggle-group').removeClass('fl-filter-list');
			} else {
				$('#fl-modules-form .fl-toggle-group').addClass('fl-filter-list');
			}

			// Filter module rows within toggle groups
			$('#fl-modules-form .fl-toggle-group').each(function() {
				var $group   = $(this);
				var $modules = $group.find('.fl-toggle-group-content > p[data-module-usage]');
				var visible  = 0;

				if ( 'all' === filter ) {
					$modules.removeClass('fl-filter-hidden');
					visible = $modules.length;
				} else {
					$modules.each(function() {
						if ( $(this).data('module-usage') === filter ) {
							$(this).removeClass('fl-filter-hidden');
							visible++;
						} else {
							$(this).addClass('fl-filter-hidden');
						}
					});
				}

				// Hide entire group if no visible modules
				if ( visible === 0 && $modules.length > 0 ) {
					$group.addClass('fl-filter-hidden');
				} else {
					$group.removeClass('fl-filter-hidden');
				}

				// Update the count badge
				var $count = $group.find('.fl-toggle-group-count');
				if ( $count.length ) {
					$count.text( visible );
				}
			});
		},

		/**
		 * Applies single-column layout on load when usage tracking is enabled.
		 *
		 * @since 2.11
		 * @access private
		 * @method _initModuleUsageLayout
		 */
		_initModuleUsageLayout: function()
		{
			if ( $('.fl-module-filter-bar').length ) {
				$('#fl-modules-form .fl-toggle-group').addClass('fl-filter-list');
			}
		},

		/**
		 * Filters modules by name as the user types in the search input.
		 *
		 * @since 2.11
		 * @access private
		 * @method _moduleSearchInput
		 */
		_moduleSearchInput: function()
		{
			var query = $(this).val().toLowerCase().trim();

			$('#fl-modules-form .fl-toggle-group').each(function() {
				var $group   = $(this);
				var $modules = $group.find('.fl-toggle-group-content > p');
				var visible  = 0;

				$modules.each(function() {
					var name = $(this).find('label').text().toLowerCase();
					if ( ! query || name.indexOf( query ) > -1 ) {
						$(this).removeClass('fl-search-hidden');
						visible++;
					} else {
						$(this).addClass('fl-search-hidden');
					}
				});

				// Hide entire group if no matches
				if ( visible === 0 && $modules.length > 0 ) {
					$group.addClass('fl-search-hidden');
				} else {
					$group.removeClass('fl-search-hidden');
					// Auto-expand group when searching
					if ( query ) {
						$group.find('.fl-toggle-group-content').show();
						$group.find('.fl-toggle-group-header .dashicons')
							.removeClass('dashicons-arrow-down-alt2')
							.addClass('dashicons-arrow-up-alt2');
					}
				}

				// Update the count badge
				var $count = $group.find('.fl-toggle-group-count');
				if ( $count.length ) {
					$count.text( visible );
				}
			});

			// Also filter the deprecated section
			$('#fl-modules-form .fl-modules-deprecated').each(function() {
				var $group   = $(this);
				var $modules = $group.find('.fl-toggle-group-content > p');
				var visible  = 0;

				$modules.each(function() {
					var name = $(this).find('label').text().toLowerCase();
					if ( ! query || name.indexOf( query ) > -1 ) {
						$(this).removeClass('fl-search-hidden');
						visible++;
					} else {
						$(this).addClass('fl-search-hidden');
					}
				});

				if ( visible === 0 && $modules.length > 0 ) {
					$group.addClass('fl-search-hidden');
				} else {
					$group.removeClass('fl-search-hidden');
				}
			});
		},

		/**
		 * @since 1.7.4
		 * @access private
		 * @method _initLicenseSettings
		 */
		_initLicenseSettings: function()
		{
			$( '.fl-new-license-form .button' ).on( 'click', FLBuilderAdminSettings._newLicenseButtonClick );
		},

		/**
		 * @since 1.7.4
		 * @access private
		 * @method _newLicenseButtonClick
		 */
		_newLicenseButtonClick: function()
		{
			$( '.fl-new-license-form' ).hide();
			$( '.fl-license-form' ).show();
		},

		/**
		 * Fires when the template visibility select changes. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _templateVisibilityChanged
		 */
		_templateVisibilityChanged: function()
		{
			var value = $( this ).val();

			$.post( ajaxurl, {
				action:   'fl_template_visibility_save',
				value:    value,
				_wpnonce: $( '#fl-templates-nonce' ).val()
			}, function( response ) {
				if ( response.success ) {
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					new Notify({
						status: 'error',
						title: 'Save Error',
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			});
		},

		/**
		 * Fires when a templates override toggle changes. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _templatesOverrideToggleChanged
		 */
		_templatesOverrideToggleChanged: function()
		{
			var $cb     = $( this );
			var setting = $cb.data( 'setting' ) || 'fl-templates-override';
			var value   = $cb.is( ':checked' );

			$.post( ajaxurl, {
				action:   'fl_templates_override_save',
				setting:  setting,
				value:    value,
				_wpnonce: $( '#fl-templates-nonce' ).val()
			}, function( response ) {
				if ( response.success ) {
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					var msg = response.data && response.data.message ? response.data.message : 'Save Error';
					new Notify({
						status: 'error',
						title: msg,
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			});
		},

		/**
		 * Fires when the templates override site ID input changes. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _templatesOverrideInputChanged
		 */
		_templatesOverrideInputChanged: function()
		{
			var value = $( this ).val();

			$.post( ajaxurl, {
				action:   'fl_templates_override_save',
				setting:  'fl-templates-override',
				value:    value,
				_wpnonce: $( '#fl-templates-nonce' ).val()
			}, function( response ) {
				if ( response.success ) {
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					var msg = response.data && response.data.message ? response.data.message : 'Save Error';
					new Notify({
						status: 'error',
						title: msg,
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			});
		},

		/**
		 * Fires when the templates override setting is changed.
		 *
		 * @since 1.6.3
		 * @access private
		 * @method _templatesOverrideChange
		 */
		_templatesOverrideChange: function()
		{
			var input 			= $('input[name=fl-templates-override]'),
				val 			= input.val(),
				overrideNodes 	= $( '.fl-templates-override-nodes' ),
				toggle 			= false;

			if ( 'checkbox' == input.attr( 'type' ) ) {
				toggle = input.is( ':checked' );
			}
			else {
				toggle = '' !== val;
			}

			overrideNodes.toggle( toggle );
		},

		/**
		 * Shows the media library lightbox for uploading icons.
		 *
		 * @since 1.4.6
		 * @access private
		 * @method _showIconUploader
		 */
		_showIconUploader: function()
		{
			if(FLBuilderAdminSettings._iconUploader === null) {
				FLBuilderAdminSettings._iconUploader = wp.media({
					title: FLBuilderAdminSettingsStrings.selectFile,
					button: { text: FLBuilderAdminSettingsStrings.selectFile },
					library : { type : 'application/zip' },
					multiple: false
				});
			}

			FLBuilderAdminSettings._iconUploader.once('select', $.proxy(FLBuilderAdminSettings._iconFileSelected, this));
			FLBuilderAdminSettings._iconUploader.open();
		},

		/**
		 * Callback for when an icon set file is selected.
		 *
		 * @since 1.4.6
		 * @access private
		 * @method _iconFileSelected
		 */
		_iconFileSelected: function()
		{
			var file = FLBuilderAdminSettings._iconUploader.state().get('selection').first().toJSON();

			$( 'input[name=fl-new-icon-set]' ).val( file.id );
			$( '#icons-form' ).submit();
		},

		/**
		 * Fires when the delete link for an icon set is clicked.
		 *
		 * @since 1.4.6
		 * @access private
		 * @method _deleteCustomIconSet
		 */
		_deleteCustomIconSet: function()
		{
			var set = $( this ).data( 'set' );

			$( 'input[name=fl-delete-icon-set]' ).val( set );
			$( '#icons-form' ).submit();
		},

		/**
		 * Clears the builder cache via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _clearCacheSubmit
		 * @return {Boolean}
		 */
		_clearCacheSubmit: function( e )
		{
			e.preventDefault();

			var $btn   = $( '#cache-form input[type="submit"]' );
			var nonce  = $( '#fl-cache-nonce' ).val();
			var originalVal = $btn.val();

			$btn.val( FLBuilderAdminSettingsStrings.clearing || 'Clearing...' ).prop( 'disabled', true );

			$.post( ajaxurl, {
				action:   'fl_clear_cache',
				_wpnonce: nonce
			}, function( response ) {
				$btn.prop( 'disabled', false ).val( originalVal );
				if ( response.success ) {
					new Notify({
						status: 'success',
						title: FLBuilderAdminSettingsStrings.cacheCleared || 'Cache Cleared',
						autoclose: true,
						autotimeout: 2000,
						distance: 20,
					});
				} else {
					new Notify({
						status: 'error',
						title: 'Error',
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			}).fail( function() {
				$btn.prop( 'disabled', false ).val( originalVal );
				new Notify({
					status: 'error',
					title: 'Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});

			return false;
		},

		/**
		 * Fires when the uninstall button is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _uninstallFormSubmit
		 * @return {Boolean}
		 */
		_uninstallFormSubmit: function()
		{
			var result = prompt(FLBuilderAdminSettingsStrings.uninstall.replace(/&quot;/g, '"'), '');

			if(result == 'uninstall') {
				return true;
			}

			return false;
		},
		/**
		 * Fires when an icon set toggle is changed. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _iconSetToggleChanged
		 */
		_iconSetToggleChanged: function()
		{
			var $cb      = $( this );
			var iconSet  = $cb.val();
			var enabled  = $cb.is( ':checked' );
			var nonce    = $( '#fl-icons-nonce' ).val();
			var $label   = $cb.closest( '.fl-toggle-switch' );

			$label.addClass( 'fl-toggle-switch-saving' );

			$.post( ajaxurl, {
				action:   'fl_icon_set_toggle',
				icon_set: iconSet,
				enabled:  enabled,
				_wpnonce: nonce
			}, function( response ) {
				$label.removeClass( 'fl-toggle-switch-saving' );
				if ( response.success ) {
					$label.addClass( 'fl-toggle-switch-saved' );
					setTimeout( function() {
						$label.removeClass( 'fl-toggle-switch-saved' );
					}, 1500 );
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					// Revert the toggle on error.
					$cb.prop( 'checked', ! enabled );
					var msg = response.data && response.data.message ? response.data.message : 'Save Error';
					new Notify({
						status: 'error',
						title: msg,
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			}).fail( function() {
				$label.removeClass( 'fl-toggle-switch-saving' );
				$cb.prop( 'checked', ! enabled );
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});
		},

		/**
		 * Fires when the Font Awesome Pro toggle is changed. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _faProToggleChanged
		 */
		_faProToggleChanged: function()
		{
			var $cb     = $( this );
			var enabled = $cb.is( ':checked' );
			var nonce   = $( '#fl-icons-nonce' ).val();
			var $label  = $cb.closest( '.fl-toggle-switch' );

			$label.addClass( 'fl-toggle-switch-saving' );

			$.post( ajaxurl, {
				action:   'fl_fa_pro_toggle',
				enabled:  enabled,
				_wpnonce: nonce
			}, function( response ) {
				$label.removeClass( 'fl-toggle-switch-saving' );
				if ( response.success ) {
					$label.addClass( 'fl-toggle-switch-saved' );
					setTimeout( function() {
						$label.removeClass( 'fl-toggle-switch-saved' );
					}, 1500 );
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					$cb.prop( 'checked', ! enabled );
					new Notify({
						status: 'error',
						title: 'Save Error',
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			}).fail( function() {
				$label.removeClass( 'fl-toggle-switch-saving' );
				$cb.prop( 'checked', ! enabled );
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});
		},

		/**
		 * Fires when the debug mode toggle is changed. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _debugToggleChanged
		 */
		_debugToggleChanged: function()
		{
			var $cb     = $( this );
			var enabled = $cb.is( ':checked' );
			var nonce   = $( '#fl-debug-nonce' ).val();
			var $label  = $cb.closest( '.fl-toggle-switch' );

			$label.addClass( 'fl-toggle-switch-saving' );

			$.post( ajaxurl, {
				action:   'fl_debug_toggle',
				enabled:  enabled,
				_wpnonce: nonce
			}, function( response ) {
				if ( response.success ) {
					// Reload so the server re-renders the Global CSS/JS and Theme
					// Code cards, which are gated by the same transient.
					location.reload();
				} else {
					$label.removeClass( 'fl-toggle-switch-saving' );
					$cb.prop( 'checked', ! enabled );
					new Notify({
						status: 'error',
						title: 'Save Error',
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			}).fail( function() {
				$label.removeClass( 'fl-toggle-switch-saving' );
				$cb.prop( 'checked', ! enabled );
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});
		},

		/**
		 * Fires when the usage tracking toggle is changed. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _usageToggleChanged
		 */
		_usageToggleChanged: function()
		{
			var $cb     = $( this );
			var enabled = $cb.is( ':checked' );
			var nonce   = $( '#fl-usage-nonce' ).val();
			var $label  = $cb.closest( '.fl-toggle-switch' );

			$label.addClass( 'fl-toggle-switch-saving' );

			$.post( ajaxurl, {
				action:   'fl_usage_toggle',
				enable:   enabled ? 1 : 0,
				_wpnonce: nonce
			}, function() {
				$label.removeClass( 'fl-toggle-switch-saving' );
				$label.addClass( 'fl-toggle-switch-saved' );
				setTimeout( function() {
					$label.removeClass( 'fl-toggle-switch-saved' );
				}, 1500 );
				new Notify({
					status: 'success',
					title: 'Saved',
					autoclose: true,
					autotimeout: 1000,
					distance: 20,
				});
			}).fail( function() {
				$label.removeClass( 'fl-toggle-switch-saving' );
				$cb.prop( 'checked', ! enabled );
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});
		},

		/**
		 * Fires when the Cache Clearing Tool main toggle changes. Saves via
		 * AJAX and slides the dependent sub-option in/out.
		 *
		 * @since 2.11
		 * @access private
		 * @method _cachePluginsToggleChanged
		 */
		_cachePluginsToggleChanged: function()
		{
			var $cb     = $( this );
			var enabled = $cb.is( ':checked' );
			FLBuilderAdminSettings._cachePluginsSave( 'enabled', $cb, function() {
				$( '.fl-tools-sub-option' ).slideToggle( 200, function() {
					$( this ).css( 'display', enabled ? '' : 'none' );
				} );
			} );
		},

		/**
		 * Fires when the Cache Clearing Tool varnish sub-toggle changes.
		 *
		 * @since 2.11
		 * @access private
		 * @method _cacheVarnishToggleChanged
		 */
		_cacheVarnishToggleChanged: function()
		{
			FLBuilderAdminSettings._cachePluginsSave( 'varnish', $( this ) );
		},

		/**
		 * Shared AJAX save for the Cache Clearing Tool toggles.
		 *
		 * @since 2.11
		 * @access private
		 * @method _cachePluginsSave
		 */
		_cachePluginsSave: function( setting, $cb, onSuccess )
		{
			var enabled = $cb.is( ':checked' );
			var nonce   = $( '#fl-cache-plugins-nonce' ).val();
			var $label  = $cb.closest( '.fl-toggle-switch' );

			$label.addClass( 'fl-toggle-switch-saving' );

			$.post( ajaxurl, {
				action:   'fl_cache_plugins_toggle',
				setting:  setting,
				enabled:  enabled,
				_wpnonce: nonce
			}, function( response ) {
				$label.removeClass( 'fl-toggle-switch-saving' );
				if ( response.success ) {
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
					if ( onSuccess ) {
						onSuccess();
					}
				} else {
					$cb.prop( 'checked', ! enabled );
					new Notify({
						status: 'error',
						title: 'Save Error',
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			}).fail( function() {
				$label.removeClass( 'fl-toggle-switch-saving' );
				$cb.prop( 'checked', ! enabled );
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});
		},

		/**
		 * Fires when a release channel radio is changed. Saves via AJAX.
		 *
		 * @since 2.11
		 * @access private
		 * @method _releaseChannelChanged
		 */
		_releaseChannelChanged: function()
		{
			var $radio   = $( this );
			var channel  = $radio.val();
			var $cards   = $( '.fl-release-channel-card' );
			var $card    = $radio.closest( '.fl-release-channel-card' );
			var $prev    = $( '.fl-release-channel-active' );
			var nonce    = $( '#fl-beta-nonce' ).val();

			var confirmMessages = {
				beta:  FLBuilderAdminSettingsStrings.confirmBeta || 'Are you sure you want to enable Beta releases?',
				alpha: FLBuilderAdminSettingsStrings.confirmAlpha || 'Are you sure you want to enable Alpha releases?'
			};

			var message = confirmMessages[ channel ];

			if ( message && ! confirm( message ) ) {
				// Revert to previous selection
				$prev.find( 'input[type="radio"]' ).prop( 'checked', true );
				return;
			}

			// Update active card styling
			$cards.removeClass( 'fl-release-channel-active' );
			$card.addClass( 'fl-release-channel-active' );

			$.post( ajaxurl, {
				action:   'fl_release_channel',
				channel:  channel,
				_wpnonce: nonce
			}, function( response ) {
				if ( response.success ) {
					new Notify({
						status: 'success',
						title: 'Saved',
						autoclose: true,
						autotimeout: 1000,
						distance: 20,
					});
				} else {
					// Revert on error
					$cards.removeClass( 'fl-release-channel-active' );
					$prev.addClass( 'fl-release-channel-active' );
					$prev.find( 'input[type="radio"]' ).prop( 'checked', true );
					new Notify({
						status: 'error',
						title: 'Save Error',
						autoclose: true,
						autotimeout: 3000,
						distance: 20,
					});
				}
			}).fail( function() {
				$cards.removeClass( 'fl-release-channel-active' );
				$prev.addClass( 'fl-release-channel-active' );
				$prev.find( 'input[type="radio"]' ).prop( 'checked', true );
				new Notify({
					status: 'error',
					title: 'Save Error',
					autoclose: true,
					autotimeout: 3000,
					distance: 20,
				});
			});
		},

		_iconPro: function() {
			form = $('#icons-form')
			checkbox = form.find('input[name=fl-enable-fa-pro]').prop('checked')
			light = form.find('input[value=font-awesome-5-light]').parent()
			duo   = form.find('input[value=font-awesome-5-duotone]').parent()

			if ( true === checkbox ) {
				light.css('font-weight', '800')
			//	light.css('color', '#0E5A71')
				duo.css('font-weight', '800')
			//	duo.css('color', '#0E5A71')
			}

		},
		_alphaSettings: function() {
			// Confirmation is now handled inline in admin-settings-license.php
		},
	};

	/* Initializes the builder's admin settings. */
	$(function(){
		FLBuilderAdminSettings.init();
	});

})(jQuery);
