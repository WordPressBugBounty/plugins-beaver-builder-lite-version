( function( $ ) {

	/**
	 * @since 2.6
	 * @class FLBuilderGlobalImportExport
	 */
	FLBuilderGlobalImportExport = {

		_settingsUploader: null,
		_snapshots: FLBuilderAdminImportExportConfig.snapshots || {},
		_maxSnapshots: FLBuilderAdminImportExportConfig.maxSnapshots || 5,

		/**
		 * Initializes custom exports for the builder.
		 *
		 * @since 1.8
		 * @access private
		 * @method _init
		 */
		_init: function()
		{
			$('body').on( 'click', '#fl-import-export-form input.export', FLBuilderGlobalImportExport._exportClicked);
			$('body').on( 'click', '#fl-import-export-form input.import', FLBuilderGlobalImportExport._importClicked);
			$('body').on( 'click', '#fl-import-export-form input.reset', FLBuilderGlobalImportExport._resetClicked);
			$('body').on( 'click', '#fl-snapshots-section .snapshot-save', FLBuilderGlobalImportExport._saveSnapshotClicked);
			$('body').on( 'click', '#fl-snapshots-section .snapshot-restore', FLBuilderGlobalImportExport._restoreSnapshotClicked);
			$('body').on( 'click', '#fl-snapshots-section .snapshot-delete', FLBuilderGlobalImportExport._deleteSnapshotClicked);
			FLBuilderGlobalImportExport.bindChecks();
			FLBuilderGlobalImportExport._renderSnapshotsList();
		},

		_exportClicked: function() {

			nonce = $('#fl-import-export-form').find('#_wpnonce').val();

			data = {
				global_all: $('#fl-import-export-form input.global_all').prop('checked'),
				admin: $('#fl-import-export-form input.admin').prop('checked'),
				global: $('#fl-import-export-form input.global').prop('checked'),
				styles: $('#fl-import-export-form input.styles').prop('checked'),
				colors: $('#fl-import-export-form input.colors').prop('checked'),
			}

			// generate data file.
			FLBuilderGlobalImportExport.ajax( {
				action: 'export_global_settings',
				data: data,
				_wpnonce: nonce,
			}, function ( response ) {

				switch( response.success ) {
					case false:
						break;
					case true:
						data     = response.data;
						settings = data.settings;
						var filename = '';

						$.each( data.selected, function( e,i ) {
							if ( 'global_all' === e && 'true' === i ) {
								filename += 'all-';
								return false;
							}
							if ( 'true' === i ) {
								filename += e + '-';
							}
						});
						date  = new Date();
						day   = date.getDate();
						month = date.getMonth() + 1;
						year  = date.getFullYear();
						filename = 'bb-settings-' + filename + `${year}-${month}-${day}` + '.txt';
						var blob = new Blob( [settings], { type: "application/octetstream" } );

						//Check the Browser type and download the File.
						var isIE = false || !!document.documentMode;
						if (isIE) {
							 window.navigator.msSaveBlob(blob, filename);
						} else {
							 var url = window.URL || window.webkitURL;
							 link = url.createObjectURL(blob);
							 var a = $("<a />");
							 a.attr("download", filename);
							 a.attr("href", link);
							 $("body").append(a);
							 a[0].click();
							 $("body").remove(a);
						}
						break;
				}
			});
		},
		_importClicked: function() {
			if(FLBuilderGlobalImportExport._settingsUploader === null) {
				FLBuilderGlobalImportExport._settingsUploader = wp.media({
					title: 'Import Settings',
					button: { text: FLBuilderAdminImportExportConfig.select },
					library : { type : 'text/plain' },
					multiple: false
				});

				_wpPluploadSettings['defaults']['multipart_params']['fl_global_import']= 'json';

				FLBuilderGlobalImportExport._settingsUploader.on( 'select', function() {
					var selection = FLBuilderGlobalImportExport._settingsUploader.state().get('selection');
					var attachment_id = selection.map( function( attachment ) {
						attachment = attachment.toJSON();
						return attachment.id;
					}).join();

					if ( ! confirm( 'Are you sure you want to import settings?' ) ) {
						return;
					}

					var self = FLBuilderGlobalImportExport;
					var nonce = $('#fl-import-export-form').find('#_wpnonce').val();
					var snapshotCount = Object.keys( self._snapshots ).length;

					if ( confirm( 'Would you like to create a backup snapshot before importing?' ) ) {
						if ( snapshotCount >= self._maxSnapshots ) {
							if ( ! confirm( 'You already have ' + self._maxSnapshots + ' snapshots. The oldest snapshot will be deleted to make room. Continue?' ) ) {
								return;
							}
						}
						self.ajax({
							action: 'save_settings_snapshot',
							snapshot_name: 'Pre-Import Backup ' + new Date().toLocaleString(),
							_wpnonce: nonce,
						}, function( response ) {
							if ( response.success ) {
								self._snapshots = response.data.snapshots;
								FLBuilderGlobalImportExport._importSettings( attachment_id );
							} else {
								alert( 'Failed to create backup snapshot. Import aborted.' );
							}
						});
						return;
					}

					FLBuilderGlobalImportExport._importSettings( attachment_id );
				});
			}
			FLBuilderGlobalImportExport._settingsUploader.open();
		},
		_importSettings: function(attachment_id) {
			nonce = $('#fl-import-export-form').find('#_wpnonce').val();
			FLBuilderGlobalImportExport.ajax( {
				action: 'import_global_settings',
				_wpnonce: nonce,
				importid: attachment_id
			}, function ( response ) {
				switch( response.success ) {
					case false:
						if ( 'undefined' != typeof response.data ) {
							alert( response.data )
						} else {
							alert( 'Something went wrong' );
						}
						break;
					case true:
						alert( 'Success!');
						location.reload();
						break;
				};
			});
		},
		_resetClicked: function() {
			var nonce = $('#fl-import-export-form').find('#_wpnonce').val();
			var self = FLBuilderGlobalImportExport;
			var snapshotCount = Object.keys( self._snapshots ).length;

			if ( ! confirm( 'Are you sure you want to reset all settings?' ) ) {
				return;
			}

			if ( confirm( 'Would you like to create a backup snapshot before resetting?' ) ) {
				if ( snapshotCount >= self._maxSnapshots ) {
					if ( ! confirm( 'You already have ' + self._maxSnapshots + ' snapshots. The oldest snapshot will be deleted to make room. Continue?' ) ) {
						return;
					}
				}
				self.ajax({
					action: 'save_settings_snapshot',
					snapshot_name: 'Pre-Reset Backup ' + new Date().toLocaleString(),
					_wpnonce: nonce,
				}, function( response ) {
					if ( response.success ) {
						self._snapshots = response.data.snapshots;
						self._doReset( nonce );
					} else {
						alert( 'Failed to create backup snapshot. Reset aborted.' );
					}
				});
				return;
			}

			self._doReset( nonce );
		},
		_doReset: function( nonce ) {
			FLBuilderGlobalImportExport.ajax({
				action: 'reset_global_settings',
				_wpnonce: nonce,
			}, function( response ) {
				if ( response.success ) {
					alert( 'Success!' );
					location.reload();
				} else {
					alert( 'There was an error :(' );
				}
			});
		},
		_saveSnapshotClicked: function() {
			var self  = FLBuilderGlobalImportExport;
			var nonce = $('#fl-import-export-form').find('#_wpnonce').val();
			var name  = $('#snapshot-name').val();
			var count = Object.keys( self._snapshots ).length;

			if ( count >= self._maxSnapshots ) {
				if ( ! confirm( 'Only ' + self._maxSnapshots + ' snapshots can be saved. The oldest snapshot will be deleted. Continue?' ) ) {
					return;
				}
			}

			self.ajax({
				action: 'save_settings_snapshot',
				snapshot_name: name,
				_wpnonce: nonce,
			}, function( response ) {
				if ( response.success ) {
					self._snapshots = response.data.snapshots;
					$('#snapshot-name').val('');
					self._renderSnapshotsList();
				} else {
					if ( response.data ) {
						alert( response.data );
					} else {
						alert( 'Failed to save snapshot.' );
					}
				}
			});
		},
		_restoreSnapshotClicked: function() {
			var self          = FLBuilderGlobalImportExport;
			var nonce         = $('#fl-import-export-form').find('#_wpnonce').val();
			var snapshotId    = $(this).data('snapshot-id');
			var snapshotCount = Object.keys( self._snapshots ).length;

			if ( ! confirm( 'Are you sure you want to restore this snapshot? Current settings will be overwritten.' ) ) {
				return;
			}

			var doRestore = function() {
				self.ajax({
					action: 'restore_settings_snapshot',
					snapshot_id: snapshotId,
					_wpnonce: nonce,
				}, function( response ) {
					if ( response.success ) {
						alert( 'Snapshot restored successfully!' );
						location.reload();
					} else {
						if ( response.data ) {
							alert( response.data );
						} else {
							alert( 'Failed to restore snapshot.' );
						}
					}
				});
			};

			if ( confirm( 'Would you like to create a backup snapshot before restoring?' ) ) {
				if ( snapshotCount >= self._maxSnapshots ) {
					if ( ! confirm( 'You already have ' + self._maxSnapshots + ' snapshots. The oldest snapshot will be deleted to make room. Continue?' ) ) {
						return;
					}
				}
				self.ajax({
					action: 'save_settings_snapshot',
					snapshot_name: 'Pre-Restore Backup ' + new Date().toLocaleString(),
					_wpnonce: nonce,
				}, function( response ) {
					if ( response.success ) {
						self._snapshots = response.data.snapshots;
						doRestore();
					} else {
						alert( 'Failed to create backup snapshot. Restore aborted.' );
					}
				});
				return;
			}

			doRestore();
		},
		_deleteSnapshotClicked: function() {
			var self       = FLBuilderGlobalImportExport;
			var nonce      = $('#fl-import-export-form').find('#_wpnonce').val();
			var snapshotId = $(this).data('snapshot-id');

			if ( ! confirm( 'Are you sure you want to delete this snapshot?' ) ) {
				return;
			}

			self.ajax({
				action: 'delete_settings_snapshot',
				snapshot_id: snapshotId,
				_wpnonce: nonce,
			}, function( response ) {
				if ( response.success ) {
					self._snapshots = response.data.snapshots;
					self._renderSnapshotsList();
				} else {
					if ( response.data ) {
						alert( response.data );
					} else {
						alert( 'Failed to delete snapshot.' );
					}
				}
			});
		},
		_renderSnapshotsList: function() {
			var self      = FLBuilderGlobalImportExport;
			var $list     = $('#fl-snapshots-list');
			var snapshots = self._snapshots;
			var keys      = Object.keys( snapshots );

			$list.empty();

			if ( keys.length === 0 ) {
				$list.html('<p class="description">No snapshots saved.</p>');
				return;
			}

			// Sort by created_at descending (newest first).
			keys.sort(function(a, b) {
				return snapshots[b].created_at - snapshots[a].created_at;
			});

			var $table = $('<table class="widefat fl-snapshots-table"><thead><tr>' +
				'<th>Name</th><th>Date</th><th>Actions</th>' +
				'</tr></thead><tbody></tbody></table>');
			var $tbody = $table.find('tbody');

			$.each( keys, function( i, id ) {
				var snapshot = snapshots[ id ];
				var date     = new Date( snapshot.created_at * 1000 );
				var dateStr  = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();

				var $row = $('<tr>' +
					'<td><strong>' + $('<span>').text( snapshot.name ).html() + '</strong></td>' +
					'<td>' + $('<span>').text( dateStr ).html() + '</td>' +
					'<td>' +
						'<input type="button" class="button button-primary snapshot-restore" data-snapshot-id="' + id + '" value="Restore" /> ' +
						'<input type="button" class="button button-primary fl-tools-btn-danger snapshot-delete" data-snapshot-id="' + id + '" value="Delete" />' +
					'</td>' +
				'</tr>');

				$tbody.append( $row );
			});

			$list.append( $table );
		},
		/**
		 * Makes an AJAX request.
		 *
		 * @since 1.0
		 * @method ajax
		 * @param {Object} data An object with data to send in the request.
		 * @param {Function} callback A function to call when the request is complete.
		 */
		ajax: function(data, callback) {
			// Send the request.
			$.post(ajaxurl, data, function(response) {
				if(typeof callback !== 'undefined') {
					callback.call(this, response);
				}
			});
		},
		bindChecks: function() {
			$('body').on( 'change', '#fl-import-export-form input.global_all', function(){
				checked = $(this).prop('checked')
				if ( ! checked ) {
					$('#fl-import-export-form .extra').fadeIn();
				} else {
					$('#fl-import-export-form .extra').fadeOut();
				}
			});
		}
	}
	$( FLBuilderGlobalImportExport._init );
} )( jQuery );
