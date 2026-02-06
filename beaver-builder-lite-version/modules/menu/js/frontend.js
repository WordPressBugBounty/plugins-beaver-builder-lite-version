/* eslint-disable no-undef */
(function($) {

	/**
	 * Class for Menu Module
	 *
	 * @since 1.6.1
	 */
	FLBuilderMenu = function( settings ){

		// set params
		this.nodeId              = settings.id;
		this.nodeClass           = '.fl-node-' + settings.id;
		this.wrapperClass        = this.nodeClass + ' .fl-menu';
		this.type				 				 = settings.type;
		this.mobileToggle		     = settings.mobile;
		this.mobileBelowRow		   = settings.mobileBelowRow;
		this.mobileFlyout		     = settings.mobileFlyout;
		this.breakPoints         = settings.breakPoints;
		this.mobileBreakpoint	 	 = settings.mobileBreakpoint;
		this.currentBrowserWidth = $( window ).width();
		this.postId              = settings.postId;
		this.mobileStacked       = settings.mobileStacked;
		this.submenuIcon         = settings.submenuIcon;
		this.flyoutWidth         = settings.flyoutWidth;

		// initialize the menu
		this._initMenu();

		// check if viewport is resizing
		$( window ).on( 'resize', $.proxy( function() {

			var width = $( window ).width();

			// if screen width is resized, reload the menu
		    if( width != this.currentBrowserWidth ){

				this.currentBrowserWidth = width;
				this._initMenu( true );
 				this._clickOrHover( true );
			}

			this._resizeFlyoutMenuPanel();
		}, this ) );

		$( window ).on( 'scroll', $.proxy( function() {
			this._resizeFlyoutMenuPanel();
		}, this ) );

		// Move focus to the first menu item if the layout is set to BelowRow
		$( this.wrapperClass ).on( 'keydown', '.fl-menu-mobile-toggle, .menu-item:first a:first', $.proxy( function ( event ) {
			// Make sure it is a tab key with both the menu toggle enabled & below row layout set
			if ( event.key !== 'Tab' || ! this._isMenuToggle() || ! this._isMobileBelowRowEnabled() ) {
				return;
			}
			// Trigger focus on the mobile toggle button if the user is shift tabbing from the first menu item
			if ( event.shiftKey && $( event.target ).is( 'a' ) ) {
				event.preventDefault();
				$( this.wrapperClass ).find( '.fl-menu-mobile-toggle' ).trigger( 'focus' );
			}
			// Trigger focus on the first menu item if the user is tabbing from the mobile toggle button
			else if ( ! event.shiftKey && $( event.target ).hasClass( 'fl-menu-mobile-toggle' ) && $( event.target ).hasClass( 'fl-active' )  ) {
				event.preventDefault();
				$( this.wrapperClass ).find( '.menu-item:first a:first' ).trigger( 'focus' );
			}
		}, this ) );

		// Close mobile menu or accordion when tabbing out from the first or last menu item
		$( this.wrapperClass ).on( 'focusout', $.proxy( function ( event ) {
			if ( $( this.wrapperClass + ' nav' ).has( $( event.relatedTarget ) ).length === 0 ) {
				if ( this.type === 'accordion' ) {
					this._toggleSubmenu( $( event.target ).parents( '.fl-has-submenu:last' ), false );
				}
				else {
					this._clickOrHover( true );
				}
				const mobileToggle = $( this.wrapperClass ).find( '.fl-menu-mobile-toggle' );
				if ( this._isMenuToggle() && mobileToggle.hasClass( 'fl-active' ) && ! $( event.relatedTarget ).is( mobileToggle ) && 'expanded' !== this.mobileToggle ) {
					mobileToggle.trigger( 'click' );
				}
			}
		}, this ) );

	};

	FLBuilderMenu.prototype = {
		nodeClass		: '',
		wrapperClass: '',
		type				: '',
		breakPoints	: {},
		$submenus		: null,

		/**
		 * Check if the screen size fits a mobile viewport.
		 *
		 * @since  1.6.1
		 * @return bool
		 */
		_isMobile: function(){
			return this.currentBrowserWidth <= this.breakPoints.small ? true : false;
		},

		/**
		 * Check if the device is connected to a mouse.
		 * 
		 * @since  2.9.0
		 * @return bool
		 */
		_isMouseAvailable: function(){
			return window.matchMedia("(pointer: fine) and (hover: hover)").matches;
		},

		/**
		 * Check if the screen size fits a medium viewport.
		 *
		 * @since  1.10.5
		 * @return bool
		 */
		_isMedium: function(){
			return this.currentBrowserWidth <= this.breakPoints.medium ? true : false;
		},

		/**
		 * Check if the screen size fits a large viewport.
		 *
		 * @since  1.10.5
		 * @return bool
		 */
		_isLarge: function(){
			return this.currentBrowserWidth <= this.breakPoints.large ? true : false;
		},

		/**
		 * Check if the menu should toggle for the current viewport base on the selected breakpoint.
		 *
		 * @see 	 this._isMobile()
		 * @see 	 this._isMedium()
		 * @since  1.10.5
		 * @return bool
		 */
		_isMenuToggle: function(){
			if ( ( 'always' == this.mobileBreakpoint
				|| ( this._isMobile() && 'mobile' == this.mobileBreakpoint )
				|| ( this._isMedium() && 'medium-mobile' == this.mobileBreakpoint )
				|| ( this._isLarge() && 'large-mobile' == this.mobileBreakpoint )
			) && ( $( this.wrapperClass ).find( '.fl-menu-mobile-toggle' ).is(':visible') || 'expanded' == this.mobileToggle ) ) {
				return true;
			}

			return false;
		},

		/**
		 * Initialize the toggle logic for the menu.
		 *
		 * @see    this._isMenuToggle()
		 * @see    this._menuOnCLick()
		 * @see    this._clickOrHover()
		 * @see    this._submenuOnRight()
		 * @see    this._submenuRowZindexFix()
		 * @see    this._toggleForMobile()
		 * @since  1.6.1
		 * @return void
		 */
		_initMenu: function( resized = false ){
			this._setupSubmenu();
			if ( ! resized ) {
				this._menuOnFocus();
				this._menuOnClick();
				this._menuOnEscape();
			}
			if ( $( this.nodeClass ).length && this.type == 'horizontal' ) {
				this._initMegaMenus();
			}

			if( this._isMenuToggle() || this.type == 'accordion' ){

				$( this.wrapperClass ).off( 'mouseenter mouseleave' );
				this._clickOrHover();

			} else {
				this._submenuOnRight();
				this._submenuRowZindexFix();
			}

			if( this.mobileToggle != 'expanded' ){
				this._toggleForMobile();
			}

			if( $( this.wrapperClass ).find( '.fl-menu-search-item' ).length ){
				this._toggleMenuSearch();
			}

			if( $( this.wrapperClass ).find( '.fl-menu-cart-item').length ){
				this._wooUpdateParams();
			}
		},

		/**
		 * Whether the toggle behavior for submenus is through hovering or not.
		 *
		 * @since  2.5
		 * @return void
		 */
		_setupSubmenu: function() {
			if ( ! this._isMouseAvailable() || this._isMenuToggle() || this.type === 'accordion' ) {
				$( this.wrapperClass ).addClass( 'no-hover' );
			}
			else {
				$( this.wrapperClass ).removeClass( 'no-hover' );
			}
		},

		/**
		 * Logic for menu items when focused.
		 *
		 * @since  1.9.0
		 * @return void
		 */
		_menuOnFocus: function(){
			$( this.wrapperClass ).on( 'focus', 'a, .fl-menu-toggle', $.proxy( function( event ) {
				const focusedMenuItem = $( event.target ).closest( '.menu-item' );
				const blurredMenuItem = $( event.relatedTarget ).closest( '.menu-item' );
				// In case the blurred & focused items are siblings
				if ( focusedMenuItem.closest( 'ul' ).is( blurredMenuItem.closest( 'ul' ) ) )	{
					// Check if the focus event is not between the link & submenu icon of the same menu item
					if ( ! focusedMenuItem.is( blurredMenuItem ) ) {
						this._toggleSubmenu( blurredMenuItem, false );
					}
				}
				// In case neither items are child & parent nor vice versa
				else if ( focusedMenuItem.has( blurredMenuItem ).length === blurredMenuItem.has( focusedMenuItem ).length ) {
					// Get the blurred item parent & is the sibling of the focused item
					blurredMenuItem.parents( '.fl-has-submenu' ).each( ( _, parent ) => {
						if ( focusedMenuItem.closest( 'ul' ).is( $( parent ).closest( 'ul' ) ) ) {
							this._toggleSubmenu( $( parent ), false );
							return false;
						}
					} );
				}
				// In case the blurred item is a child of the focused item
				else if ( focusedMenuItem.has( blurredMenuItem ).length ) {
					if ( this.type !== 'accordion' && this.submenuIcon === 'none' ) {
						this._toggleSubmenu( blurredMenuItem, false );
					}
				}
			}, this ) );
		},

		/**
		 * Logic for menu items when Escape key is pressed.
		 *
		 * @since  2.7.1
		 * @return void
		 */
		_menuOnEscape: function(){
			$( this.wrapperClass ).on( 'keydown', $.proxy( function( event ){
				if ( event.key !== 'Escape' ) return;
				const menuItem = $( event.target ).closest( '.menu-item' );
				const mobileToggle = $( this.wrapperClass ).find( '.fl-menu-mobile-toggle' );
				// In case there is a focused menu item
				if ( menuItem.length || $( event.target ).hasClass( 'fl-menu-mobile-close' ) ) {
					// Close all the submenus of the focused menu item
					if ( menuItem.hasClass( 'fl-has-submenu' ) && menuItem.find( '.sub-menu:first' ).is( ':visible' ) ) {
						this._toggleSubmenu( menuItem, false );
					}
					else {
						// Shift focus to the parent while closing all its submenus
						const parentMenuItem = menuItem.parents( '.fl-has-submenu' ).not( '.mega-menu.hide-heading, .mega-menu-disabled.hide-heading' ).first();
						// Close the mobile menu if there is no parent menu item and the mobile toggle is active
						if ( parentMenuItem.length === 0 && this._isMenuToggle() && mobileToggle.hasClass( 'fl-active' ) ) {
							// Blur to trigger the focus-out event for closing the menu
							mobileToggle.trigger( 'blur' ).trigger( 'focus' );
						}
						// Close the parent submenu and shift focus to its link
						else if ( parentMenuItem.length !== 0 ) {
							this._toggleSubmenu( parentMenuItem, false );
							parentMenuItem.find( 'a:first' ).trigger( 'focus' );
						}
					}
				}
				// Close the mobile menu if the mobile toggle is active
				else if ( $( event.target ).hasClass( 'fl-menu-mobile-toggle' ) && mobileToggle.hasClass( 'fl-active' ) ) {
					// Blur to trigger the focus-out event for closing the menu
					mobileToggle.trigger( 'blur' ).trigger( 'focus' );
				}
			}, this ) );
		},

		/**
		 * Logic for menu items when clicked.
		 *
		 * @since  1.6.1
		 * @return void
		 */
		_menuOnClick: function(){
			// Fallback for span elements with role="button" to be clickable
			$( this.wrapperClass ).on( 'keydown', 'span.fl-menu-toggle', $.proxy( function( event ) {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					$( event.target ).trigger( 'click' );
				}
			}, this ) );
			$( this.wrapperClass ).on( 'click', 'a, .fl-menu-toggle', $.proxy( function( event ) {
				event.stopPropagation();
				// Only allow mouse clicks with accordion & mobile menus
				if ( this._isMouseAvailable() && ! this._isMenuToggle() && event.detail && this.type !== 'accordion' ) return;
				// Links only open & do not toggle submenus if there is either a submenu icon or an accordion layout
				if ( $( event.target ).is( 'a' ) && ( this.submenuIcon !== 'none' || this.type === 'accordion' ) ) return;
				const menuItem = $( event.target ).closest( '.menu-item' );
				const menuLink = menuItem.find( 'a:first' ).attr( 'href' );
				const submenuHidden = menuItem.find( '.sub-menu:first' ).is( ':hidden' );
				if ( typeof menuLink === 'undefined' || menuLink === '#' || submenuHidden ) {
					event.preventDefault();
				}
				if ( $( event.target ).hasClass( 'fl-menu-toggle' ) || submenuHidden ) {
					this._toggleSubmenu( menuItem, submenuHidden );
				}
			}, this ) );
		},

		/**
		 * Controls toggling the visibility of all submenu items for desktop & mobile.
		 *
		 * @since  2.10
		 * @return void
		 */
		_toggleSubmenu: function ( menuItem, opened ) {
			const togglingClass = this._isMenuToggle() || this.type === 'accordion' ? 'fl-active' : 'focus';
			const toggleElement = this.submenuIcon === 'none' ? 'a' : '.fl-menu-toggle';
			const hiddenMenu = '.mega-menu.hide-heading, .mega-menu-disabled.hide-heading';
			if ( opened && menuItem.hasClass( 'fl-has-submenu' ) && ! menuItem.is( hiddenMenu ) ) {
				menuItem.addClass( togglingClass );
				menuItem.find( toggleElement ).first().attr( 'aria-expanded', true );
				if ( this._isMenuToggle() || this.type === 'accordion' ) {
					menuItem.find( '.sub-menu:first:hidden' ).slideDown();
				}
			}
			else {
				menuItem.parent().find( '.menu-item' ).removeClass( togglingClass );
				menuItem.parent().find( '.fl-has-submenu' ).not( hiddenMenu ).find( toggleElement ).attr( 'aria-expanded', false );
				if ( this._isMenuToggle() || this.type === 'accordion' ) {
					menuItem.find( '.sub-menu:visible' ).slideUp();
				}
			}
		},

		/**
		 * Changes general styling and behavior of menus based on mobile / desktop viewport.
		 *
		 * @see    this._isMenuToggle()
		 * @since  1.6.1
		 * @return void
		 */
		_clickOrHover: function( clear = false ){
			const selector = this._isMobileBelowRowEnabled() ? this.nodeClass + '-clone' : this.nodeClass;
			this.$submenus = this.$submenus || $( selector ).find( '.sub-menu' );

			const className = this._isMenuToggle() || this.type === 'accordion' ? 'fl-active' : 'focus';
			const toggleElement = this.submenuIcon === 'none' ? 'a' : '.fl-menu-toggle';
			const hiddenMenu = '.mega-menu.hide-heading, .mega-menu-disabled.hide-heading';
			$( selector ).find( '.fl-has-submenu' ).not( hiddenMenu ).each( function() {
				if( clear || ! $ ( this ).hasClass( className ) ){
					if ( clear ) $( this ).removeClass( className );
					$( this ).find( toggleElement + ':first' ).attr( 'aria-expanded', false );
					if ( className === 'fl-active' ) {
						$( this ).find( '.sub-menu' ).fadeOut();
					} else if ( className === 'focus' ) {
						$( this ).find( '.sub-menu' ).css( {
							'display' : '',
							'opacity' : ''
						} );
					}
				}
			} );
		},

		/**
		 * Logic to prevent submenus to go outside viewport boundaries.
		 *
		 * @since  1.6.1
		 * @return void
		 */
		_submenuOnRight: function(){

			$( this.wrapperClass )
				.on( 'mouseenter focus', '.fl-has-submenu', $.proxy( function( e ){

					if( $ ( e.currentTarget ).find('.sub-menu').length === 0 ) {
						return;
					}

					var $link           = $( e.currentTarget ),
						$parent         = $link.parent(),
						$subMenu        = $link.find( '.sub-menu' ),
						subMenuWidth    = $subMenu.width(),
						subMenuPos      = 0,
						bodyWidth       = $( 'body' ).width();

					if( $link.closest( '.fl-menu-submenu-right' ).length !== 0) {

						$link.addClass( 'fl-menu-submenu-right' );

					} else if( $( 'body' ).hasClass( 'rtl' ) ) {

						subMenuPos = $parent.is( '.sub-menu' ) ?
									 $parent.offset().left - subMenuWidth:
									 $link.offset().left - $link.width() - subMenuWidth;

						if( subMenuPos <= 0 ) {
							$link.addClass( 'fl-menu-submenu-right' );
						}

					} else {

						subMenuPos = $parent.is( '.sub-menu' ) ?
									 $parent.offset().left + $parent.width() + subMenuWidth :
									 $link.offset().left + $link.width() + subMenuWidth;

						if( subMenuPos > bodyWidth ) {
							$link.addClass('fl-menu-submenu-right');
						}
					}
				}, this ) )
				.on( 'mouseleave', '.fl-has-submenu', $.proxy( function( e ){
					$( e.currentTarget ).removeClass( 'fl-menu-submenu-right' );
				}, this ) );

		},

		/**
		 * Logic to prevent submenus to go behind the next overlay row.
		 *
		 * @since  1.10.9
		 * @return void
		 */
		_submenuRowZindexFix: function(){

			$( this.wrapperClass )
				.on( 'mouseenter', 'ul.menu > .fl-has-submenu', $.proxy( function( e ){

					if( $ ( e.currentTarget ).find('.sub-menu').length === 0 ) {
						return;
					}

					$( this.nodeClass )
						.closest( '.fl-row' )
						.find( '.fl-row-content' )
						.css( 'z-index', '10' );

				}, this ) )
				.on( 'mouseleave', 'ul.menu > .fl-has-submenu', $.proxy( function(){

					$( this.nodeClass )
						.closest( '.fl-row' )
						.find( '.fl-row-content' )
						.css( 'z-index', '' );

				}, this ) );
		},

		/**
		 * Logic for the mobile menu button.
		 *
		 * @since  1.6.1
		 * @return void
		 */
		_toggleForMobile: function(){

			var $wrapper = null,
				$menu    = null,
				self     = this;

			$( this.wrapperClass ).find( 'ul.menu' ).attr( 'role', 'menu');
			$( this.wrapperClass ).find( '.fl-menu-mobile-toggle' ).attr('aria-controls', $( this.wrapperClass ).find( 'ul.menu' ).attr('id'));

			if( this._isMenuToggle() ){

				if ( this._isMobileBelowRowEnabled() ) {
					this._placeMobileMenuBelowRow();
					$wrapper = $( this.wrapperClass );
					$menu    = $( this.nodeClass + '-clone' );
					$menu.find( 'ul.menu' ).show();
				}
				else {
					$wrapper = $( this.wrapperClass );
					$menu    = $wrapper.find( '.menu' );
				}

				if( !$wrapper.find( '.fl-menu-mobile-toggle' ).hasClass( 'fl-active' ) && ! self.mobileFlyout ){
					$menu.css({ display: 'none' });
				}

				// Flayout Menu
				if ( self.mobileFlyout ) {
					this._initFlyoutMenu();
				}

				$wrapper.on( 'click', '.fl-menu-mobile-toggle', function( e ){
					e.stopImmediatePropagation();

					$( this ).toggleClass( 'fl-active' );

					if ( self.mobileFlyout ) {
						self._toggleFlyoutMenu();
						const flyoutWrapper = $( '.fl-menu-mobile-flyout' );
						if ( $( this ).hasClass( 'fl-active' ) ) {
							flyoutWrapper.attr( 'aria-hidden', false );
							flyoutWrapper.find( 'a[href], button, input, select, textarea, span.fl-menu-toggle, [tabindex="-1"]' ).attr( 'tabindex', 0 );
						}
						else {
							flyoutWrapper.attr( 'aria-hidden', true );
							flyoutWrapper.find( 'a[href], button, input, select, textarea, span.fl-menu-toggle, [tabindex]:not([tabindex="-1"])' ).attr( 'tabindex', -1 );
						}
					}
					else {
						var targetMenu = null;

						if ( self.mobileBelowRow ) {
							var $closestCol = $( this ).parents( '.fl-col, .fl-module-box' ),
								$closestColGroup = $closestCol.length ? $closestCol.parent( '.fl-col-group' ) : null;
								targetMenu  = $closestCol.length ? $closestCol.last().next( '.fl-menu-mobile-clone' ) : null;

							if ( $closestColGroup.length ) {
								if ( $closestColGroup.hasClass( 'fl-col-group-responsive-reversed' ) ) {
									$closestColGroup.find( '.fl-menu-mobile-clone' ).css( 'order', -1 );
								} else if ( $closestColGroup ) {
									$closestColGroup.find( '.fl-menu-mobile-clone' ).css( 'order', 2 );
								}
							}
						} else {
							targetMenu = $( this ).closest( '.fl-menu' ).find( 'ul.menu' );
						}
						
						if ( targetMenu.length ) {
							$menu = $( targetMenu );
						}

						$menu.slideToggle();
					}

					e.stopPropagation();
				} );

				// Hide active menu when click on anchor link ID that exists on a page
				$menu.off().on( 'click', '.menu-item > a[href*="#"]:not([href="#"])', function(){
					var $href = $(this).attr('href'),
						$targetID = $href.split('#')[1],
						element = $('#' + $targetID);
					if ( $('body').find(element).length > 0 ) {
						$( this ).toggleClass( 'fl-active' );
						FLBuilderLayout._scrollToElement( element );
						if ( ! self._isMenuToggle() ) {
							$menu.slideToggle();
						}
					}
				});
			}
			else {

				if ( this._isMobileBelowRowEnabled() ) {
					this._removeMenuFromBelowRow();
				}

				$wrapper = $( this.wrapperClass ),
				$menu    = $wrapper.find( 'ul.menu' );
				$wrapper.find( '.fl-menu-mobile-toggle' ).removeClass( 'fl-active' );
				$menu.css({ display: '' });

				if ( ! this._isMobileBelowRowEnabled() ) {
					$menu.off( 'click', '.menu-item > a[href*="#"]:not([href="#"])' );
				}

				if ( this.mobileFlyout && $wrapper.find( '.fl-menu-mobile-flyout' ).length > 0 ) {
					$( 'body' ).css( 'margin', '' );
					$( '.fl-builder-ui-pinned-content-transform' ).css( 'transform', '' );
					$menu.unwrap();
					$wrapper.find( '.fl-menu-mobile-close' ).remove();
					$wrapper.find( '.fl-menu-mobile-opacity' ).remove();
				}
			}
		},

		/**
		 * Init any mega menus that exist.
		 *
		 * @see 	 this._isMenuToggle()
		 * @since  1.10.4
		 * @return void
		 */
		_initMegaMenus: function(){

			var module     = $( this.nodeClass ),
				rowContent = module.closest( '.fl-row-content' ),
				rowWidth   = rowContent.width(),
				megas      = module.find( '.mega-menu' ),
				disabled   = module.find( '.mega-menu-disabled' ),
				isToggle   = this._isMenuToggle();

			if ( isToggle ) {
				megas.removeClass( 'mega-menu' ).addClass( 'mega-menu-disabled' );
				module.find( 'li.mega-menu-disabled > ul.sub-menu' ).css( 'width', '' );
				rowContent.css( 'position', '' );
			} else {
				disabled.removeClass( 'mega-menu-disabled' ).addClass( 'mega-menu' );
				module.find( 'li.mega-menu > ul.sub-menu' ).css( 'width', rowWidth + 'px' );
				rowContent.css( 'position', 'relative' );
			}
		},

		/**
		 * Check to see if Below Row should be enabled.
		 *
		 * @since  1.11
		 * @return boolean
		 */
		_isMobileBelowRowEnabled: function() {
			return this.mobileBelowRow && ( $( this.nodeClass ).parents( '.fl-col, .fl-module-box' ).length );
		},

		/**
		 * Logic for putting the mobile menu below the menu's
		 * column so it spans the full width of the page.
		 *
		 * @since  1.10
		 * @return void
		 */
		_placeMobileMenuBelowRow: function(){

			if ( $( this.nodeClass + '-clone' ).length ) {
				return;
			}

			var module = $( this.nodeClass ),
				clone  = null,
				col    = module.parents( '.fl-col, .fl-module-box' ).last();

			if ( module.length < 1 ) {
				return;
			}

			clone = ( module.length > 1 ) ? $( module[0] ).clone() : module.clone();
			module.find( 'ul.menu' ).remove();
			clone.addClass( ( this.nodeClass + '-clone' ).replace( '.', '' ) );
			clone.addClass( 'fl-menu-mobile-clone' );
			clone.find( '.fl-menu-mobile-toggle' ).remove();
			col.after( clone );

			// Removes animation when enabled
			if ( module.hasClass( 'fl-animation' ) ) {
				clone.removeClass( 'fl-animation' );
			}

			this._menuOnFocus();
			this._menuOnClick();
			this._menuOnEscape();
		},

		/**
		 * Logic for removing the mobile menu from below the menu's
		 * column and putting it back in the main wrapper.
		 *
		 * @since  1.10
		 * @return void
		 */
		_removeMenuFromBelowRow: function(){

			if ( ! $( this.nodeClass + '-clone' ).length ) {
				return;
			}

			var module = $( this.nodeClass ),
				clone  = $( this.nodeClass + '-clone' ),
				menu   = clone.find( 'ul.menu' );

			module.find( '.fl-menu-mobile-toggle' ).after( menu );
			clone.remove();
			menu.find( 'a' ).each( FLBuilderLayout._initAnchorLink );
		},

		/**
		 * Logic for Flyout responsive menu.
		 *
		 * @since  2.2
		 * @return void
		 */
		_initFlyoutMenu: function(){
			var wrapper = $( this.wrapperClass ),
				menu  	= wrapper.find( 'ul.menu' ),
				button	= wrapper.find( '.fl-menu-mobile-toggle' );

			if ( 0 === wrapper.find( '.fl-menu-mobile-flyout' ).length ) {
				menu.wrap( '<div class="fl-menu-mobile-flyout" aria-hidden="true"></div>' );
			}

			if ( 0 === wrapper.find( '.fl-menu-mobile-close' ).length ) {
				var close = window.fl_responsive_close || 'Close'
				wrapper.find( '.fl-menu-mobile-flyout' ).prepend( '<button class="fl-menu-mobile-close fl-content-ui-button" aria-label="' + close + '"><i class="fas fa-times"></i></button>' );
			}

			// Push with opacity
			if ( wrapper.hasClass( 'fl-menu-responsive-flyout-push-opacity' ) && 0 === wrapper.find( '.fl-menu-mobile-opacity' ).length ) {
				wrapper.append( '<div class="fl-menu-mobile-opacity"></div>' );
			}

			wrapper.off( 'click', '.fl-menu-mobile-opacity, .fl-menu-mobile-close' ).on( 'click', '.fl-menu-mobile-opacity, .fl-menu-mobile-close', function(e){
				button.trigger('focus').trigger( 'click' );
				e.stopPropagation();
			});

			if ( 'undefined' !== typeof FLBuilder ) {
				FLBuilder.addHook('restartEditingSession', function(){
					$( '.fl-builder-ui-pinned-content-transform' ).css( 'transform', '' );

					// Toggle active menu
					if ( button.hasClass( 'fl-active' ) ) {
						button.trigger( 'click' );
					}
				});
			}
			$( '.fl-menu-mobile-flyout' ).find( 'a[href], button, input, select, textarea, span.fl-menu-toggle, [tabindex]:not([tabindex="-1"])' ).attr( 'tabindex', -1 );
		},

		/**
		 * Logic to enable/disable the Flyout menu on button click.
		 *
		 * @since  2.2
		 * @return void
		 */
		_toggleFlyoutMenu: function(){
			var wrapper		= $( this.wrapperClass ),
				button		= wrapper.find( '.fl-menu-mobile-toggle' ),
				position 	= wrapper.hasClass( 'fl-flyout-right' ) ? 'right' : 'left',
				pushMenu 	= wrapper.hasClass( 'fl-menu-responsive-flyout-push' ) || wrapper.hasClass( 'fl-menu-responsive-flyout-push-opacity' ),
				opacity		= wrapper.find( '.fl-menu-mobile-opacity' ),
				marginPos	= {},
				fixedPos 	= {},
				fixedHeader	= $('header, header > div');

			this._resizeFlyoutMenuPanel();

			// Fix the push menu when builder ui panel is pinned
			if ( $( '.fl-builder-ui-pinned-content-transform' ).length > 0 && ! $( 'body' ).hasClass( 'fl-builder-edit' ) ) {
				$( '.fl-builder-ui-pinned-content-transform' ).css( 'transform', 'none' );
			}

			if ( pushMenu ) {
				marginPos[ 'margin-' + position ] = button.hasClass( 'fl-active' ) ? this.flyoutWidth + 'px' : '0px';
				$( 'body' ).animate( marginPos, 200);

				// Fixed header
				if ( fixedHeader.length > 0 ) {
					fixedPos[ position] = button.hasClass( 'fl-active' ) ? this.flyoutWidth + 'px' : '0px';
					fixedHeader.each(function(){
						if ( 'fixed' == $( this ).css( 'position' ) ) {
							$( this ).css({ 'transition' : 'none' });
							$( this ).animate( fixedPos, 200 );
						}
					});
				}
			}

			if ( opacity.length > 0 && button.hasClass( 'fl-active' ) ) {
				opacity.show();
			}
			else {
				opacity.hide();
			}
		},

		/**
		 * Resize or reposition the Flyout Menu Panel.
		 * 
		 * @since  2.8.1
		 * @return void
		 */
		_resizeFlyoutMenuPanel: function(){
			const wrapper    = $( this.wrapperClass );
			const wrapFlyout = wrapper.find( '.fl-menu-mobile-flyout' );
				
			if ( wrapFlyout.length > 0 ) {
				wrapFlyout.css( this._getFlyoutMenuPanelPosition() );
			}
		},

		/**
		 * Compute the Flyout Menu Panel's position on the screen.
		 * 
		 * @since  2.8.1
		 * @return object
		 */
		_getFlyoutMenuPanelPosition: function() {
			var wrapper        = $( this.wrapperClass ),
				button         = wrapper.find( '.fl-menu-mobile-toggle' ),
				side           = wrapper.hasClass( 'fl-flyout-right' ) ? 'right' : 'left',
				winHeight      = $(window).outerHeight(),
				winTop         = $(window).scrollTop(),
				adminBarHeight = $( '#wpadminbar' ).length ? $( '#wpadminbar' ).height() : 0,
				flyoutPosition = {};

			flyoutPosition[ side ]  = '-' + ( parseInt( this.flyoutWidth ) + 15 ) + 'px';
			if ( ! button.hasClass( 'fl-active' ) ) {
				return flyoutPosition;
			}

			flyoutPosition[ side ]  = '0px';
			flyoutPosition[ 'height' ]  = winHeight + 'px';
			flyoutPosition[ 'top' ] = '0px';
			
			if ( adminBarHeight > 0 ) {
				const diff = adminBarHeight - winTop;
				flyoutPosition[ 'top' ] = diff <= 0 ? '0px' : (diff) + 'px';
			}

			return flyoutPosition;
		},

		/**
		 * Shows or hides the nav search form.
		 *
		 * @since  2.5
		 * @method _toggleMenuSearch
		 */
		_toggleMenuSearch: function(){
			var wrapper = $( this.wrapperClass ).find('.fl-menu-search-item'),
				button  = wrapper.find('.fl-button:is(a, button)'),
				form    = wrapper.find('.fl-search-form-input-wrap'),
				self    = this;

			button.attr( 'tabindex', 0 );
			button.attr( 'aria-label', 'Search' );
			button.on('click', function(e){
				e.preventDefault();

				if(form.is(':visible')) {
					form.stop().fadeOut(200);
				}
				else {
					form.stop().fadeIn(200);
					$('body').on('click.fl-menu-search', $.proxy(self._hideMenuSearch, self));
					form.find('.fl-search-text').focus();
				}
			});
		},

		/**
		 * Hides the nav search form.
		 *
		 * @since  2.5
		 * @method _hideMenuSearch
		 */
		_hideMenuSearch: function(e){
			var form = $( this.wrapperClass ).find('.fl-search-form-input-wrap');

			if(e !== undefined) {
				if($(e.target).closest('.fl-menu-search-item').length > 0) {
					return;
				}
			}

			form.stop().fadeOut(200);
			$('body').off('click.fl-menu-search');
		},

		/**
		 * Adds menu node and post ID to WooCommerce ajax URL requests.
		 *
		 * @since  2.5
		 * @return void
		 */
		_wooUpdateParams: function() {
			if ( 'undefined' !== typeof wc_cart_fragments_params ) {
				wc_cart_fragments_params.wc_ajax_url += '&fl-menu-node='+ this.nodeId +'&post-id='+ this.postId;
			}
			if ( 'undefined' !== typeof wc_add_to_cart_params ) {
				wc_add_to_cart_params.wc_ajax_url += '&fl-menu-node='+ this.nodeId +'&post-id='+ this.postId;
			}
		},
	};

})(jQuery);
