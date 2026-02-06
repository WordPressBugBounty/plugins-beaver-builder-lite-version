(function($){

	/**
	 * Dynamic Node Class
	 *
	 * @since 2.10
	 * @class FLBuilderDynamicGlobal
	 */
	FLBuilderDynamicGlobal = {
		/**
		 * Initialization.
		 *
		 * @since 2.10
		 * @access private
		 * @method _init
		 */
		_init: function()
		{
			FLBuilder.addHook( 'settings-form-init', FLBuilderDynamicGlobal._initDynamicNodeFields );
		},

		_initDynamicNodeFields: function() {
			const form = $( '.fl-builder-settings:visible', window.parent.document );
			form.on( 'click', '.fl-dynamic-node-field', FLBuilderDynamicGlobal._toggleDynamicField.bind( FLBuilderDynamicGlobal ) );
		},

		_toggleDynamicField: function(e) {
			const form                  = $( '.fl-builder-settings:visible', window.parent.document );
			const dynamicFields         = $( form ).find('.active-dynamic-fields');
			const currentDynamicFields  = dynamicFields.length ? dynamicFields.val().toString() : '';
			const curFormSettingsField  = $( form ).find('.fl-builder-settings-json');
			const curFormSettings       = curFormSettingsField.length ? curFormSettingsField.val().toString() : '';
			const targetEl              = e.target;

			const targetField = {
				name :         $(targetEl).data('target-field'),
				type :         $(targetEl).data('target-field-type'),
				container :    $(targetEl).closest('tr'),
				isResponsive : $(targetEl).next().hasClass('fl-field-responsive-toggle'),
			};

			const dynamicEditingEnabled = $(targetEl).hasClass('fl-dynamic-node-field-enabled');

			let   activeDynamicFields   = [];
			let   targetTitle           = '';

			if ( dynamicEditingEnabled ) {
				$(targetEl).removeClass('fl-dynamic-node-field-enabled');
				targetTitle = FLBuilderStrings.enableComponentEditing;
				activeDynamicFields = this._deleteDynamicField( targetField, currentDynamicFields );
			} else {
				$(targetEl).addClass('fl-dynamic-node-field-enabled');
				targetTitle = FLBuilderStrings.disableComponentEditing;
				activeDynamicFields = this._addDynamicField( targetField, currentDynamicFields );
			}

			$( targetEl ).removeClass('fl-has-tip');
			$( targetEl ).removeAttr( 'data-title' );
			$( targetEl ).attr( 'title', targetTitle );

			FLBuilder._initTipTips(true);

			const formNode = form.data('node');
			const formId   = form.data('form-id');
			let dynamicFieldsData = {
				nodeId: formNode,
				nodeType: formId,
				fields: [ ... new Set( activeDynamicFields ) ]
			};

			form.find('.active-dynamic-fields').val( JSON.stringify( dynamicFieldsData ) );
		},

		/**
		 * Add target field name and related names to the active list of fields.
		 *
		 * @since 2.10
		 * @access private
		 * @method _addDynamicField
		 * @param {String} targetField The name of the target field.
		 * @param {Array} currentDynamicFields Namelist of fields actively selected.
		 * @returns Array
		 */
		_addDynamicField: function( targetField, currentDynamicFields ) {
			let updatedFields = [];
			let fieldList = [];

			try {
				let currentDynamicFieldsJson = JSON.parse( currentDynamicFields );
				let currentFields = currentDynamicFieldsJson.fields;

				fieldList = this._getCompoundFieldNames( targetField );
				updatedFields = [ ...currentFields, ...fieldList ];
			} catch ( e ) {
				if ( currentDynamicFields === '' ) {
					fieldList = this._getCompoundFieldNames( targetField );
					updatedFields = [ ...fieldList ];
				}
			}

			return updatedFields;
		},


		/**
		 * Delete target field from the current active list of fields.
		 *
		 * @since 2.10
		 * @access private
		 * @method _deleteDynamicField
		 * @param {String} targetField The name of the target field.
		 * @param {Array} currentDynamicFields Namelist of fields actively selected.
		 * @returns Array
		 */
		_deleteDynamicField: function( targetField, currentDynamicFields ) {
			let updatedFields = [];

			try {
				let currentDynamicFieldsJson = JSON.parse( currentDynamicFields );
				let currentFields = currentDynamicFieldsJson.fields;
				let targetFieldList = this._getCompoundFieldNames( targetField );

				updatedFields = currentFields.filter( ( target ) => ! targetFieldList.includes( target ) );

			} catch ( e ){
				;
			}

			return updatedFields;
		},

		/**
		 * Get names related to a field to be used in its settings.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getCompoundFieldNames
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getCompoundFieldNames: function ( field ) {
			let nameList = [];
			if ( field.type === 'link' ) {
				nameList = this._getLinkFieldNameList( field );
			} else if ( field.type === 'border' ) {
				nameList = this._getBorderFieldNameList( field );
			} else if ( field.type === 'unit' ) {
				nameList = this._getUnitFieldNameList( field );
			} else if ( field.type === 'dimension' ) {
				nameList = this._getDimensionFieldNameList( field );
			} else if ( field.type === 'photo' ) {
				nameList = this._getPhotoFieldNameList( field );
			} else {
				nameList = this._getFieldNameList( field );
			}

			return nameList;
		},

		/**
		 * General field -- Get related names if responsive is enabled.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getLinkFieldNameList
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getFieldNameList: function( field ) {
			let nameList = [
				field.name,
			];

			if ( field.isResponsive ) {
				nameList = [ ...nameList, ...this._getResponsiveNames( field.name ) ];
			}

			return nameList;
		},

		/**
		 * Link field -- Get related names.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getLinkFieldNameList
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getLinkFieldNameList: function( field ) {
			return [
				field.name,
				field.name + '_target',
				field.name + '_nofollow',
				field.name + '_download',
			];
		},

		/**
		 * Photo field -- Get related names.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getPhotoFieldNameList
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getPhotoFieldNameList: function( field ) {
			let nameList = [
				field.name,
				field.name + '_src',
			];

			if ( ! field.isResponsive ) {
				return nameList;
			}

			nameList = [
				...nameList,
				...this._getResponsiveNames( field.name ).map( ( fieldName ) => ( fieldName ) ),
				...this._getResponsiveNames( field.name ).map( ( fieldName ) => ( fieldName + '_src' ) ),
			];

			return nameList;
		},

		/**
		 * Border field -- Get related names.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getBorderFieldNameList
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getBorderFieldNameList: function( field ) {
			let nameList = [
				field.name,
			];

			if ( field.isResponsive ) {
				nameList = [
					...nameList,
					...this._getResponsiveNames( field.name )
				];
			}

			return nameList;
		},

		/**
		 * Unit field -- Get related names.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getUnitFieldNameList
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getUnitFieldNameList: function( field ) {
			let nameList = [
				field.name,
			];

			if ( field.isResponsive ) {
				nameList = [
					...nameList,
					...this._getResponsiveNames( field.name )
				];
			}

			const units = field.container.find('.fl-unit-field-unit-select select').toArray();
			if ( units.length > 0 ) {
				nameList = [
					...nameList,
					...units.map( ( select ) => $(select).attr('name') )
				];
			}

			return nameList;
		},

		/**
		 * Dimension field -- Get related names.
		 *
		 * @since 2.10
		 * @access private
		 * @method _getDimensionFieldNames
		 * @param {Object} field The target field data object.
		 * @returns Array
		 */
		_getDimensionFieldNameList: function( field ) {
			let nameList = [
				field.name,
			];
			const dimNames = ['top', 'right', 'bottom', 'left'].map( (side) => field.name + '_' + side );

			dimNames.forEach((fieldName) => {
				nameList.push( fieldName );
				if ( field.isResponsive ) {
					nameList = [
						...nameList,
						...this._getResponsiveNames( fieldName )
					];
				}
			});

			// Check if the field unit is enabled.
			const dimUnits = field.container.find('.fl-dimension-field-unit-select select').toArray();
			if ( dimUnits.length >= 4 ) {
				nameList = [
					...nameList,
					...dimUnits.map( ( select ) => $(select).attr('name') )
				];
			}

			return nameList;
		},

        /**
		 * Add screen width/size suffix to the field name.
		 *
		 * @since 2.10
		 * @access public
		 * @method _getResponsiveNames
		 * @param {String} fieldName The name of the field.
		 * @returns Array
		 */
		_getResponsiveNames: function( fieldName ) {
			return ['large', 'medium','responsive'].map( ( size ) => ( fieldName + '_' + size ) );
		},

		/**
		 * Either render dynamic node setting UI or edit the global template.
		 *
		 * @since 2.10
		 * @access public
		 * @method handleGlobalNodeDisplay
		 */
		handleGlobalNodeDisplay: function( nodeData ) {
			const { nodeId, nodeType, isNewModule, showTemplate, global, dynamic, dynamicFields } = nodeData;

			if ( nodeType === 'module' && nodeData.type !== 'box' && ! dynamicFields && FLBuilderConfig.postType !== 'fl-builder-template' ) {
				const notice = FLBuilderStrings.dynamicNodeEmpty.replace( 'TEMPLATE_URL', nodeData.templateUrl );

				FLBuilder._showModuleSettings( {
					nodeId        : nodeId,
					parentId      : nodeData.parentId,
					type          : nodeData.type,
					dynamicFields : dynamicFields,
					global        : true,
					notice        : notice,
				}, function() {
					FLBuilderDynamicGlobal._bindDynamicNodeEditLink( nodeData.nodeId );
				} );

				return;
			}

			FLBuilder.ajax( {
				action: 'get_dynamic_node_tabs',
				node_id: nodeData.nodeId,
			}, function( response ) {
				const config = FLBuilder._jsonParse( response );
				const showTemplate = typeof( nodeData.showTemplate ) !== 'undefined' ? nodeData.showTemplate : true;

				if ( config.dynamicEditing && ! config.isEmpty ) {

					if ( nodeData.layout ) {
						FLBuilder._renderLayout( nodeData.layout );
					}

					FLBuilderDynamicGlobal._showDynamicNodeForm( config, nodeData );
				} else if ( FLBuilderConfig.userCanEditGlobalTemplates && showTemplate ) {

					const node = $( '[data-node="' + nodeData.nodeId + '"]' );
					const win = window.parent.open( node.attr( 'data-template-url' ) );

					win.FLBuilderGlobalNodeId = nodeData.nodeId;

				}

			} );

			const actions = FL.Builder.data.getSystemActions();
			const isPinnedRight = 'right' === FLBuilder.PinnedUI.getPinnedSide();

			if ( isPinnedRight ) {
				actions.hideCurrentPanel();
			}
		},

		_bindDynamicNodeEditLink: function( nodeId ) {
			$( '.fl-dynamic-node-edit-link' ).on( 'click', function( e ) {
				e.preventDefault();
				FLBuilder._lightbox.close();
				const win = window.parent.open( $( this ).attr( 'href' ) );
				win.FLBuilderGlobalNodeId = nodeId;
			} )
		},

		/**
		 * Render Dynamic node settings form UI.
		 *
		 * @since 2.10
		 * @access private
		 * @method _showDynamicNodeForm
		 */
		_showDynamicNodeForm: function ( config, nodeData ) {

			let isNewNode = false;

			if ( nodeData.nodeType === 'module' && nodeData.isNewModule ) {
				isNewNode = true;
			}

			FLBuilderSettingsForms.render( {
				id                  : 'dynamic_node_form',
				title               : config.title,
				nodeId              : nodeData.nodeId,
				nodeType            : nodeData.nodeType,
				isNewNode           : isNewNode,
				tabs                : config.tabs,
				settings            : config.settings,
				notice              : config.notice,
				dynamicNodeSettings : JSON.stringify( config.dynamic_node_settings ),
				type                : 'dynamic',
				className           : `fl-builder-dynamic-${ nodeData.nodeType }-settings`,
				attrs               : 'data-node="' + nodeData.nodeId + '"',
				badges              : [ FLBuilderStrings.componentBadge ],
				preview             : {
					type: nodeData.nodeType,
				}
			}, function() {

				if ( nodeData.isNewModule ) {
					$( '.fl-builder-dynamic-module-settings', window.parent.document ).data( 'new-module', '1' );
				}

				FLBuilderDynamicGlobal._bindDynamicNodeEditLink( nodeData.nodeId );
			} );
		},
	}

	// Run initialization.
	$(function(){
		FLBuilderDynamicGlobal._init();
	});

})(jQuery);
