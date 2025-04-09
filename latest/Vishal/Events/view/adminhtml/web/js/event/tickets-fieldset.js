define([
    'jquery',
    'underscore',
    'mage/template',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'ko',
    'mage/translate'
], function ($, _, mageTemplate, registry, abstract, ko, $t) {
    'use strict';

    return abstract.extend({
        defaults: {
            template: 'Vishal_Events/event/tickets-fieldset',
            ticketsData: [],
            ticketItemTemplate: '# ticket_item_template',
            itemCount: 0,
            links: {
                ticketsData: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            return this;
        },

        /**
         * Initialize observable properties
         */
        initObservable: function () {
            this._super()
                .observe([
                    'ticketsData'
                ]);

            return this;
        },
        
        /**
         * Add new ticket item
         */
        addItem: function () {
            var ticketItem = {
                ticket_id: '',
                name: '',
                sku: '',
                price: '',
                product_id: '',
                position: 0
            };
            
            var ticketsData = this.ticketsData() || [];
            ticketsData.push(ticketItem);
            this.ticketsData(ticketsData);
        },
        
        /**
         * Remove ticket item
         */
        removeItem: function (index) {
            var ticketsData = this.ticketsData() || [];
            ticketsData.splice(index, 1);
            this.ticketsData(ticketsData);
        },
        
        /**
         * Get product selector URL
         */
        getProductSelectorUrl: function () {
            return this.productSelectorUrl;
        },
        
        /**
         * Open product selector
         */
        openProductSelector: function (index) {
            var self = this;
            
            require([
                'Magento_Ui/js/modal/modal'
            ], function (modal) {
                var options = {
                    type: 'slide',
                    responsive: true,
                    innerScroll: true,
                    title: $t('Select Product'),
                    buttons: [{
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function () {
                            this.closeModal();
                        }
                    }, {
                        text: $t('Confirm'),
                        class: 'action-primary action-accept',
                        click: function () {
                            // Get selected product and update ticket data
                            this.closeModal();
                        }
                    }]
                };
                
                // Create and open modal for product selection
                var productSelectorModal = $('<div/>').modal(options);
                productSelectorModal.modal('openModal');
            });
        }
    });
});