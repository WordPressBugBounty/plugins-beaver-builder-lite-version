( function( $ ) {
	'use strict';

	var nonce  = null;
	var saving = false;

	function init() {
		nonce = $( '#fl-maintenance-nonce' ).val();

		$( '.fl-maintenance-enabled-toggle' ).on( 'change', onEnabledToggleChange );
		$( '#fl-maintenance-layout-id' ).on( 'change', save );
		$( '.fl-maintenance-bypass-role' ).on( 'change', save );
		$( '.fl-maintenance-503-toggle' ).on( 'change', save );
		$( '.fl-maintenance-schedule-toggle' ).on( 'change', onScheduleToggleChange );
		$( '#fl-maintenance-start, #fl-maintenance-end' ).on( 'change', save );

		$( '.fl-maintenance-info-btn' ).on( 'click keydown', function( e ) {
			if ( e.type === 'keydown' && e.which !== 13 && e.which !== 32 ) {
				return;
			}
			// The info button now lives inside the toggle's <label>, so stop
			// the event from also toggling the checkbox.
			e.preventDefault();
			e.stopPropagation();
			var expanded = $( this ).attr( 'aria-expanded' ) === 'true';
			$( this ).attr( 'aria-expanded', String( ! expanded ) );
			$( '#' + $( this ).attr( 'aria-controls' ) ).prop( 'hidden', expanded );
		} );
	}

	/**
	 * Gathers all current field values and POSTs them to the AJAX save handler.
	 */
	function save( callback ) {
		var $card  = $( '#fl-maintenance-mode-form' );
		var $label = $card.find( '.fl-toggle-switch' ).first();

		var bypassRoles = [];
		$( '.fl-maintenance-bypass-role:checked' ).each( function() {
			bypassRoles.push( $( this ).val() );
		} );

		$label.addClass( 'fl-toggle-switch-saving' );

		$.post( ajaxurl, {
			action:           'fl_maintenance_save',
			_wpnonce:         nonce,
			enabled:          $( '.fl-maintenance-enabled-toggle' ).is( ':checked' ) ? '1' : '',
			status_503:       $( '.fl-maintenance-503-toggle' ).is( ':checked' ) ? '1' : '',
			layout_id:        $( '#fl-maintenance-layout-id' ).val(),
			bypass_roles:     bypassRoles,
			schedule_enabled: $( '.fl-maintenance-schedule-toggle' ).is( ':checked' ) ? '1' : '',
			start:            $( '#fl-maintenance-start' ).val(),
			end:              $( '#fl-maintenance-end' ).val(),
		}, function( response ) {
			$label.removeClass( 'fl-toggle-switch-saving' );

			if ( response.success ) {
				$label.addClass( 'fl-toggle-switch-saved' );
				setTimeout( function() {
					$label.removeClass( 'fl-toggle-switch-saved' );
				}, 1500 );
				new Notify( {
					status:      'success',
					title:       'Saved',
					autoclose:   true,
					autotimeout: 1000,
					distance:    20,
				} );
				if ( typeof callback === 'function' ) {
					callback();
				}
			} else {
				new Notify( {
					status:      'error',
					title:       'Save Error',
					autoclose:   true,
					autotimeout: 3000,
					distance:    20,
				} );
			}
		} ).fail( function() {
			$label.removeClass( 'fl-toggle-switch-saving' );
			new Notify( {
				status:      'error',
				title:       'Save Error',
				autoclose:   true,
				autotimeout: 3000,
				distance:    20,
			} );
		} );
	}

	/**
	 * Saves and updates the main enable/disable badge.
	 */
	function onEnabledToggleChange() {
		var enabled = $( this ).is( ':checked' );
		var $badge  = $( this ).closest( '.fl-tools-card' ).find( '.fl-tools-badge' );

		save( function() {
			if ( enabled ) {
				$badge.removeClass( 'fl-tools-badge-inactive fl-tools-badge-scheduled' ).addClass( 'fl-tools-badge-active' ).text( 'Active' );
			} else {
				$badge.removeClass( 'fl-tools-badge-active fl-tools-badge-scheduled' ).addClass( 'fl-tools-badge-inactive' ).text( 'Inactive' );
			}
		} );
	}

	/**
	 * Saves, shows/hides schedule fields, and updates the schedule badge.
	 */
	function onScheduleToggleChange() {
		var enabled = $( this ).is( ':checked' );
		var $badge  = $( this ).closest( '.fl-tools-card' ).find( '.fl-tools-badge' );
		var $fields = $( '#fl-maintenance-schedule-fields' );

		if ( enabled ) {
			$fields.slideDown( 200 );
		} else {
			$fields.slideUp( 200 );
		}

		save( function() {
			if ( enabled ) {
				$badge.removeClass( 'fl-tools-badge-inactive' ).addClass( 'fl-tools-badge-active' ).text( 'Enabled' );
			} else {
				$badge.removeClass( 'fl-tools-badge-active' ).addClass( 'fl-tools-badge-inactive' ).text( 'Disabled' );
			}
		} );
	}

	$( init );

} )( jQuery );
