define([
    'jquery',
    'mage/translate',
    'jquery/validate'
], function ($, $t) {
    'use strict';

    return function () {
        $.validator.addMethod(
            'validate-time',
            function (value) {
                if (value === '') {
                    return true;
                }
                
                // Check for 24h time format (HH:MM)
                return /^([01]?[0-9]|2[0-3]):([0-5][0-9])$/.test(value);
            },
            $t('Please enter a valid time in 24-hour format (HH:MM).')
        );
    };
});