define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';
    
    // Initialize datetime modal when DOM is ready
    $(function () {
        // Wait for modal element to be available
        var checkInterval = setInterval(function () {
            if ($('#event-datetime-modal').length) {
                clearInterval(checkInterval);
                
                // Load modal widget
                require(['Vishal_Events/js/event/datetime-modal'], function (datetimeModal) {
                    $('#event-datetime-modal').datetimeModal({
                        triggerButtonSelector: '#add-event-datetime'
                    });
                });
            }
        }, 500);
    });
});