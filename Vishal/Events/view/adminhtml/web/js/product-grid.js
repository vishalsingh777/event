define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    'use strict';
    
    return function (config) {
        $(document).on('submit', config.formSelector, function (e) {
            var selectedProducts = [];
            var productGrid = registry.get('event_product_listing');
            
            if (productGrid) {
                // Get all selected products
                var selections = productGrid.selections();
                
                if (selections && selections.getSelected().length) {
                    selections.getSelected().forEach(function (product) {
                        selectedProducts.push(product.entity_id);
                        
                        // Add a hidden input for each product with its position
                        var positionInput = $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', 'product_tickets[]')
                            .val(product.entity_id);
                            
                        $(e.target).append(positionInput);
                    });
                }
            }
            
            // The form submission continues as normal, and your Save controller
            // will handle these inputs via the processProductTickets method
        });
    };
});