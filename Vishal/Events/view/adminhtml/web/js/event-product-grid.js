define([
    'jquery',
    'mage/apply/main',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, mage, modal, $t) {
    'use strict';

    $.widget('mage.eventProductGrid', {
        options: {
            gridSelector: '[data-grid-id="event_product_listing"]',
            inputSelector: '#event_products',
            checked: {}
        },

        /**
         * Initialize widget
         * @private
         */
        _create: function () {
            this.initCheckedProducts();
            this.bindProductGridEvents();
        },

        /**
         * Initialize checked products
         */
        initCheckedProducts: function () {
            var self = this;
            
            // Parse selected product IDs from input
            if (this.element.val()) {
                try {
                    var productIds = this.element.val().split(',');
                    $.each(productIds, function (index, productId) {
                        if (productId) {
                            self.options.checked[productId] = 1;
                        }
                    });
                } catch (e) {
                    console.error('Failed to parse selected products', e);
                }
            }
            
            // Pre-check products in grid
            $(document).on('contentUpdated', this.options.gridSelector, function () {
                self.updateCheckedProducts();
            });
        },

        /**
         * Bind product grid events
         */
        bindProductGridEvents: function () {
            var self = this;
            
            // Handle checkbox changes
            $(document).on('change', this.options.gridSelector + ' input[type="checkbox"]', function () {
                var productId = $(this).val();
                
                if (productId && $(this).is(':checked')) {
                    self.options.checked[productId] = 1;
                } else if (productId) {
                    delete self.options.checked[productId];
                }
                
                self.updateProductInput();
            });
        },

        /**
         * Update checked products in the grid
         */
        updateCheckedProducts: function () {
            var self = this;
            
            // Check products that should be checked
            $(this.options.gridSelector + ' input[type="checkbox"]').each(function () {
                var productId = $(this).val();
                
                if (productId && self.options.checked[productId]) {
                    $(this).prop('checked', true);
                }
            });
        },

        /**
         * Update product input with selected product IDs
         */
        updateProductInput: function () {
            var selectedProducts = Object.keys(this.options.checked).join(',');
            this.element.val(selectedProducts);
        }
    });

    return $.mage.eventProductGrid;
});