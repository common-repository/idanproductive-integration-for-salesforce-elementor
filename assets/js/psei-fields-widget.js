(function($) {
    var PSEIFieldsWidgetHandler = {
        cachedFields: {},

        init: function( panel, model, view ) {
            this.$element = view.$el;
            this.container = view.getContainer();
            this.panel = panel;
            this.widgetType = model.get( 'widgetType' );

            this.setupEventListeners();
            this.loadInitialFields();
        },

        setupEventListeners: function() {
            this.panel.$el.on( 'change', 'select[data-setting="table"]', this.onTableChange.bind( this ));
        },

        onTableChange: function( event ) {
            var selectedTable = $( event.currentTarget ).val();
            this.updateFieldsControl( selectedTable );
        },

        loadInitialFields: function() {
            var selectedTable = this.container.settings.get( 'table' );
            if ( selectedTable ) {
                this.updateFieldsControl( selectedTable );
            }
        },

        updateFieldsControl: function( selectedTable ) {
            if ( ! selectedTable ) {
                return;
            }

            if ( this.cachedFields[ selectedTable ] ) {
                this.populateFieldsControl( this.cachedFields[ selectedTable ] );
            } else {
                this.fetchFields( selectedTable );
            }
        },

        fetchFields: function( selectedTable ) {
            $.ajax({
                url: psei_ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'psei_get_table_fields',
                    table: selectedTable,
                    widget: this.widgetType,
                    nonce: psei_ajax_object.nonce
                },
                success: this.onFieldsFetched.bind( this, selectedTable ),
                error: this.onFieldsFetchError.bind( this )
            });
        },

        onFieldsFetched: function( selectedTable, response ) {
            if ( response.success ) {
                this.cachedFields[ selectedTable ] = response.data;
                this.populateFieldsControl( response.data );
            } else {
                console.error( 'PSEIFieldsWidgetHandler: AJAX request failed:', response.data );
            }
        },

        onFieldsFetchError: function( jqXHR, textStatus, errorThrown ) {
            console.error( 'PSEIFieldsWidgetHandler: AJAX request error:', textStatus, errorThrown );
        },

        populateFieldsControl: function( fields ) {
            var fieldsControl = this.panel.$el.find( 'select[data-setting="field"]' );
            fieldsControl.empty()
                .append( $( '<option></option>' ).attr( 'value', '' ).text( 'Select a field' ) );
            
            $.each( fields, function( value, label ) {
                fieldsControl.append( $( '<option></option>' ).attr( 'value', value ).text( label ) );
            });
            
            var selectedField = this.container.settings.get( 'field' );
            if ( selectedField ) {
                fieldsControl.val( selectedField );
            }

            fieldsControl.trigger( 'change' );
        }
    };

    elementor.hooks.addAction( 'panel/open_editor/widget/fields-widget', function( panel, model, view ) {
        PSEIFieldsWidgetHandler.init( panel, model, view );
    });
} )( jQuery );