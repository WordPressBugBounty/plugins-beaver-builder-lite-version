( function( $ ) {

	/**
	 * Node IDs already reported by _logStrayOverlayNode, so a stray element the
	 * overlay sweep keeps revisiting is logged once per session rather than on
	 * every mouse move.
	 *
	 * @since 2.11
	 */
	const strayOverlayNodes = new Set();

	$.extend( FLBuilder, {

		/**
		 * An array of active overlay data to use during mouse events
		 * to prevent constantly querying the DOM.
		 *
		 * @since 2.11
		 * @property {Object} _activeOverlays
		 */
		_activeOverlays: {},

		/**
		 * Node ID for the currently selected node, if any.
		 *
		 * @since 2.8
		 * @property {String} _selectedNode
		 */
		_selectedNode: null,

		/**
		 * Initialize overlays.
		 *
		 * @since 2.8
		 */
		_initOverlays: function()
		{
			FLBuilder._bindGeneralOverlayEvents();
		},

		/**
		 * Binds general overlay events that don't get removed.
		 *
		 * @since 2.8
		 */
		_bindGeneralOverlayEvents: function()
		{
			var isTouch = FLBuilderLayout._isTouch();
			var body = $( 'body' );
			var parentBody = $( 'body', window.parent.document );
			var content = $( FLBuilder._contentClass );

			/* Context Menu */
			body.on( 'contextmenu', '.fl-block-overlay', FLBuilder._onOverlayContextMenu );

			/* Remove Overlays */
			FLBuilder.addHook( 'didStartNodeLoading', FLBuilder._removeAllOverlays );

			/* Selected Overlays */
			FLBuilder.addHook( 'didInitDrag', FLBuilder._deselectNodeOverlay );
			$( document ).add( window.parent.document ).on( 'keyup', FLBuilder._deselectNodeOverlayOnEsc );
			body.on( 'click', '[data-node]', FLBuilder._deselectNodeOverlayOnClick );
			body.on( 'click', '.fl-block-overlay-actions *', FLBuilder._deselectNodeOverlayOnActionsClick );
			parentBody.on( 'click', '.fl-builder-module-settings .fl-lightbox-footer button', FLBuilder._deselectNodeOverlay );
			parentBody.on( 'click', '.fl-builder-col-settings .fl-lightbox-footer button', FLBuilder._deselectNodeOverlay );
			parentBody.on( 'click', '.fl-builder-row-settings .fl-lightbox-footer button', FLBuilder._deselectNodeOverlay );
			parentBody.on( 'click', FLBuilder._deselectNodeOverlay );
			body.on( 'click', FLBuilder._deselectNodeOverlay );
			body.on( 'click', '.fl-block-overlay .fl-block-select-parent', FLBuilder._selectNodeParentOnIconClick);
			body.on( 'click', '.fl-block-overlay .fl-block-select-parent-menu > li > a', FLBuilder._selectNodeParentOnMenuClick);
			body.on( 'mouseenter', '.fl-block-overlay .fl-block-select-parent-menu a', FLBuilder._highlightNodeParentOnMenuHover);
			body.on( 'mouseleave', '.fl-block-overlay .fl-block-select-parent-menu a', FLBuilder._removeNodeParentHighlight);
			body.on( 'mousedown', '.fl-block-overlay .fl-block-select-parent-menu a', FLBuilder._removeNodeParentHighlight);

			/* Overlay Submenus */
			body.on( 'click touchend', '.fl-builder-has-submenu', FLBuilder._submenuParentClicked);
			body.on( 'mouseenter', '.fl-builder-submenu-hover', FLBuilder._hoverMenuParentMouseEnter);
			body.on( 'mouseleave', '.fl-builder-submenu-hover', FLBuilder._hoverMenuParentMouseLeave);
			body.on( 'click touchend', '.fl-builder-has-submenu a', FLBuilder._submenuChildClicked);
			body.on( 'mouseenter', '.fl-builder-submenu', FLBuilder._submenuMouseenter);
			body.on( 'mouseleave', '.fl-builder-submenu', FLBuilder._submenuMouseleave);
			body.on( 'mouseenter', '.fl-builder-submenu .fl-builder-has-submenu', FLBuilder._submenuNestedParentMouseenter);

			/* Generic Actions */
			body.on( 'click touchend', '.fl-block-overlay .fl-block-move-up', FLBuilder._moveNodeUpClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-move-down', FLBuilder._moveNodeDownClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-settings', FLBuilder._nodeSettingsClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-copy', FLBuilder._nodeDuplicateClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-remove', FLBuilder._nodeRemoveClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-unlink-global', FLBuilder._nodeUnlinkGlobalClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-edit-template', FLBuilder._nodeEditTemplateClicked);
			body.on( 'click touchend', '.fl-block-overlay .fl-block-save-as', FLBuilder._nodeOverlaySaveAsClicked);

			/* Rows */
			body.on( 'mousedown', '.fl-row-overlay .fl-block-move, .fl-row-move', FLBuilder._rowDragInit);
			body.on( 'touchstart', '.fl-row-overlay .fl-block-move, .fl-row-move', FLBuilder._rowDragInitTouch);
			body.on( 'click touchend', '.fl-row-quick-copy', FLBuilder._rowCopySettingsClicked);
			body.on( 'click touchend', '.fl-row-quick-paste', FLBuilder._rowPasteSettingsClicked);
			parentBody.on( 'click', '.fl-builder-row-settings .fl-builder-settings-save', FLBuilder._saveSettings);
			parentBody.on( 'click', '.fl-builder-dynamic-row-settings .fl-builder-settings-save', FLBuilder._saveSettings);

			// Row touch or mouse specific events.
			if ( isTouch ) {
				content.on( 'touchend', '.fl-row', FLBuilder._rowClicked);
			} else {
				content.on( 'click', '.fl-row', FLBuilder._rowClicked);
			}

			/* Rows Submenu */
			body.on( 'click touchend', '.fl-builder-submenu .fl-block-row-reset', FLBuilder._resetRowWidthClicked);

			/* Columns */
			body.on( 'mousedown', '.fl-col-overlay .fl-block-move, .fl-col-move', FLBuilder._colDragInit);
			body.on( 'touchstart', '.fl-col-overlay .fl-block-move, .fl-col-move', FLBuilder._colDragInitTouch);
			body.on( 'click touchend', '.fl-col-quick-copy', FLBuilder._colCopySettingsClicked);
			body.on( 'click touchend', '.fl-col-quick-paste', FLBuilder._colPasteSettingsClicked);
			body.on( 'click touchend', '.fl-builder-submenu .fl-block-col-reset', FLBuilder._resetColumnWidthsClicked);
			parentBody.on( 'click', '.fl-builder-col-settings .fl-builder-settings-save', FLBuilder._saveSettings);
			parentBody.on( 'click', '.fl-builder-dynamic-col-settings .fl-builder-settings-save', FLBuilder._saveSettings);

			// Column touch or mouse specific events.
			if ( isTouch ) {
				content.on( 'touchend', '.fl-col', FLBuilder._colClicked);
			} else {
				content.on( 'click', '.fl-col', FLBuilder._colClicked);
			}

			/* Modules */
			body.on( 'mousedown', '.fl-module-overlay .fl-block-move, .fl-module-move', FLBuilder._moduleDragInit);
			body.on( 'touchstart', '.fl-module-overlay .fl-block-move, .fl-module-move', FLBuilder._moduleDragInitTouch);
			body.on( 'click touchend', '.fl-module-quick-copy', FLBuilder._moduleCopySettingsClicked);
			body.on( 'click touchend', '.fl-module-quick-paste', FLBuilder._modulePasteSettingsClicked);
			parentBody.on( 'click', '.fl-builder-module-settings .fl-builder-settings-save', FLBuilder._saveModuleClicked);
			parentBody.on( 'click', '.fl-builder-dynamic-module-settings .fl-builder-settings-save', FLBuilder._saveSettings);

			// Module touch or mouse specific events.
			if ( isTouch ) {
				content.on( 'touchend', '.fl-module', FLBuilder._moduleClicked );
			} else {
				content.on( 'click', '.fl-module', FLBuilder._moduleClicked );
			}

			// Reposition visible overlays on window resize and scroll.
			$( window ).on( 'resize scroll', $.debounce( 100, FLBuilder._repositionOverlays ) );
		},

		/**
		 * Binds the events for overlays that appear when
		 * mousing over a row, column or module.
		 *
		 * @since 1.0
		 */
		_bindOverlayEvents: function()
		{
			if ( 'undefined' == typeof FLBuilderSettingsConfig.nodes ) {
				return;
			}

			const body = $( 'body' );
			const content = $( FLBuilder._contentClass );

			body.on( 'mousemove', FLBuilder._removeOverlaysOnMouseMove );
			content.on( 'mousemove touchstart', '.fl-row', FLBuilder._rowMousemove );
			content.on( 'mousemove touchstart', '.fl-col', FLBuilder._colMousemove );
			content.on( 'mousemove touchstart', '.fl-module', FLBuilder._moduleMousemove );
		},

		/**
		 * Unbinds the events for overlays that appear when
		 * mousing over a row, column or module.
		 *
		 * @since 1.0
		 */
		_destroyOverlayEvents: function()
		{
			if ( 'undefined' == typeof FLBuilderSettingsConfig.nodes ) {
				return;
			}

			const body = $( 'body' );
			const content = $( FLBuilder._contentClass );

			body.off( 'mousemove', FLBuilder._removeOverlaysOnMouseMove );
			content.undelegate( '.fl-row', 'mouseenter mousemove touchstart', FLBuilder._rowMousemove );
			content.undelegate( '.fl-col', 'mouseenter mousemove touchstart', FLBuilder._colMousemove );
			content.undelegate( '.fl-module', 'mouseenter mousemove touchstart', FLBuilder._moduleMousemove );
		},

		/**
		 * Hides overlays when the contextmenu event is fired on them.
		 * This allows us to inspect the actual node in the console
		 * instead of getting the overlay.
		 *
		 * @since 2.2
		 * @param {Object} e The event object.
		 */
		_onOverlayContextMenu: function( e )
		{
			$( this ).hide();
		},

		/**
		 * Selects the overlay for the specified node.
		 *
		 * @since 2.8
		 * @param {Object} node
		 * @param {Boolean} openSettings
		 */
		_selectNodeOverlay: function( node, openSettings = true )
		{
			if ( ! node.length ) {
				return;
			}

			var selected = $( '.fl-node-selected' );
			selected.removeClass( 'fl-node-selected' );
			node.addClass( 'fl-node-selected' );

			FLBuilder._selectedNode = node.data( 'node' );
			FLBuilder._hideTipTips();
			FLBuilder._removeColOverlays();

			if ( node.hasClass( 'fl-module' ) ) {
				FLBuilder._removeNonRootModuleOverlays( node );
			} else {
				FLBuilder._removeModuleOverlays();
			}

			node.trigger( 'mousemove' ); // Show the selected overlay

			if ( openSettings ) {
				var overlay = $( `.fl-block-overlay[data-node=${ node.attr( 'data-node' ) }]` );
				overlay.find( '.fl-block-settings:not(.fl-builder-submenu-link)' ).eq(0).trigger( 'click' );
			}
		},

		/**
		 * Selects the parent node for the current node overlay.
		 *
		 * @since 2.8
		 * @param {Object} e The event object.
		 */
		_selectNodeParentOnIconClick: function( e )
		{
			var overlay = $( this ).closest( '.fl-block-overlay' );
			var nodeId = overlay.attr( 'data-node' );
			var node = $( `.fl-node-${ nodeId }` );
			var parent = node.parents( '[data-node]:not(.fl-col-group)' ).eq( 0 );

			FLBuilder._selectNodeOverlay( parent );

			e.stopPropagation();
		},

		/**
		 * Selects a parent node from the parent select menu.
		 *
		 * @since 2.8
		 * @param {Object} e The event object.
		 */
		_selectNodeParentOnMenuClick: function( e )
		{
			var nodeId = $( this ).data( 'target-node' );
			var node = $( `.fl-node-${ nodeId }` );

			FLBuilder._selectNodeOverlay( node );
			FLBuilder._removeNodeParentHighlight();

			e.stopPropagation();
		},

		/**
		 * Highlights a parent node when hovered in the select menu.
		 *
		 * @since 2.8
		 */
		_highlightNodeParentOnMenuHover: function()
		{
			var nodeId = $( this ).data( 'target-node' );
			var node = $( `.fl-node-${ nodeId }` );

			FLBuilder._removeNodeParentHighlight();

			if ( node.hasClass( 'fl-block-overlay-active' ) ) {
				node.addClass( 'fl-overlay-highlight' );
			} else {
				node.addClass( 'fl-node-highlight' );
			}
		},

		/**
		 * Removes all parent node highlights.
		 *
		 * @since 2.8
		 */
		_removeNodeParentHighlight: function()
		{
			$( '.fl-node-highlight' ).removeClass( 'fl-node-highlight' );
			$( '.fl-overlay-highlight' ).removeClass( 'fl-overlay-highlight' );
		},

		/**
		 * Gets the menu data for the parent select menu.
		 *
		 * @since 2.8
		 * @param {Object} node
		 * @return {Array}
		 */
		_getNodeParentMenuData: function( node )
		{
			var selector = '.fl-row, .fl-col, .fl-module';
			var parents = node.parentsUntil( FLBuilder._contentClass, selector ).add( node );
			var data = [];

			if ( parents.length <= 1 ) {
				return null;
			}

			parents.each( function( i ) {
				var parent = $( this );

				// Don't show top most parent when editing global nodes.
				if ( parent.hasClass( 'fl-node-global' ) ) {
					if ( parent.hasClass( 'fl-row' ) && 'row' === FLBuilderConfig.userTemplateType ) {
						return;
					} else if ( parent.hasClass( 'fl-col' ) && 'column' === FLBuilderConfig.userTemplateType ) {
						return;
					} else if ( parent.hasClass( 'fl-module' ) && 'module' === FLBuilderConfig.userTemplateType && parent.data( 'node' ) === node.data( 'node' ) ) {
						return;
					}
				}

				data[ i ] = {
					node: parent.data( 'node' )
				};

				if ( parent.hasClass( 'fl-row' ) ) {
					data[ i ].name = FLBuilderStrings.row;
					data[ i ].type = 'row';
				} else if ( parent.hasClass( 'fl-col' ) ) {
					data[ i ].name = FLBuilderStrings.column;
					data[ i ].type = 'col';
				} else {
					data[ i ].name = parent.data( 'name' );
					data[ i ].type = 'module';
					data[ i ].moduleType = parent.data( 'type' );
				}
			} );

			return data;
		},

		/**
		 * Deselect the currently selected overlay.
		 *
		 * @since 2.8
		 */
		_deselectNodeOverlay: function()
		{
			$( '.fl-node-selected' ).each( function() {
				const node = $( this );
				const nodeId = node.attr( 'data-node' );

				node.removeClass( 'fl-node-selected' );
				node.removeClass( 'fl-block-overlay-active' );

				FLBuilder._removeOverlay( nodeId );
			} )

			FLBuilder._selectedNode = null;
		},

		/**
		 * Deselect the currently selected overlay on the esc key.
		 * Doing it like this because Mousetrap doesn't support multiple
		 * callbacks for the same key.
		 *
		 * @since 2.8
		 * @param {Object} e The event object.
		 */
		_deselectNodeOverlayOnEsc: function( e )
		{
			if ( e.key === 'Escape' ) {
				FLBuilder._deselectNodeOverlay();
			}
		},

		/**
		 * Deselect the currently selected overlay when an overlay is clicked.
		 *
		 * @since 2.8
		 * @param {Object} e The event object.
		 */
		_deselectNodeOverlayOnClick: function( e )
		{
			var nodeId = $( this ).attr( 'data-node' );
			var node = $( `.fl-node-${ nodeId }` );

			if ( node.hasClass( 'fl-node-selected' ) ) {
				e.stopImmediatePropagation();
			}

			FLBuilder._deselectNodeOverlay();
		},

		/**
		 * Deselect the currently selected overlay an overlay actions are clicked.
		 *
		 * @since 2.8
		 * @param {Object} e The event object.
		 */
		_deselectNodeOverlayOnActionsClick: function( e )
		{
			var overlay = $( this ).closest( '.fl-block-overlay' );
			var nodeId = overlay.attr( 'data-node' );
			var node = $( `.fl-node-${ nodeId }` );

			if ( node.hasClass( 'fl-node-selected' ) ) {
				return;
			}

			FLBuilder._deselectNodeOverlay();
		},

		/**
		 * Shows an overlay with actions when the mouse enters a row.
		 *
		 * @since 1.0
		 * @since 2.11 Changed to mousemove event
		 */
		_rowMousemove: function( e )
		{
			// Initial checks to efficiently bail early from mousemove
			const currentTarget = e.currentTarget;

			// Markup in a module's content can carry .fl-row without being a row —
			// see FLBuilder._isLayoutNode. Bail before _appendOverlay, which would
			// build an overlay around it for an undefined node ID. (#5558)
			if ( ! FLBuilder._isLayoutNode( currentTarget ) ) {
				return;
			}

			const isActive = currentTarget.classList.contains( 'fl-block-overlay-active' );
			const isPopover = $( e.target ).closest( '[popover]' ).length > 0;

			// Return if a popover is open
			if ( isPopover ) {
				return;
			}

			// Return if already active
			if ( isActive ) {
				return;
			}

			// Additional checks for bailing
			const row = $( this );
			const isLoading = row.closest( '.fl-builder-node-loading' ).length;
			const isInlineEditor = $( '.fl-inline-editor:visible' ).length;

			if ( isLoading || isInlineEditor ) {
				return;
			} else if ( FLBuilder._isHoveringUnrelatedRootOverlay( e, row ) ) {
				return;
			}

			// Remove existing overlays.
			FLBuilder._removeRowOverlays();

			// Data needed for the overlay.
			const id = row.attr( 'data-node' );
			const template = wp.template( 'fl-row-overlay' );
			const mode = FLBuilderResponsiveEditing._mode;
			const settings = FLBuilderSettingsConfig.nodes[ id ];
			const parentChildren = row.parent().find( '> .fl-row' );

			// Append the overlay.
			const overlay = FLBuilder._appendOverlay( row, template( {
				node : id,
				global : row.hasClass( 'fl-node-global' ),
				dynamic: !! row.attr( 'data-dynamic-editing' ),
				hasRules : row.hasClass( 'fl-node-has-rules' ),
				rulesTextRow : row.attr( 'data-rules-text' ),
				rulesTypeRow : row.attr( 'data-rules-type' ),
				nodeLabel : settings?.node_label,
				isFirst: 0 === parentChildren.index( row ),
				isLast: parentChildren.index( row ) === parentChildren.length - 1,
			} ) );

			// Put action headers on the bottom if they're hidden.
			if ( ( 'default' === mode && overlay.offset().top < 43 ) || ( 'default' !== mode && 0 === row.index() ) ) {
				overlay.addClass( 'fl-row-overlay-header-bottom' );
			}

			// Add the muted class if we have a select node in the row.
			if ( row.find( '.fl-node-selected' ).length || row.find( '.fl-block-overlay-active' ).length ) {
				$( 'body' ).addClass( 'fl-block-overlay-muted' );
			}

			// Build the overlay overflow menu if needed.
			if ( ! row.hasClass( 'fl-node-has-rules' ) ) {
				FLBuilder._buildOverlayOverflowMenu( overlay );
			}
		},

		/**
		 * Removes all row overlays from the page.
		 *
		 * @since 1.0
		 */
		_removeRowOverlays: function()
		{
			const overlays = $( '.fl-row-overlay' );

			FLBuilder._removeOverlays( overlays );
			FLBuilder._closeAllSubmenus();
			FL.Builder.data.getOutlinePanelActions().setFocusNode( false );
		},

		/**
		 * Shows an overlay with actions when the mouse enters a column.
		 *
		 * @since 1.1.9
		 * @since 2.11 Changed to mousemove event
		 */
		_colMousemove: function( e )
		{
			// Initial checks to efficiently bail early from mousemove
			const target = e.currentTarget;

			// Same guard as _rowMousemove, for .fl-col. (#5558)
			if ( ! FLBuilder._isLayoutNode( target ) ) {
				return;
			}

			const isActive = target.classList.contains( 'fl-block-overlay-active' );
			const isActiveChild = target.querySelector( '.fl-block-overlay-active' );

			if ( isActive || isActiveChild ) {
				return;
			}

			// Additional checks for bailing
			const col = $( this );
			const global = col.hasClass( 'fl-node-global' );
			const parentGlobal = col.parents( '.fl-node-global' ).length > 0;
			const isMove = $( e.relatedTarget ).closest( '.fl-block-move-dir' ).length;
			const isParentSelected = col.parents( '.fl-node-selected' ).length;
			const isLoading = col.closest( '.fl-builder-node-loading' ).length;
			const isChildLoading = col.find( '.fl-builder-node-loading-placeholder' ).length > 0;
			const isInlineEditor = $( '.fl-inline-editor:visible' ).length;

			// A global root (global, with no global ancestor) must always be able to
			// show its own overlay, even when a module fills its hoverable area.
			const isGlobalRoot = global && ! parentGlobal && ! FLBuilderConfig.userTemplateType;

			if ( isMove || isParentSelected || isLoading || isChildLoading || isInlineEditor ) {
				return;
			} else if ( global && parentGlobal && ! FLBuilderConfig.userTemplateType ) {
				return;
			} else if ( FLBuilder._isHoveringUnrelatedRootOverlay( e, col ) ) {
				return;
			} else if ( ! isGlobalRoot && FLBuilder._triggerColModuleOverlay( e, col ) ) {
				return;
			}

			// Remove existing overlays.
			FLBuilder._removeColOverlays();
			FLBuilder._removeModuleOverlays();

			// Data needed for the overlay.
			const group = col.closest( '.fl-col-group' );
			const numCols = col.closest( '.fl-col-group' ).find( '> .fl-col' ).length;
			const index = group.find( '> .fl-col' ).index( col );
			const parentCol = col.parents( '.fl-col' );
			const parentGroup = parentCol.closest( '.fl-col-group' );
			const hasParentCol = parentCol.length > 0;
			const numParentCols = hasParentCol ? parentGroup.find( '> .fl-col' ).length : 0;
			const parentIndex = parentGroup.find( '> .fl-col' ).index( parentCol );
			const row = col.closest('.fl-row');
			const template = wp.template( 'fl-col-overlay' );
			const colNode = col.attr( 'data-node' );
			const settings = FLBuilderSettingsConfig.nodes[ colNode ];

			// Append the template.
			const overlay = FLBuilder._appendOverlay( col, template( {
				global	      		: global,
				dynamic	      		: !! col.attr( 'data-dynamic-editing' ),
				parentDynamic		: !! col.closest( '[data-dynamic-editing]' ).length,
				groupLoading  		: group.hasClass( 'fl-col-group-has-child-loading' ),
				numCols	      		: numCols,
				isFirst         	: 0 === index,
				isLast   	      	: numCols === index + 1,
				isRootCol     		: 'column' == FLBuilderConfig.userTemplateType && ! hasParentCol,
				node				: colNode,
				hasChildCols  		: col.find( '.fl-col' ).length > 0,
				hasParentCol  		: hasParentCol,
				parentFirst   		: hasParentCol ? 0 === parentIndex : false,
				parentLast    		: hasParentCol ? numParentCols === parentIndex + 1 : false,
				numParentCols 		: numParentCols,
				rowIsFixedWidth 	: !! row.find('.fl-row-fixed-width').addBack('.fl-row-fixed-width').length,
				userCanResizeRows 	: FLBuilderConfig.rowResize.userCanResizeRows,
				hasRules			: col.hasClass( 'fl-node-has-rules' ),
				nodeLabel 			: settings?.node_label,
				parentMenu			: FLBuilder._getNodeParentMenuData( col ),
				isTemplate			: 'undefined' !== typeof col.data('template-url'),
			} ) );

			// Mute any parent overlays
			if ( ! col.closest( '.fl-row.fl-node-selected' ).length ) {
				$('body').addClass('fl-block-overlay-muted');
			}

			// Build the overlay overflow menu if needed.
			FLBuilder._buildOverlayOverflowMenu( overlay );

			// Init column resizing.
			FLBuilder._initColDragResizing();
		},

		/**
		 * Checks to see if the mouse is hovering over a module inside a column.
		 * This check takes margins into account so we can begin showing the 
		 * module overlay when margins are entered, instead of the col overlay. 
		 * If we need to show the module overlay, trigger it.
		 * 
		 * @since 2.11
		 * @param {Object} e
		 * @param {Object} col
		 * @return {Boolean|Object}
		 */
		_triggerColModuleOverlay: function( e, col ) {
			let hovering = false;

			col.find( '.fl-module' ).each( function() {
				const module = $( this );
				const { top, left, width, height } = FLBuilder._getOverlayPositionData( module );

				// Add a 2px buffer for when col and module edges touch.
				if ( e.clientX >= left - 2 && 
					e.clientY >= top - 2 &&
					e.clientX <= left + width + 2 &&
					e.clientY <= top + height + 2 ) {
					hovering = module;
				}
			} );

			if ( hovering ) {
				hovering.trigger( 'mousemove' );
			}

			return hovering;
		},

		/**
		 * Removes all column overlays from the page.
		 *
		 * @since 1.6.4
		 */
		_removeColOverlays: function()
		{
			const overlays = $( '.fl-col-overlay' );

			FLBuilder._removeOverlays( overlays );
			FLBuilder._closeAllSubmenus();
			FL.Builder.data.getOutlinePanelActions().setFocusNode( false );
		},

		/**
		 * Shows an overlay with actions when the mouse enters a module.
		 *
		 * @since 1.0
		 * @since 2.11 Changed to mousemove event
		 */
		_moduleMousemove: function( e )
		{
			// Initial checks to efficiently bail early from mousemove
			const currentTarget = e.currentTarget;

			// Same guard as _rowMousemove, for .fl-module — the reported case: a
			// <div class="fl-module"> inside an HTML module's content, or markup
			// copied straight off a rendered page (which carries data-node but
			// never data-type). Either way it isn't a real module, so its overlay
			// would be appended with no data-type/data-name, because
			// _appendOverlay passes the undefined values to .attr(), which
			// jQuery reads as a getter. Clicking that overlay's gear then opens
			// settings for no node. (#5558, #5597)
			if ( ! FLBuilder._isModuleNode( currentTarget ) ) {
				return;
			}

			const isActive = currentTarget.classList.contains( 'fl-block-overlay-active' );
			const isRootModule = currentTarget.parentElement.classList.contains( 'fl-builder-content' );
			const isActiveChild = ! isRootModule && currentTarget.querySelector( '.fl-block-overlay-active' );
			const isPopover = $( e.target ).closest( '[popover]' ).length > 0;
			const inPopover = $( currentTarget ).closest( '[popover]' ).length > 0;

			// Return if a popover is open and this node is outside of it
			if ( isPopover && ! inPopover ) {
				return;
			}

			// Return if already active or child is active
			if ( isActive || isActiveChild ) {
				return;
			}

			// Additional checks for bailing
			const module = $( this );
			const global = module.hasClass( 'fl-node-global' );
			const parentGlobal = module.parents( '.fl-node-global' ).length > 0;
			const isMove = $( e.relatedTarget ).closest( '.fl-block-move-dir' ).length;
			const isParentSelected = module.parents( '.fl-node-selected' ).length;
			const isLoading = module.closest( '.fl-builder-node-loading' ).length;
			const isInlineEditor = $( '.fl-inline-editor:visible' ).length;

			const parentTemplate = module.parents( '[data-template-url]' ).first();
			const isParentTemplateNested = parentTemplate.parents( '[data-template-url]' ).first().length;

			const templateType = FLBuilderConfig.userTemplateType;
			const isNodeTemplate = templateType && 'layout' !== templateType;
			const isLayout = ! templateType || 'layout' === templateType;

			if ( isMove || isParentSelected || isLoading || isInlineEditor ) {
				return;
			} else if ( isNodeTemplate && global && isParentTemplateNested ) {
				return;
			} else if ( isLayout && global && parentGlobal ) {
				return;
			} else if ( FLBuilder._isHoveringUnrelatedRootOverlay( e, module ) ) { 
				return;
			} 

			// Remove existing overlays.
			FLBuilder._removeColOverlays();
			FLBuilder._removeNonRootModuleOverlays( module );

			// Data needed for the overlay.
			const id = module.attr( 'data-node' );
			const settings = FLBuilderSettingsConfig.nodes[ id ];
			const group = module.parents( '.fl-col-group' ).last();
			const numCols = module.closest( '.fl-col-group' ).find( '> .fl-col' ).length;
			const col = module.closest( '.fl-col' );
			const parentCol = col.parents( '.fl-col' );
			const hasParentCol = parentCol.length > 0;
			const numParentCols = hasParentCol ? parentCol.closest( '.fl-col-group' ).find( '> .fl-col' ).length : 0;
			const row = module.closest('.fl-row');
			const hasParentModule = module.parents( '.fl-module' ).length > 0;
			const parentChildren = module.parent().find( '> [data-node]' );
			const template = wp.template( 'fl-module-overlay' );

			// Append the template.
			const overlay = FLBuilder._appendOverlay( module, template( {
				global 		  		: global,
				dynamic 		  	: !! module.attr( 'data-dynamic-editing' ),
				parentDynamic 		: !! module.closest( '[data-dynamic-editing]' ).length,
				moduleType	  		: module.attr( 'data-type' ),
				node				: id,
				moduleName	  		: module.attr( 'data-name' ),
				nodeLabel			: settings?.node_label,
				groupLoading  		: group.hasClass( 'fl-col-group-has-child-loading' ),
				numCols		  		: numCols,
				colFirst      		: col.index() <= 0,
				colLast       		: numCols === col.index() + 1,
				hasParentCol  		: hasParentCol,
				numParentCols 		: numParentCols,
				parentFirst   		: hasParentCol ? 0 === parentCol.index() : false,
				parentLast    		: hasParentCol ? numParentCols === parentCol.index() + 1 : false,
				rowIsFixedWidth 	: !! row.find('.fl-row-fixed-width').addBack('.fl-row-fixed-width').length,
				userCanResizeRows 	: FLBuilderConfig.rowResize.userCanResizeRows,
				hasRules          	: module.hasClass( 'fl-node-has-rules' ),
				rulesTextModule   	: module.attr('data-rules-text'),
				rulesTypeModule   	: module.attr('data-rules-type'),
				rulesTextCol      	: col.attr('data-rules-text'),
				rulesTypeCol      	: col.attr('data-rules-type'),
				colHasRules       	: col.hasClass( 'fl-node-has-rules' ),
				parentMenu		  	: FLBuilder._getNodeParentMenuData( module ),
				isRootModule	  	: 'module' === FLBuilderConfig.userTemplateType && ! hasParentModule,
				isFirst			  	: 0 === parentChildren.index( module ),
				isLast			  	: parentChildren.index( module ) === parentChildren.length - 1,
				layoutDirection	  	: FLBuilder._getNodeLayoutDirection( module ),
				isTemplate		  	: 'undefined' !== typeof module.data('template-url'),
				dynamicEditing    	: !! module.attr( 'data-dynamic-editing' ),
			} ) );

			// Add the root class to the overlay.
			if ( isRootModule ) {
				overlay.addClass( 'fl-root-module-overlay' );
			}

			// Mute any parent overlays
			const parentRow = module.closest( '.fl-row' );
			const parentRowSelected = parentRow.hasClass( 'fl-node-selected' );
			const rootModule = module.parents().filter( function() {
				const $this = $( this );
				return $this.hasClass( 'fl-module' ) && $this.parent().is( FLBuilder._contentClass );
			} ).first();

			if ( ( parentRow.length && ! parentRowSelected ) || rootModule.length ) {
				$('body').addClass('fl-block-overlay-muted');
			}

			// Build the overlay overflow menu if necessary.
			FLBuilder._buildOverlayOverflowMenu( overlay );

			// Init column resizing.
			FLBuilder._initColDragResizing();
		},

		/**
		 * Removes all module overlays from the page.
		 *
		 * @since 1.6.4
		 */
		_removeModuleOverlays: function( overlays = null )
		{
			const moduleOverlays = overlays ? overlays : $( '.fl-module-overlay' );

			FLBuilder._removeOverlays( moduleOverlays );
			FLBuilder._closeAllSubmenus();
			FL.Builder.data.getOutlinePanelActions().setFocusNode( false );
		},

		/**
		 * Removes all module overlays when the mouse enters 
		 * a module except for the overlays of parents that 
		 * are root nodes in the layout.
		 *
		 * @since 2.11
		 */
		_removeNonRootModuleOverlays: function( module ) {
			const root = module.parents().filter( function() {
				const $this = $( this );
				return $this.hasClass( 'fl-module' ) && $this.parent().is( FLBuilder._contentClass );
			} ).first();

			if ( ! root.length ) {
				FLBuilder._removeModuleOverlays();
			} else {
				const overlays = $( '.fl-module-overlay' ).filter( function() {
					return root.length && root.data( 'node' ) !== $( this ).data( 'node' ) ? true : false;
				} );

				FLBuilder._removeModuleOverlays( overlays );
			}
		},

		/**
		 * Removes all node overlays and hides any tooltip helpies.
		 *
		 * @since 1.0
		 */
		_removeAllOverlays: function()
		{
			FLBuilder._removeRowOverlays();
			FLBuilder._removeColOverlays();
			FLBuilder._removeModuleOverlays();
			FLBuilder._closeAllSubmenus();
		},

		/**
		 * Appends a node action overlay to the layout.
		 *
		 * @since 1.6.3.3
		 * @param {Object} node A jQuery reference to the node this overlay is associated with.
		 * @param {Object} template A rendered wp.template.
		 * @return {Object} The overlay element.
		 */
		_appendOverlay: function( node, template )
		{
			const overlay = $( template );
			const nodeId = node.attr('data-node');

			// Add the active class to the node.
			node.addClass( 'fl-block-overlay-active' );

			// Focus the node in the outline panel.
			FL.Builder.data.getOutlinePanelActions().setFocusNode( nodeId );

			// Set the overlay data-node attribute.
			overlay.attr( 'data-node', nodeId );

			// Append the overlay.
			$( 'body' ).append( overlay );

			// Show the overlay. Guarded, and before _positionOverlay, which caches
			// this element's rect — see FLBuilder._showPopover.
			FLBuilder._showPopover( overlay[0] );

			// Position the overlay.
			FLBuilder._positionOverlay( overlay, node );

			// Init TipTips
			FLBuilder._initTipTips();

			return overlay;
		},

		/**
		 * Positions the overlay relative to the node it controls.
		 * 
		 * @since 2.11
		 */
		_positionOverlay: function( overlay, node ) {
			const nodeId = node.attr( 'data-node' );
			const { top, left, width, height } = FLBuilder._getOverlayPositionData( node );

			// Set the overlay's position and size.
			overlay.css( {
				'top': top + 'px',
				'left': left + 'px',
				'width':  width + 'px',
				'height': height + 'px'
			} );

			// Store the overlay data.
			FLBuilder._activeOverlays[ nodeId ] = {
				overlay,
				node,
				rect: overlay[0].getBoundingClientRect(),
				children: node.children( '[data-node]' )
			};

		},

		/**
		 * Returns the data for positioning an overlay.
		 * 
		 * @param {Object} node
		 * @return {Object}
		 * @since 2.11
		 */
		_getOverlayPositionData: function( node ) {
			const nodeId = node.attr( 'data-node' );

			// Get the node's content wrapper.
			let content = node.find( '> .fl-node-content' );
			if ( content.length === 0 ) {
				content = node;
			} else if ( node.hasClass( 'fl-row' ) ) {
				content = node.find( '> .fl-row-content-wrap' );
			}

			// Get the node's bounding rectangle and offset.
			const rect = content[0].getBoundingClientRect();
			const offset = content.offset();
			
			// Get the node's margins.
			const margins = { top: 0, right: 0, bottom: 0, left: 0 };
			if ( ! node.attr( 'popover' ) ) {
				margins.top = parseInt( content.css( 'margin-top' ), 10 );
				margins.top = margins.top < 0 ? 0 : margins.top;
				margins.right = parseInt( content.css( 'margin-right' ), 10 );
				margins.right = margins.right < 0 ? 0 : margins.right;
				margins.bottom = parseInt( content.css( 'margin-bottom' ), 10 );
				margins.bottom = margins.bottom < 0 ? 0 : margins.bottom;
				margins.left = parseInt( content.css( 'margin-left' ), 10 );
				margins.left = margins.left < 0 ? 0 : margins.left;
			}

			// Get the node's size and position.
			let width = rect.width + margins.left + margins.right;
			let height = rect.height + margins.top + margins.bottom;
			let top = offset.top - margins.top;
			let left = offset.left - margins.left;
			
			// Adjust row overlay position if covered by negative margin content.
			// Otherwise, push the overlay up to account for child overlays.
			// Exclude the pop-up module since it is only a placeholder.
			const isRow = node.hasClass( 'fl-row' );
			const isRootModule = node.hasClass( 'fl-module:not(.fl-popup-wrapper)' ) && node.parent().is( FLBuilder._contentClass );
			const actions = $( `[data-node=${ nodeId }] .fl-block-overlay-actions` );
			
			if ( actions.length && ( isRow || isRootModule ) ) {
				const actionsHeight = actions.height();
				let childTop = null;

				node.find( '[data-node]:visible, .fl-node-content:visible' ).each( function() {
					const child = $( this );
					const childPosition = child.css( 'position' );
					const childFloating = 'absolute' === childPosition || 'fixed' === childPosition;
					const parentPosition = child.parent().css( 'position' );
					const parentFloating = 'absolute' === parentPosition || 'fixed' === parentPosition;
					
					if ( ! childFloating && ! parentFloating ) {
						const top = child.offset().top;
						childTop = ( null === childTop || childTop > top ) ? top : childTop;
					}
				} );

				if ( null !== childTop && childTop < top ) {
					height = top - childTop + height + actionsHeight;
					top = ( childTop - actionsHeight );
				} else {
					height += actionsHeight;
					top -= actionsHeight;
				}
			}

			// Ensure the overlay is within the viewport.
			const winWidth = $( window ).width();
			left = Math.max( left, 0 );
			top = Math.max( top, 0 );
			if ( left + width > winWidth ) {
				width = winWidth - left;
			}

			return { top, left, width, height }
		},

		/**
		 * Reposition any active overlays.
		 * 
		 * @since 2.11
		 */
		_repositionOverlays: function() {
			requestAnimationFrame( () => {
				for ( nodeId in FLBuilder._activeOverlays ) {
					const { overlay } = FLBuilder._activeOverlays[ nodeId ];
					const node = $( `.fl-node-${ nodeId }` );
					FLBuilder._positionOverlay( overlay, node );
				}
			} );
		},

		/**
		 * Builds the overflow menu for an overlay if necessary.
		 *
		 * @since 1.9
		 * @param {Object} overlay The overlay object.
		 */
		_buildOverlayOverflowMenu: function( overlay )
		{
			var header        = overlay.find( '.fl-block-overlay-header' ),
				actions       = overlay.find( '.fl-block-overlay-actions' ),
				hasRules	  = overlay.find( '.fl-block-has-rules' ),
				original      = actions.data( 'original' ),
				actionsWidth  = 0,
				actionsLeft   = 0,
				actionsRight  = 0,
				items         = null,
				itemsWidth    = 0,
				item          = null,
				i             = 0,
				visibleItems  = [],
				overflowItems = [],
				menuData      = [],
				template	  = wp.template( 'fl-overlay-overflow-menu' );

			// Use the original copy if we have one.
			if ( undefined != original ) {
				actions.after( original );
				actions.remove();
				actions = original;
			}

			// Save a copy of the original actions.
			actions.data( 'original', actions.clone() );

			// Get the actions width and items.
			actionsLeft   = parseInt( actions.css( 'padding-left' ) );
			actionsRight  = parseInt( actions.css( 'padding-right' ) );
			actionsWidth  = actions.outerWidth() - actionsLeft - actionsRight;
			items         = actions.find( ' > i, > span' );

			// Add the width of the visibility rules indicator if there is one.
			if ( hasRules.length && actionsWidth + hasRules.outerWidth() > header.outerWidth() ) {
				itemsWidth += hasRules.outerWidth();
			}

			// Remove the max-width to calculate true item width.
			actions.css( 'max-width', 'none' );

			// Find visible and overflow items.
			for( ; i < items.length; i++ ) {

				item        = items.eq( i );
				itemsWidth += Math.floor(item[0].getBoundingClientRect().width);

				if ( itemsWidth > actionsWidth ) {
					overflowItems.push( item );
					item.remove();
				}
				else {
					visibleItems.push( item );
				}
			}

			// Add the max-width back.
			actions.css( 'max-width', '100%' );

			// Build the menu if we have overflow items.
			if ( overflowItems.length > 0 ) {

				if( visibleItems.length > 0 ) {
					overflowItems.unshift( visibleItems.pop().remove() );
				}

				for( i = 0; i < overflowItems.length; i++ ) {

					if ( overflowItems[ i ].is( '.fl-builder-has-submenu' ) ) {
						menuData.push( {
							type    : 'submenu',
							label   : overflowItems[ i ].find( '.fa, .fas, .far, svg, span' ).data( 'title' ),
							submenu : overflowItems[ i ].find( '.fl-builder-submenu' )[0].outerHTML,
							className : overflowItems[ i ].find( '> span, > i, > svg' ).removeClass( function( i, c ) {
											return c.replace( /fl-block-([^\s]+)/, '' );
										} ).attr( 'class' )
						} );
					}
					else {
						menuData.push( {
							type      : 'action',
							label     : overflowItems[ i ].data( 'title' ),
							className : overflowItems[ i ].removeClass( function( i, c ) {
											return c.replace( /fl-block-([^\s]+)/, '' );
										} ).attr( 'class' )
						} );
					}
				}

				actions.append( template( menuData ) );
				FLBuilder._initTipTips();
			}
		},

		/*
		 * Remove all passed overlays from the DOM.
		 * 
		 * @param {Object} overlays 
		 * @since 2.11
		 */
		_removeOverlays: function( overlays ) {
			overlays.each( function() {
				FLBuilder._removeOverlay( $( this ).attr( 'data-node' ) );
			} );
		},

		/**
		 * Remove the overlay with the passed node ID from the DOM.
		 * 
		 * Overlay lookups are scoped to body's own children, because that is where
		 * _appendOverlay puts every real overlay, while module *content* can carry
		 * the same overlay classes and a data-node of its own (pasted builder
		 * markup) and would match first in document order. A swept element may
		 * still have no real overlay at all — see _logStrayOverlayNode. (#5558)
		 *
		 * Both IDs are CSS.escape'd for the same reason, and it is the same bug:
		 * the sweep reads nodeId straight off page DOM, so pasted markup can
		 * carry one that is not a valid CSS identifier. Selector construction
		 * then throws a jQuery syntax error from inside the .each(), which does
		 * not catch — stranding every remaining overlay exactly the way an
		 * unguarded hidePopover() did. Matches _isHoveringOverlay below.
		 *
		 * @param {String} nodeId
		 * @since 2.11
		 */
		_removeOverlay: function( nodeId ) {
			const body = $( 'body' ), escapedId = CSS.escape( nodeId );
			const overlay = $( `body > .fl-block-overlay[data-node=${ escapedId }]` );
			const node = $( `.fl-node-${ escapedId }` );

			if ( node.hasClass( 'fl-node-selected' ) ) {
				return;
			}

			FLBuilder._removeNodeParentHighlight();
			FLBuilder._hideTipTips();

			// The teardown below is a safe no-op on an empty set, and must still
			// run so the bookkeeping is cleared either way.
			if ( overlay.length ) {
				FLBuilder._hidePopover( overlay[0] );
			} else {
				FLBuilder._logStrayOverlayNode( nodeId );
			}
			overlay.find( '*' ).off();
			overlay.off().empty().remove();
			node.removeClass( 'fl-block-overlay-active' );
			body.removeClass( 'fl-builder-row-resizing' );

			if ( ! $( 'body > .fl-block-overlay:not(.fl-row-overlay, .fl-root-module-overlay)' ).length ) {
				body.removeClass( 'fl-block-overlay-muted' );
			}

			delete FLBuilder._activeOverlays[ nodeId ];
		},

		/**
		 * Reports a node ID that was swept as an overlay but has no real overlay
		 * on the body — pasted or third-party markup carrying an overlay class,
		 * or an overlay appended for a node with no data-node attribute.
		 *
		 * Logged once per ID per session on purpose: the sweep runs from body
		 * mousemove handlers, so this is a hot path, but the silence is what made
		 * #5558 undiagnosable — nothing pointed at the module holding the markup.
		 *
		 * @param {String} nodeId
		 * @since 2.11
		 */
		_logStrayOverlayNode: function( nodeId ) {
			if ( strayOverlayNodes.has( nodeId ) ) {
				return;
			}

			strayOverlayNodes.add( nodeId );

			FLBuilder.log( `Beaver Builder: swept an element carrying an overlay class with no matching overlay (data-node="${ nodeId }"). Check that node's content for pasted builder markup. See issue #5558.` );
		},

		/**
		 * Removes overlays on the mousemove event. This allows us to remove
		 * overlays when the overlay partially sits outside of the node's 
		 * hit area and overlaps the document outside of the layout.
		 * 
		 * @param {Object} e
		 * @since 2.11 
		 */
		_removeOverlaysOnMouseMove: function( e ) {
			for ( nodeId in FLBuilder._activeOverlays ) {
				if ( ! FLBuilder._isHoveringOverlay( e, nodeId ) ) {
					FLBuilder._removeOverlay( nodeId );
				}
			}
		},

		/**
		 * Checks if the mouse is hovering over an overlay.
		 * This is used to prevent the overlay from being removed
		 * when the mouse leaves the node but should still be showing
		 * because of space outside (e.g., margins).
		 * 
		 * @since 2.11
		 * @param {Object} e The event object.
		 * @return {Boolean} True if hovering, false otherwise.
		 */
		_isHoveringOverlay: function( e, nodeId ) {
			if ( FLBuilder._activeOverlays[ nodeId ] ) {
				const { rect } = FLBuilder._activeOverlays[ nodeId ];

				if ( e.clientX >= rect.left && 
					e.clientY >= rect.top &&
					e.clientX <= rect.right &&
					e.clientY <= rect.bottom ) {
					return true;
				} else {
					const overElement = e.clientX && e.clientY ? document.elementFromPoint( e.clientX, e.clientY ) : null;
					const overElementNodeId = overElement ? overElement.closest( '[data-node]' )?.getAttribute( 'data-node' ) : null;
					const overElementNode = overElementNodeId ? $( `.fl-node-${ CSS.escape( overElementNodeId ) }` ) : null;
					const isNodeChild = overElementNode ? !! overElementNode.closest( `[data-node=${ CSS.escape( nodeId ) }]` ).length : false;

					if ( isNodeChild ) {
						return true;
					}
				}
			}
			
			return false;
		},

		/**
		 * Returns the id of an active overlay whose rect contains the
		 * event's coordinates, or null if none does. Skips nodeId itself
		 * and any descendants of that node.
		 *
		 * Overlays are pointer-events: none over their empty areas, so
		 * clicks on the strip an overlay extends above its node fall
		 * through to whatever sits underneath in the layout DOM (often
		 * an unrelated row). Click handlers use this to route the click
		 * to the visually-correct node.
		 *
		 * @since 2.11
		 * @param {Object} e The event object.
		 * @param {String} nodeId The node id of the handler that received the click.
		 * @return {String|null}
		 */
		_findOverlayClaimingClick: function( e, nodeId ) {
			for ( const otherId in FLBuilder._activeOverlays ) {
				if ( otherId === nodeId ) {
					continue;
				}

				const { rect, node } = FLBuilder._activeOverlays[ otherId ];

				// Skip descendants — their overhang inside this node's space
				// shouldn't redirect a row click to a child.
				if ( node.closest( `[data-node="${ nodeId }"]` ).length ) {
					continue;
				}

				// Skip when the click is inside the other node's own bounding
				// rect. That's natural territory (e.g. a column click inside
				// the row that owns the surrounding overlay), not an overhang.
				const nodeRect = node[0].getBoundingClientRect();
				if ( e.clientX >= nodeRect.left && e.clientX <= nodeRect.right &&
					 e.clientY >= nodeRect.top  && e.clientY <= nodeRect.bottom ) {
					continue;
				}

				if ( e.clientX >= rect.left && e.clientX <= rect.right &&
					 e.clientY >= rect.top  && e.clientY <= rect.bottom ) {
					return otherId;
				}
			}

			return null;
		},

		/**
		 * Checks if the mouse is hovering over a root node overlay that
		 * is not a parent of the passed node. This ensures overlays stay
		 * active even if you mouse out of the node, but are still on the
		 * overlay, since root node overlays extend beyond the top of the node.
		 *
		 * @since 2.11
		 * @param {Object} e The event object.
		 * @param {String} type The type of overlay.
		 * @return {Boolean} True if hovering, false otherwise.
		 */
		_isHoveringUnrelatedRootOverlay: function( e, node ) {
			let rootId = node.attr( 'data-node' );

			if ( ! node.parent().is( FLBuilder._contentClass ) ) {
				rootId = node.parents().filter( function() {
					return $( this ).parent().is( FLBuilder._contentClass );
				} ).first().attr( 'data-node' );
			}
			
			for ( nodeId in FLBuilder._activeOverlays ) {
				const { overlay } = FLBuilder._activeOverlays[ nodeId ];
				const isRow = overlay.hasClass( 'fl-row-overlay' );
				const isRootModule = overlay.hasClass( 'fl-root-module-overlay' );

				if ( ! isRow && ! isRootModule ) {
					continue;
				} else if ( rootId !== nodeId && FLBuilder._isHoveringOverlay( e, nodeId ) ) {
					return true;
				}
			}

			return false;
		},
	} );

	$( function() {
		FLBuilder._initOverlays();
	} );

} )( jQuery );
