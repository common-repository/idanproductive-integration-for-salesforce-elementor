(function($) {
    var PSEIOrdersCardHandler = {
        cachedFields: {},

        init: function(panel, model, view) {
            this.$element = view.$el;
            this.container = view.getContainer();
            this.panel = panel;

            this.setupEventListeners();
            this.loadInitialFields();
        },

        setupEventListeners: function() {
            var self = this;
            this.panel.$el.on('change', 'select[data-setting="table"]', function() {
                var selectedTable = $(this).val();
                self.updateFieldsControl(selectedTable);
            });
        },

        loadInitialFields: function() {
            var selectedTable = this.container.settings.get('table');
            if (selectedTable) {
                this.updateFieldsControl(selectedTable);
            }
        },

        updateFieldsControl: function(selectedTable) {
            var self = this;
            if (!selectedTable) {
                return;
            }

            if (this.cachedFields[selectedTable]) {
                this.populateFieldsControl(this.cachedFields[selectedTable]);
            } else {
                $.ajax({
                    url: psei_ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'psei_get_table_fields',
                        table: selectedTable,
                        widget: 'table-view',
                        nonce: psei_ajax_object.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            self.cachedFields[selectedTable] = response.data;
                            self.populateFieldsControl(response.data);
                        } else {
                            console.error('AJAX request failed:', response.data);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX request error:', textStatus, errorThrown);
                    }
                });
            }
        },

        populateFieldsControl: function(fields) {
            var fieldsControl = this.panel.$el.find('select[data-setting="fields"]');
            fieldsControl.empty();
            $.each(fields, function(value, label) {
                fieldsControl.append($('<option></option>').attr('value', value).text(label));
            });
            
            // Restore previously selected fields
            var selectedFields = this.container.settings.get('fields');
            if (selectedFields && selectedFields.length > 0) {
                fieldsControl.val(selectedFields);
            }
            
            fieldsControl.trigger('change');
        }
    };

    elementor.hooks.addAction('panel/open_editor/widget/orders-card', function(panel, model, view) {
        PSEIOrdersCardHandler.init(panel, model, view);
    });

})(jQuery);