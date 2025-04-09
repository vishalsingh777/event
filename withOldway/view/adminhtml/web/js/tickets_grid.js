define([
    'jquery',
    'mage/url',
    'jquery/ui'
], function ($, urlBuilder) {
    'use strict';

    return function (config, element) {
        var gridContainer = $(element);

        // Listen for change event on checkbox inputs
        $('input[name="in_tickets[]"]').on('change', function () {
            var selected = [];
            $('input[name="in_tickets[]"]:checked').each(function () {
                selected.push($(this).val());
            });

            var gridUrl = config.gridUrl;

            $.ajax({
                url: gridUrl,
                data: {
                    'filter[in_tickets]': selected.join(',')  // Send selected filter values
                },
                type: 'GET',
                success: function (response) {
                    // Replace grid content with new HTML
                    var newGridHtml = $(response).find(gridContainer.selector).html();
                    gridContainer.html(newGridHtml);
                },
                error: function () {
                    console.log('Grid reload failed.');
                }
            });
        });
    }
});
