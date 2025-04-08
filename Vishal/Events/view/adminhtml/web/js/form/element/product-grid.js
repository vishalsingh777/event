define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            valueUpdate: false,
            elementTmpl: 'ui/form/element/hidden',
            listens: {
                '${ $.provider }:data.validate': 'validate'
            }
        },

        /**
         * Initialize component
         *
         * @returns {Object} Chainable
         */
        initialize: function () {
            this._super();
            
            // Listen for product grid loading and selections
            registry.async('index = event_product_listing')(function (productGrid) {
                var selections = productGrid.selections();
                
                selections.on('selectedRowsChanged', function (selected) {
                    var productIds = _.pluck(selected, 'entity_id').join(',');
                    this.value(productIds);
                }.bind(this));

                // Set initial values if this is an edit form
                if (this.value()) {
                    var initialIds = this.value().split(',');
                    initialIds.forEach(function (id) {
                        selections.select(id);
                    });
                }
            }.bind(this));

            return this;
        }
    });
});