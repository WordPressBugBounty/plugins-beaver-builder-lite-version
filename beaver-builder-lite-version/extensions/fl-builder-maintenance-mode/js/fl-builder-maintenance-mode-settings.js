( function( $ ) {
	'use strict';

	var nonce  = null;
	var saving = false;

	// Last stored Start/End, posted in place of a half-filled field, which reads as ''.
	var savedDates = { start: '', end: '' };

	function init() {
		nonce      = $( '#fl-maintenance-nonce' ).val();
		savedDates = { start: $( '#fl-maintenance-start' ).val(), end: $( '#fl-maintenance-end' ).val() };

		$( '.fl-maintenance-enabled-toggle' ).on( 'change', onEnabledToggleChange );
		$( '#fl-maintenance-layout-id' ).on( 'change', save );
		$( '.fl-maintenance-bypass-role' ).on( 'change', save );
		$( '.fl-maintenance-503-toggle' ).on( 'change', save );
		$( '.fl-maintenance-hide-chrome-toggle' ).on( 'change', save );
		$( '.fl-maintenance-schedule-toggle' ).on( 'change', onScheduleToggleChange );
		// A half-filled datetime-local fires no change or input event, only blur.
		$( '#fl-maintenance-start, #fl-maintenance-end' ).on( 'change', onDateChange ).on( 'blur', warnIfDateIncomplete );

		$( '.fl-maintenance-copy-link' ).on( 'click', onCopyLink );
		$( '.fl-maintenance-regenerate-link' ).on( 'click', onRegenerateLink );

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
	 * Returns the field's value, or the last stored one while it holds a partial
	 * date or time, so saving another control cannot wipe a stored date.
	 */
	function dateValue( name ) {
		var input = document.getElementById( 'fl-maintenance-' + name );

		return input.validity.badInput ? savedDates[ name ] : input.value;
	}

	/**
	 * A half-filled date has nothing new to save; blur explains why.
	 */
	function onDateChange() {
		if ( this.validity.badInput ) {
			return;
		}

		save();
	}

	function warnIfDateIncomplete() {
		if ( ! this.validity.badInput ) {
			return;
		}

		new Notify( {
			status:      'error',
			title:       'Enter both a date and a time',
			autoclose:   true,
			autotimeout: 3000,
			distance:    20,
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

		var data = {
			action:           'fl_maintenance_save',
			_wpnonce:         nonce,
			enabled:          $( '.fl-maintenance-enabled-toggle' ).is( ':checked' ) ? '1' : '',
			status_503:       $( '.fl-maintenance-503-toggle' ).is( ':checked' ) ? '1' : '',
			hide_chrome:      $( '.fl-maintenance-hide-chrome-toggle' ).is( ':checked' ) ? '1' : '',
			layout_id:        $( '#fl-maintenance-layout-id' ).val(),
			bypass_roles:     bypassRoles,
			schedule_enabled: $( '.fl-maintenance-schedule-toggle' ).is( ':checked' ) ? '1' : '',
			start:            dateValue( 'start' ),
			end:              dateValue( 'end' ),
		};

		$.post( ajaxurl, data, function( response ) {
			$label.removeClass( 'fl-toggle-switch-saving' );

			if ( response.success ) {
				savedDates = { start: data.start, end: data.end };
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
	 * Saves, then reloads the page so server-rendered state that depends on the
	 * enabled flag — the admin-bar "Maintenance Mode" node, the badge, and the
	 * preview link — all resync, rather than patching one badge client-side.
	 */
	function onEnabledToggleChange() {
		save( function() {
			// Brief pause so the "Saved" toast is visible before the reload tears
			// the page down; the reload then resyncs all server-rendered state.
			setTimeout( function() {
				window.location.reload();
			}, 800 );
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

	/**
	 * Copies the preview link to the clipboard.
	 */
	function onCopyLink() {
		var url = $( '#fl-maintenance-preview-url' ).val();

		function notify( status, title ) {
			new Notify( {
				status:      status,
				title:       title,
				autoclose:   true,
				autotimeout: 1500,
				distance:    20,
			} );
		}

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( url ).then( function() {
				notify( 'success', 'Copied' );
			}, function() {
				notify( 'error', 'Copy failed' );
			} );
		} else {
			// Fallback for insecure contexts where the async Clipboard API is unavailable.
			$( '#fl-maintenance-preview-url' ).trigger( 'select' );
			document.execCommand( 'copy' );
			notify( 'success', 'Copied' );
		}
	}

	/**
	 * Regenerates the secret token, swapping in the new preview link and
	 * invalidating any link shared before.
	 */
	function onRegenerateLink() {
		var $btn = $( this );

		$btn.prop( 'disabled', true );

		$.post( ajaxurl, {
			action:   'fl_maintenance_regenerate_key',
			_wpnonce: nonce,
		}, function( response ) {
			$btn.prop( 'disabled', false );

			if ( response.success && response.data && response.data.url ) {
				// The revoke-only row has no link field to refresh, so only the old link is gone.
				var $url = $( '#fl-maintenance-preview-url' ).val( response.data.url );
				new Notify( {
					status:      'success',
					title:       $url.length ? 'New link generated' : 'Previous link revoked',
					autoclose:   true,
					autotimeout: 1500,
					distance:    20,
				} );
			} else {
				new Notify( {
					status:      'error',
					title:       'Error',
					autoclose:   true,
					autotimeout: 3000,
					distance:    20,
				} );
			}
		} ).fail( function() {
			$btn.prop( 'disabled', false );
			new Notify( {
				status:      'error',
				title:       'Error',
				autoclose:   true,
				autotimeout: 3000,
				distance:    20,
			} );
		} );
	}

	$( init );

} )( jQuery );
