<script type="text/html" id="tmpl-fl-builder-field">
	<#
		var dynamicFields = [];
		let dynamicFieldIcons = '';
		let dynamicEditingTitle = FLBuilderStrings.enableComponentEditing;
		const { source, isDataSource } = data.dynamicOptions;

		if ( data.dynamicEditing ) {
			dynamicFieldIcons = 'dashicons-admin-plugins';

			if ( 'object' === typeof data.settings.dynamic_fields && data.settings.dynamic_fields?.fields ) {
				dynamicFields = data.settings.dynamic_fields.fields;

				if ( dynamicFields.includes( data.rootName ) ) {
					dynamicFieldIcons += ' fl-dynamic-node-field-enabled';
					dynamicEditingTitle = FLBuilderStrings.disableComponentEditing;
				}
			}

			if ( data.field.type === 'form' ) {
				dynamicFieldIcons = '';
			} else if ( data.field.type === 'button' && data.name === 'service_connect_button' ) {
				dynamicFieldIcons = '';
			} else if ( data.field.type === 'select' && data.name === 'service' ) {
				dynamicFieldIcons = '';
			} else if ( source === 'legacy' && ! isDataSource ) {
				dynamicFieldIcons = '';
			}

		}
	#>
	<# if ( ! data.field.label ) { #>
	<td class="fl-field-control" colspan="2">
		<# if ( dynamicFieldIcons && 'fl-builder-template' === FLBuilderConfig.postType && 'form' !== data.field.type ) { #>
			<i class="fl-dynamic-node-field dashicons fl-tip {{dynamicFieldIcons}}" title="{{dynamicEditingTitle}}" data-target-field="{{data.name}}" data-target-field-type="{{data.field.type}}"></i>
		<# } #>
	<# } else { #>
	<th class="fl-field-label">
		<label for="{{data.name}}">
			<#
			var targetFieldName = data.rootName;

			if ( data.rootName !== data.name ) {
				targetFieldName = data.name;
			}
			#>
			<# if ( 'button' === data.field.type ) { #>
			&nbsp;
			<# } else { #>
				{{{data.field.label}}}
				<# if ( undefined !== data.index ) { #>
					<span class="fl-builder-field-index">{{ data.index + 1 }}</span>
				<# } #>
			<# } #>
				<# if ( dynamicFieldIcons && 'fl-builder-template' === FLBuilderConfig.postType && 'form' !== data.field.type ) { #>
				<i class="fl-dynamic-node-field dashicons fl-tip {{dynamicFieldIcons}}" title="{{dynamicEditingTitle}}" data-target-field="{{targetFieldName}}" data-target-field-type="{{data.field.type}}" data-target-field-index="{{ ( undefined !== data.index ) ? data.index : '' }}"></i>
				<# } #>
			<# if ( data.responsive ) { #>
			<i class="fl-field-responsive-toggle dashicons dashicons-desktop" data-mode="default"></i>
			<# } #>

			<# if ( data.field.help ) { #>
			<span class="fl-help-tooltip">
				<span class="fl-help-tooltip-icon">
					<svg width="12" height="12">
						<use href="#fl-question-mark" />
					</svg>
				</span>
				<span class="fl-help-tooltip-text">{{{data.field.help}}}</span>
			</span>
			<# } #>

		</label>
	</th>
	<td class="fl-field-control">
	<# } #>
	<div class="fl-field-control-wrapper">

		<# if ( data.responsive ) { #>
		<i class="fl-field-responsive-toggle dashicons dashicons-desktop" data-mode="default"></i>
		<# } #>
		<# var devices = [ 'default', 'large', 'medium', 'responsive' ];

		for ( var i = 0; i < devices.length; i++ ) {

			data.device = devices[ i ];

			if ( 'default' !== devices[ i ] && ! data.responsive ) {
				continue;
			}

			if ( data.responsive ) {
				data.name  = 'default' === devices[ i ] ? data.rootName : data.rootName + '_' + devices[ i ];
				data.value = data.settings[ data.name ] ? data.settings[ data.name ] : '';

				if ( 'object' === typeof data.responsive ) {
					for ( var key in data.responsive ) {
						if ( 'object' === typeof data.responsive[ key ] && undefined !== data.responsive[ key ][ devices[ i ] ] ) {
							data.field[ key ] = data.responsive[ key ][ devices[ i ] ];
						}
					}
				}
			#>
			<div class="fl-field-responsive-setting fl-field-responsive-setting-{{devices[ i ]}}" data-device="{{devices[ i ]}}">
			<# } #>
			<# if ( data.template.length ) {
				var template = wp.template( 'fl-builder-field-' + data.field.type ),
					before   = data.field.html_before ? data.field.html_before : '',
					after    = data.field.html_after ? data.field.html_after : '';

				// Allow module helper to filter the field template function
				if ( data.node ) {
					const helper = FLBuilder._moduleHelpers[data.node.type];
					if ( helper && 'filterFieldTemplate' in helper ) {
						template = helper.filterFieldTemplate( data.field, template )
					}
				}

				const field = template( data );
			#>
			{{{before}}}{{{field}}}{{{after}}}
			<# } else {
				var name  = data.name.replace( '[]', '' );
			#>
			<div class="fl-legacy-field" data-field="{{name}}"></div>
			<# } #>
			<# if ( data.responsive ) { #>
			</div>
			<# } #>
		<# } #>
		<# if ( data.field.description ) { #>
		<span class="fl-field-description">{{{data.field.description}}}</span>
		<# } #>
	</div>
	</td>
</script>
