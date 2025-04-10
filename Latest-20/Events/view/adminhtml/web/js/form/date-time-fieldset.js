define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/components/html',
    'mage/translate'
], function ($, _, Html, $t) {
    'use strict';

    return Html.extend({
        defaults: {
            template: 'Vishal_Events/form/date-time-slots',
            content: '',
            timeSlots: [],
            isMultipleTimes: false
        },

        initialize: function () {
            this._super();
            
            // Initialize event handlers once the component is rendered
            $(document).on('click', '.single-time-tab', function() {
                $('.multiple-times-tab').removeClass('active');
                $('.single-time-tab').addClass('active');
                $('.multiple-times-container').hide();
                $('.single-time-container').show();
                $('input[name="is_multiple_times"]').val('0');
            });
            
            $(document).on('click', '.multiple-times-tab', function() {
                $('.single-time-tab').removeClass('active');
                $('.multiple-times-tab').addClass('active');
                $('.single-time-container').hide();
                $('.multiple-times-container').show();
                $('input[name="is_multiple_times"]').val('1');
            });
            
            $(document).on('click', '.add-time-slot', function() {
                var timeSlotHtml = $('.time-slot-row').first().clone();
                timeSlotHtml.find('input, select').val('');
                timeSlotHtml.find('.error-message').remove();
                $('.time-slots-container').append(timeSlotHtml);
            });
            
            $(document).on('click', '.remove-time-slot', function() {
                if ($('.time-slot-row').length > 1) {
                    $(this).closest('.time-slot-row').remove();
                }
            });
            
            return this;
        }
    });
});