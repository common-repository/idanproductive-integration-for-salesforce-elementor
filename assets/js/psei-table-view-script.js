/**
 * Table View Handler for Elementor.
 *
 * This script handles the functionality of the Table View widget in Elementor.
 * It manages table selection, field updates, and widget state persistence in the editor.
 */
( function( $ ) {
	'use strict';

	var PSEISalesforceAddonHandler = {
		/**
		 * Initialize the handler.
		 */
		init: function( panel, model, view ) {
			this.$element = view.$el;
			this.model = model;
			this.panel = panel;
			this.view = view;
			this.cachedFields = {};
			this.currentTable = '';

			this.setupEventListeners();
			this.restoreWidgetState();
		},

		/**
		 * Set up event listeners for the widget controls.
		 */
		setupEventListeners: function() {
			var self = this;

			this.panel.$el.on( 'change', 'select[data-setting="table"]', function() {
				var selectedTable = $( this ).val();
				if ( selectedTable !== self.currentTable ) {
					self.currentTable = selectedTable;
					self.updateFieldsControl( selectedTable );
				}
			} );

			this.panel.$el.on( 'change', '.elementor-repeater-fields select[data-setting="field"]', function() {
				self.saveWidgetState();
			} );

			this.panel.$el.on( 'click', '.elementor-repeater-add', function() {
				setTimeout( function() {
					self.updateNewRepeaterRow();
				}, 100 );
			} );
		},

		/**
		 * Update fields control based on the selected table.
		 */
		updateFieldsControl: function( selectedTable ) {
			var self = this;

			if ( ! selectedTable ) {
				return;
			}

			if ( this.cachedFields[selectedTable] ) {
				this.updateRepeaterFields( this.cachedFields[selectedTable] );
				return;
			}

			$.ajax( {
				url: psei_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'psei_get_table_fields',
					table: selectedTable,
					widget: 'table-view',
					nonce: psei_ajax_object.nonce
				},
				success: function( response ) {
					if ( response.success ) {
						self.cachedFields[selectedTable] = response.data;
						self.updateRepeaterFields( response.data );
					}
				}
			} );
		},

		/**
		 * Update repeater fields with new field options.
		 */
		updateRepeaterFields: function( fields ) {
			var self = this;
			var columns = this.model.get( 'settings' ).get( 'columns' );

			if ( columns && columns.models ) {
				columns.models.forEach( function( column, index ) {
					var $fieldSelect = self.panel.$el.find( '.elementor-repeater-fields' ).eq( index ).find( 'select[data-setting="field"]' );
					var currentValue = column.get( 'field' );
					self.updateFieldSelect( $fieldSelect, fields, currentValue );
				} );
			}

			this.saveWidgetState();
		},

		/**
		 * Update the field select control in a new repeater row.
		 */
		updateNewRepeaterRow: function() {
			var $newRow = this.panel.$el.find( '.elementor-repeater-fields' ).last();
			var $fieldSelect = $newRow.find( 'select[data-setting="field"]' );
			var fields = this.cachedFields[this.currentTable] || {};
			this.updateFieldSelect( $fieldSelect, fields );
		},

		/**
		 * Update a single field select control.
		 */
		updateFieldSelect: function( $fieldSelect, fields, currentValue ) {
			$fieldSelect.empty();
			$.each( fields, function( value, label ) {
				$fieldSelect.append( $( '<option></option>' ).attr( 'value', value ).text( label ) );
			} );

			if ( currentValue && fields.hasOwnProperty( currentValue ) ) {
				$fieldSelect.val( currentValue );
			}

			$fieldSelect.trigger( 'change' );
		},

		/**
		 * Save the current widget state.
		 */
		saveWidgetState: function() {
			var settings = this.model.get( 'settings' );
			if ( settings ) {
				settings.set( 'table', this.currentTable );
			}
		},

		/**
		 * Restore the widget state from saved settings.
		 */
		restoreWidgetState: function() {
			var settings = this.model.get( 'settings' );
			if ( settings ) {
				var savedTable = settings.get( 'table' );
				if ( savedTable ) {
					this.currentTable = savedTable;
					this.panel.$el.find( 'select[data-setting="table"]' ).val( savedTable );

					var self = this;
					setTimeout( function() {
						self.updateFieldsControl( savedTable );
					}, 100 );
				}
			}
		}
	};

	elementor.hooks.addAction( 'panel/open_editor/widget/table-view', function( panel, model, view ) {
		PSEISalesforceAddonHandler.init( panel, model, view );
	} );

} )( jQuery );