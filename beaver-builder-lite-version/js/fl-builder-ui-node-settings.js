/**
 * UI logic for rendering node settings.
 */
( function( $ ) {

	$.extend( FLBuilder, {
        
        /**
		 * Called when the node settings overlay action is clicked.
		 *
		 * @since 2.8
		 */
		_nodeSettingsClicked: function( e )
		{
			const button = $( this );
			const nodeId = button.closest( '[data-node]' ).attr( 'data-node' );
			const targetNodeId = button.attr('data-target-node') || nodeId;
			const targetNode  = $( `.fl-node-${ targetNodeId }` );

			e.stopPropagation();
			e.preventDefault();

			if ( FLBuilderConfig.postType === 'fl-builder-template' && targetNode.data( 'node-type' ) === 'module' ) {

				const templateNode            = targetNode.closest('[data-template-url]');
				const module                  = targetNode;
				const isModuleTemplateEditing = !! $( module ).closest( '.fl-builder-content-editing' ).is( '.fl-builder-module-template' );

				if ( isModuleTemplateEditing ) {
					FLBuilder.showNodeSettings( { nodeId: targetNodeId } );
				} else {
					FLBuilder._showModuleSettingsOfTemplateNode( templateNode, module );
				}

			} else {
				FLBuilder.showNodeSettings( { nodeId: targetNodeId } );
			}
		},

		showNodeSettings: function( targetNode ) {
			const currentNode      = $( `[data-node="${ targetNode.nodeId }"]` );
			const parentNodeId     = targetNode.parentNodeId ?? currentNode.data( 'parent' );
			const nodeType         = targetNode.nodeType ?? currentNode?.attr( 'data-node-type' );
			const moduleType       = targetNode.moduleType ?? currentNode?.attr( 'data-type' );
			const isNewModule      = false;
			const isTemplate       = FLBuilderConfig.userTemplateType === nodeType;
			const templateUrl      = targetNode.templateUrl ?? currentNode.data( 'template-url' );
			const isGlobalNode     = targetNode.global ?? !! currentNode.data( 'global' );
			const isDynamicNode    = targetNode.dynamicEditing ?? !! currentNode.data('dynamic-editing');
			const hasDynamicFields = targetNode.dynamicFields ?? !! currentNode.data('dynamic-fields');
			const isRootNode       = currentNode.parent().is( FLBuilder._contentClass );

			if ( ( isDynamicNode || isGlobalNode ) && ( ( isTemplate && ! isRootNode ) || ! isTemplate ) ) {
				let globalData = {
					nodeId: targetNode.nodeId,
					nodeType,
					isNewModule,
					templateUrl,
				};

				if ( nodeType === 'module' ) {
					globalData = {
						...globalData,
						parentId: parentNodeId,
						type: moduleType,
						dynamicFields: hasDynamicFields,
					}
				}

				FLBuilderDynamicGlobal.handleGlobalNodeDisplay( globalData );

			} else {

				const actions = FL.Builder.getActions();
				actions.openSettings( targetNode.nodeId );

			}
		},

		/**
		 * Shows the settings lightbox and loads the row settings
		 * when the row settings button is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _rowSettingsClicked
		 */
		_rowClicked: function( e )
		{
			var row = $( this ).closest( '.fl-row' );

			// Markup in a module's content can carry .fl-row without being a row —
			// see FLBuilder._isLayoutNode. Returning without stopping the event
			// lets it bubble to the real node containing it. (#5558)
			if ( ! FLBuilder._isLayoutNode( row ) ) {
				return;
			}

			var nodeId = row.attr( 'data-node' );
			var claimingId = FLBuilder._findOverlayClaimingClick( e, nodeId );

			FL.Builder.getActions().openSettings( claimingId || nodeId );

			e.stopPropagation();
			e.preventDefault();
		},

		/**
		 * Show settings for a row node
		 *
		 * @since 2.?
		 * @access private
		 * @method _showRowSettings
		 */
		_showRowSettings: function( nodeId, global )
		{
			let win = null;

			// If we're on a global row template page
			if ( global && 'row' != FLBuilderConfig.userTemplateType ) {
				FLBuilderDynamicGlobal.handleGlobalNodeDisplay( { nodeId, nodeType: 'row' } );
			} else {
				const rowNode           = $( '.fl-node-' + nodeId );
				const dynamicEditing    = !! $( rowNode ).data('dynamic-editing');
				const isTemplateEditing = $( rowNode ).closest('.fl-builder-content-editing').is('.fl-builder-template');
				const rootNode          = $( rowNode ).closest( '[data-template-url]' );
				let   rootNodeEditing   = false;
				let   badges            = [];

				if ( isTemplateEditing && rootNode ) {
					rootNodeEditing = !! rootNode.data('dynamic-editing');
				}

				if ( global ) {
					badges = ( dynamicEditing || rootNodeEditing ) ? [ FLBuilderStrings.componentBadge ] : [ FLBuilderStrings.global ];
				}

				FLBuilderSettingsForms.render( {
					id        : 'row',
					global    : global,
					dynamicEditing : dynamicEditing,
					rootNodeEditing: rootNodeEditing,
					nodeId    : nodeId,
					className : 'fl-builder-row-settings',
					attrs     : 'data-node="' + nodeId + '"',
					buttons   : ! global && ! FLBuilderConfig.lite && ! FLBuilderConfig.simpleUi ? ['save-as'] : [],
					badges    : badges,
					settings  : FLBuilderSettingsConfig.nodes[ nodeId ],
					preview	  : {
						type: 'row'
					}
				}, function() {
					$( '#fl-field-width select', window.parent.document ).on( 'change', FLBuilder._rowWidthChanged );
					$( '#fl-field-content_width select', window.parent.document ).on( 'change', FLBuilder._rowWidthChanged );
					$( '#fl-field-aspect_ratio input', window.parent.document ).on( 'input', FLBuilder._rowToggleContentAlignment );
				} );
			}
		},

		/**
		 * Shows the settings lightbox and loads the column settings
		 * when the column settings button is clicked.
		 *
		 * @since 1.1.9
		 * @access private
		 * @method _colSettingsClicked
		 * @param {Object} e The event object.
		 */
		_colClicked: function(e)
		{
			const col            = $( this );

			// Same guard as _rowClicked, for .fl-col. (#5558)
			if ( ! FLBuilder._isLayoutNode( col ) ) {
				return;
			}

			const id             = col.attr( 'data-node' );
			const claimingId     = FLBuilder._findOverlayClaimingClick( e, id );

			// Redirect overhang clicks to the overlay that owns the clicked point.
			if ( claimingId ) {
				FL.Builder.getActions().openSettings( claimingId );
				e.stopPropagation();
				e.preventDefault();
				return;
			}

			const global         = col.hasClass( 'fl-node-global' );
			const parentGlobal   = col.parents( '.fl-node-global' ).length > 0;

			if ( FLBuilder._colResizing ) {
				return;
			} else if ( global && ! FLBuilderConfig.userCanEditGlobalTemplates ) {
				return;
			} else if ( global && parentGlobal && ! FLBuilderConfig.userTemplateType ) {
				return;
			}

			const actions = FL.Builder.getActions();
			actions.openSettings( id )

			e.stopPropagation();
			e.preventDefault();
		},

		/**
		 * Show Column Settings Form
		 *
		 * @since 2.?
		 * @access private
		 * @method _showColSettings
		 */
		_showColSettings: function( nodeId, global, isNodeTemplate ) {

			if ( global && isNodeTemplate && 'row' !== FLBuilderConfig.userTemplateType ) {
				FLBuilderDynamicGlobal.handleGlobalNodeDisplay( { nodeId, nodeType: 'col' } );
			}
			else {
				const colNode           = $( '.fl-col[data-node="' + nodeId + '"]' );
				const isColTemplate       = !! $( colNode ).data( 'template-url' );
				const isColDynamicEditing = !! $( colNode ).data( 'dynamic-editing' );
				const isTemplateEditing = $( colNode ).closest( '.fl-builder-content-editing' ).is( '.fl-builder-template' );
				const rootNode          = $( colNode ).closest( '[data-template-url]' );
				let   rootNodeEditing   = false;
				let   badges            = [];

				if ( isTemplateEditing && rootNode ) {
					rootNodeEditing = !! rootNode.data( 'dynamic-editing' );
				}

				let   isDynamicEditing = false;
				if ( isTemplateEditing && global ) {
					badges = [ FLBuilderStrings.global ];
					if ( ! isColTemplate ) {
						isDynamicEditing = true;
						badges = [ FLBuilderStrings.componentBadge ];
					} else if ( isColDynamicEditing ) {
						isDynamicEditing = true;
						badges = [ FLBuilderStrings.componentBadge ];
					}
				}

				FLBuilderSettingsForms.render( {
					id        : 'col',
					global    : global,
					dynamicEditing: isDynamicEditing,
					rootNodeEditing : rootNodeEditing,
					nodeId    : nodeId,
					className : 'fl-builder-col-settings',
					attrs     : 'data-node="' + nodeId + '"',
					buttons   : ! global && ! FLBuilderConfig.lite && ! FLBuilderConfig.simpleUi ? ['save-as'] : [],
					badges    : badges,
					settings  : FLBuilderSettingsConfig.nodes[ nodeId ],
					preview   : {
						type: 'col'
					}
				}, function() {
					var col = $('.fl-col.fl-node-' + nodeId )
					if ( col.siblings( '.fl-col' ).length === 0  ) {
						$( '#fl-field-equal_height, #fl-field-content_alignment', window.parent.document ).hide();
					}
				} );
			}
		},

		/**
		 * Shows the settings lightbox and loads the module settings
		 * when the module settings button is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _moduleSettingsClicked
		 * @param {Object} e The event object.
		 */
		_moduleClicked: function(e)
		{
			// Ignore programmatic .click() calls (e.g. from block JS) while
			// still allowing real user clicks and jQuery .trigger() events.
			// Contain the event before bailing — without this the untrusted
			// click keeps propagating and its default action fires (e.g. a menu
			// link navigating), which can cascade into a browser-locking loop.
			if ( e.originalEvent && ! e.originalEvent.isTrusted ) {
				e.stopPropagation();
				e.preventDefault();
				return;
			}

			const module         = $(this).closest( '.fl-module' );

			// Same guard as _rowClicked, for .fl-module — the reported case: a
			// <div class="fl-module"> inside an HTML module's content, or markup
			// copied straight off a rendered page (which carries data-node but
			// never data-type). Either way it isn't a real module, so
			// _showModuleSettings would look up an undefined module config and
			// throw on config.assets. Returning here lets the click bubble to
			// the HTML module that holds it, which is the module the user meant
			// to open. (#5558, #5597)
			if ( ! FLBuilder._isModuleNode( module ) ) {
				return;
			}

			// For modules acting as popovers, stop propagation to prevent
			// parent modules from handling the click. The close button is
			// protected by stopImmediatePropagation in the popup builder.
			if ( module[0].hasAttribute( 'popover' ) ) {
				e.stopPropagation();
			}

			const nodeId         = module.attr( 'data-node' );
			const claimingId     = FLBuilder._findOverlayClaimingClick( e, nodeId );

			// Redirect overhang clicks to the overlay that owns the clicked point.
			if ( claimingId ) {
				FL.Builder.getActions().openSettings( claimingId );
				e.stopPropagation();
				e.preventDefault();
				return;
			}

			const parentId       = module.data( 'parent' );
			const type           = module.data( 'type' );
			const global         = module.hasClass( 'fl-node-global' );
			const parentGlobal   = module.parents( '.fl-node-global' ).length > 0;
			const settings       = $( `.fl-builder-settings[data-node="${ nodeId }"]` );
			const editingNode    = $( module ).closest( '.fl-builder-content-editing' );

			const parentTemplate = module.parents( '[data-template-url]' ).first();
			const isParentTemplateNested = parentTemplate.parents( '[data-template-url]' ).first().length;

			const templateType = FLBuilderConfig.userTemplateType;
			const isNodeTemplate = templateType && 'layout' !== templateType;
			const isLayout = ! templateType || 'layout' === templateType;

			if ( FLBuilder._colResizing ) {
				return;
			} if ( global && ! FLBuilderConfig.userCanEditGlobalTemplates ) {
				return;
			} else if ( isNodeTemplate && global && isParentTemplateNested ) {
				return;
			} else if ( isLayout && global && parentGlobal ) {
				return;
			}

			e.stopPropagation();
			e.preventDefault();

			if ( FLBuilderConfig.postType === 'fl-builder-template' ) {
				const templateNode = module.closest( '[data-template-url]' );

				if ( editingNode.is( '.fl-builder-module-template' ) ) {
					// Check if this template a Box module. 
					if ( 'box' === editingNode.children().first().data( 'type' ) ) {
						FLBuilder._showModuleSettingsOfTemplateNode( templateNode, module );
					} else {
						FLBuilder._showModuleSettings( { type,	nodeId, parent: parentId, global } );
					}
				} else {
					FLBuilder._showModuleSettingsOfTemplateNode( templateNode, module );
				}

			} else if ( ! global && ! settings.length ) {
				FLBuilder._showModuleSettings( { type,	nodeId, parent: parentId, global } );
			} else {
				FLBuilder.showNodeSettings( { nodeId: nodeId } );
			}
		},
        
        /**
		 * Shows the lightbox and loads the settings for a module.
		 *
		 * @since 1.0
		 * @access private
		 * @method _showModuleSettings
		 * @param {Object} data
		 * @param {Function} callback
		 */
		_showModuleSettings: function( data, callback )
		{
			if ( ! FLBuilderSettingsConfig.modules ) {
				return;
			}

			// Guards every caller of this function, not just the ones that read
			// data.type off a DOM element (see _isModuleNode in fl-builder.js) —
			// an unregistered or undefined type here would otherwise throw below
			// on config.assets. (#5597)
			if ( ! FLBuilderSettingsConfig.modules[ data.type ] ) {
				console.error( 'FLBuilder._showModuleSettings: no module config for type "' + data.type + '"' );
				return;
			}

			var config   = FLBuilderSettingsConfig.modules[ data.type ],
				settings = data.settings ? data.settings : FLBuilderSettingsConfig.nodes[ data.nodeId ],
				head 	 = $( 'head', window.parent.document ),
				module   = $( '.fl-module[data-node="' + data.nodeId + '"]' ),
				layout   = null;

			let   dynamicFields    = data.dynamicFields;
			let   showFullSettings = true;
			let   badges           = [];

			const isTemplateEditing = FLBuilderConfig.postType === 'fl-builder-template';
			const rootNode          = $( module ).closest( '[data-template-url]' );
			const isModuleTemplate       = !! $( module ).data( 'template-url' );
			const isModuleDynamicEditing = !! $(module).data( 'dynamic-editing' );
			const rootNodeEditing   = isTemplateEditing && rootNode.length;
			const isRootNodeDynamic = !! rootNode.data( 'dynamic-editing' );

			let isDynamicEditing   = isModuleDynamicEditing;
			let openGlobalTemplate = false;

			if ( data.global ) {
				if ( isTemplateEditing ) {
					badges = [ FLBuilderStrings.global ];
					if ( isRootNodeDynamic && ( ! isModuleTemplate || isModuleDynamicEditing ) ) {
						isDynamicEditing = true;
						badges = [ FLBuilderStrings.componentBadge ];
					} else if ( data.isNewModule && data.dynamic ) {
						isDynamicEditing = true;
						badges = [ FLBuilderStrings.componentBadge ];
					}
				} else if ( isDynamicEditing ) {
					showFullSettings = true;
				} else {
					showFullSettings = false;
					openGlobalTemplate = ! FLBuilderConfig.userTemplateType && module.attr( 'data-template-node' ) && module.attr( 'data-template-url' )
				}
			}

			if ( showFullSettings ) {

				// Add settings CSS and JS.
				if ( -1 === $.inArray( data.type, FLBuilder._loadedModuleAssets ) ) {
					if ( '' !== config.assets.css ) {
						head.append( config.assets.css );
					}
					if ( '' !== config.assets.js ) {
						head.append( config.assets.js );
					}
					FLBuilder._loadedModuleAssets.push( data.type );
				}

				// Render the form.
				FLBuilderSettingsForms.render( {
					type	  : 'module',
					global    : data.global,
					dynamicEditing: isDynamicEditing,
					dynamicFields : dynamicFields,
					rootNodeEditing: rootNodeEditing,
					notice    : data.notice,
					id        : data.type,
					nodeId    : data.nodeId,
					className : 'fl-builder-module-settings fl-builder-' + data.type + '-settings',
					attrs     : 'data-node="' + data.nodeId + '" data-parent="' + data.parentId + '" data-type="' + data.type + '"',
					buttons   : ! data.global && ! FLBuilderConfig.lite && ! FLBuilderConfig.simpleUi ? ['save-as'] : [],
					badges    : badges,
					settings  : settings,
					legacy    : data.legacy,
					helper    : FLBuilder._moduleHelpers[ data.type ],
					rules     : FLBuilder._moduleHelpers[ data.type ] ? FLBuilder._moduleHelpers[ data.type ].rules : null,
					messages  : FLBuilder._moduleHelpers[ data.type ] ? FLBuilder._moduleHelpers[ data.type ].messages : null,
					hide      : ( ! FLBuilderConfig.userCanEditGlobalTemplates && data.global ) ? true : false,
					lightbox  : data.lightbox ? data.lightbox : null,
					preview   : {
						type     : 'module',
						layout   : data.layout,
						callback : function() {
							FLBuilder._initModuleMarginPlaceholders();

							// This callback also runs when opening settings for an
							// existing module, so only fire didAddModule for new ones.
							// Otherwise a false module_added history state is saved.
							if ( data.isNewModule ) {
								FLBuilder.triggerHook( 'didAddModule', {
									nodeId: data.nodeId,
									moduleType: settings ? settings.type : data.type,
									settings: settings,
									newNodes: data.newNodes,
									updatedNodes: data.updatedNodes,
								} );
							}
						}
					}
				}, callback );

			} else if ( openGlobalTemplate ) {
				
				if ( FLBuilderConfig.userCanEditGlobalTemplates ) {
					win = window.parent.open( module.attr( 'data-template-url' ) );
					win.FLBuilderGlobalNodeId = data.nodeId;
				}

			} else if ( ! FLBuilderConfig.userTemplateType && ! data.isNewModule ) {

				const globalData = {
					nodeType: 'module',
					dynamicFields,
					...data,
				};

				FLBuilderDynamicGlobal.handleGlobalNodeDisplay( globalData );

			}

		},

        /**
         * Shows the module settings for either a static node or a template node.
         */
        _showModuleSettingsForNodeOrTemplate: function( id, type, settings, parent, global, nodeData ) {
            const module                  = $( `.fl-node-${ id }` );
            const isModuleTemplateEditing = !! module.closest( '.fl-builder-content-editing' ).is( '.fl-builder-module-template' );
            const moduleType              = settings ? settings.type : ( nodeData.moduleType ?? module.data( 'type' ) );
            const targetNode = {
                nodeId: id,
                parentNodeId: parent,
                nodeType: type,
                moduleType: moduleType,
                templateUrl: nodeData.template_url,
                global: global,
                dynamicEditing: nodeData.dynamic,
            };

            if ( FLBuilderConfig.postType === 'fl-builder-template' ) {

                if ( isModuleTemplateEditing ) {

                    FLBuilder._showModuleSettings( {
                        nodeId   : id,
                        parentId : parent,
                        type     : moduleType,
                        global   : global,
                        dynamic  : nodeData.dynamic,
                    } )

                } else {
                    const templateNode = module.closest('[data-template-url]');
                    FLBuilder._showModuleSettingsOfTemplateNode( templateNode, module );
                }

            } else if ( ! targetNode.global && ! targetNode.dynamicEditing ) {

                FLBuilder._showModuleSettings( {
                    nodeId: id,
                    parentId: parent,
                    type: moduleType,
                } )

            } else {
                
                const globalData = {
                    nodeId        : id,
                    parentId      : parent,
                    global        : targetNode.global,
                    dynamicFields : targetNode.dynamicEditing,
                    nodeType      : type,
                    type          : targetNode.moduleType,
                };

                FLBuilderDynamicGlobal.handleGlobalNodeDisplay( globalData );

            }
        },

		/**
		 * Shows the settings lightbox of a module that's nested within a template.
		 * 
		 * @since 2.10
		 * @access private
		 * @method _showModuleSettingsOfTemplateNode
		 * @param {Object} templateNode The root template node object.
		 * @param {Object} module The module object.
		 */
		_showModuleSettingsOfTemplateNode: function( templateNode, module ) {

			if ( FLBuilderConfig.postType !== 'fl-builder-template' ) {
				return;
			}

			if ( ! templateNode || ! module ) {
				return;
			}

			if ( module.data( 'template-url' ) ) {

				FLBuilder.showNodeSettings( {
					nodeId : module.data( 'node' )
				} );

			} else {

				FLBuilder._showModuleSettings( {
					nodeId   : module.data( 'node' ),
					parentId : module.data( 'parent' ),
					type     : module.data( 'type' ),
					global   : templateNode.hasClass( 'fl-node-global' ),
					dynamic  : templateNode.data( 'dynamic-editing' ),
				} );

			}
		},
    } );

} )( jQuery );