/**
 * Event Form Enhancement Script
 * 
 * This script enhances the Magento UI components with modern styling and functionality
 * to match the reference design
 */
require([
    'jquery',
    'mage/translate',
    'domReady!'
], function ($, $t) {
    'use strict';
    // Initialize everything when DOM is ready
    $(document).ready(function() {
        // Wait until the select is available
        var checkExist = setInterval(function () {
            var $select = $('select[name="recurring"]');
            var $button = $('button[data-action="add_new_row"]');
            
            if ($select.length && $button.length) {
                clearInterval(checkExist);
                var recurringValue = $select.val();
                console.log('Recurring value:', recurringValue);
                
                // If recurring is 0, disable the button
                // If recurring is 1, enable the button
                if (recurringValue === '0') {

                    $button.prop('disabled', true);
                    $('.muliselect-fieldset-range').hide();
                } else if (recurringValue === '1') {
                    $button.prop('disabled', false);
                    $('.muliselect-fieldset-range').show();
                }
            }
        }, 100);
        
        $(document).on('change', 'select[name="recurring"]', function () {
            // Get the selected value from the dropdown
            var selectedValue = $(this).val();
            console.log('Recurring value changed to:', selectedValue); 
            
            // Find the button with data-action="add_new_row"
            var $button = $('button[data-action="add_new_row"]');
            
            // Enable or disable the button based on the selected value
            if (selectedValue === '0') {
                // Disable the button if value is '0'
                $button.prop('disabled', true);
                $('.muliselect-fieldset-range').hide();
            } else if (selectedValue === '1') {
                // Enable the button if value is '1'
                $button.prop('disabled', false);
                $('.muliselect-fieldset-range').show();
            }
        });
    });
});