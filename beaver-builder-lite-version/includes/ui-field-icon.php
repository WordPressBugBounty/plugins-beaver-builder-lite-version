<#

var field = data.field;
var className = 'fl-icon-field fl-builder-custom-field';

if ( '' === data.value ) {
	className += ' fl-icon-empty';
}
if ( field.className ) {
	className += ' ' + field.className;
}

var show = '';

if ( field.show ) {
	show = "data-show='" + JSON.stringify( field.show ) + "'";
}

#>
<# if ( ! data.field.show_extra_classes ) { #>
<div class="{{className}}">
	<a class="fl-icon-select" href="javascript:void(0);" onclick="return false;"><?php _e( 'Select Icon', 'fl-builder' ); ?></a>
	<div class="fl-icon-preview">
		<i class="{{{data.value}}}" data-icon="{{{data.value}}}"></i>
		<a class="fl-icon-replace" href="javascript:void(0);" onclick="return false;"><?php _e( 'Replace', 'fl-builder' ); ?></a>
		<# if ( data.field.show_remove ) { #>
		<a class="fl-icon-remove" href="javascript:void(0);" onclick="return false;"><?php _e( 'Remove', 'fl-builder' ); ?></a>
		<# } #>
	</div>
	<input name="{{data.name}}" type="hidden" value="{{{data.value}}}" {{{show}}} />
</div>
<# } else {
	var extraValue = data.settings[ data.name + '_extra' ] || '';
	var extraClasses = [];
	if ( 'string' === typeof extraValue && extraValue.length > 0 ) {
		extraClasses = extraValue.split( /\s+/ ).filter( function( c ) { return c.length > 0; } );
	}
	var suggestions = [
		{ group: 'Animation', items: [ 'fa-beat', 'fa-bounce', 'fa-fade', 'fa-beat-fade', 'fa-flip', 'fa-shake', 'fa-spin', 'fa-spin-pulse' ] }
	];
	if ( data.field.fa_pro ) {
		suggestions.push( { group: 'Style (PRO)', items: [ 'fa-thin', 'fa-light', 'fa-regular' ] } );
	}
#>
<div class="fl-compound-field fl-icon-compound-field">
	<div class="fl-compound-field-section fl-compound-field-section-visible fl-icon-compound-field-section-main">
		<div class="fl-compound-field-row">
			<div class="fl-compound-field-setting">
				<div class="{{className}}">
					<a class="fl-icon-select" href="javascript:void(0);" onclick="return false;"><?php _e( 'Select Icon', 'fl-builder' ); ?></a>
					<div class="fl-icon-preview">
						<i class="{{{data.value}}}" data-icon="{{{data.value}}}"></i>
						<a class="fl-icon-replace" href="javascript:void(0);" onclick="return false;"><?php _e( 'Replace', 'fl-builder' ); ?></a>
						<# if ( data.field.show_remove ) { #>
						<a class="fl-icon-remove" href="javascript:void(0);" onclick="return false;"><?php _e( 'Remove', 'fl-builder' ); ?></a>
						<# } #>
					</div>
					<input name="{{data.name}}" type="hidden" value="{{{data.value}}}" {{{show}}} />
				</div>
			</div>
		</div>
	</div>
	<div class="fl-compound-field-section fl-icon-compound-field-section-extra">
		<div class="fl-compound-field-section-toggle">
			<i class="dashicons dashicons-arrow-right-alt2"></i>
			<?php _e( 'Extra Classes', 'fl-builder' ); ?>
			<span class="fl-help-tooltip">
				<span class="fl-help-tooltip-icon">
					<svg width="12" height="12">
						<use href="#fl-question-mark" />
					</svg>
				</span>
				<span class="fl-help-tooltip-text"><?php esc_html_e( 'Popular Font Awesome classes are available as presets in the dropdown. You can also type any class name and press Enter or Space to add it.', 'fl-builder' ); ?></span>
			</span>
		</div>
		<div class="fl-compound-field-row">
			<div class="fl-compound-field-setting">
				<div class="fl-icon-classes-field" data-suggestions='<# print( JSON.stringify( suggestions ) ); #>'>
					<input type="hidden" name="{{data.name}}_extra" value="{{extraValue}}" class="fl-icon-classes-value" />
					<div class="fl-icon-classes-tags">
						<# for ( var i = 0; i < extraClasses.length; i++ ) { #>
						<span class="fl-icon-classes-tag">
							<span class="fl-icon-classes-tag-text">{{extraClasses[i]}}</span>
							<button type="button" class="fl-icon-classes-tag-remove" aria-label="<?php esc_attr_e( 'Remove', 'fl-builder' ); ?>">&times;</button>
						</span>
						<# } #>
						<input type="text" class="fl-icon-classes-input" placeholder="<?php esc_attr_e( 'Type a class name...', 'fl-builder' ); ?>" autocomplete="off" />
					</div>
					<div class="fl-icon-classes-suggestions"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<# } #>
