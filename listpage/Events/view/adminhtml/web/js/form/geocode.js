define([
    'jquery',
    'uiComponent',
    'mage/translate'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            addressSelector: 'input[name="address"]',
            latitudeSelector: 'input[name="latitude"]',
            longitudeSelector: 'input[name="longitude"]',
            buttonText: $t('Get Coordinates'),
            geocodeUrl: 'https://maps.googleapis.com/maps/api/geocode/json',
            apiKey: ''
        },

        initialize: function () {
            this._super();
            this.createButton();
            return this;
        },

        createButton: function () {
            var self = this;
            var $button = $('<button>', {
                type: 'button',
                'class': 'action-default',
                text: this.buttonText
            });

            $button.on('click', function (e) {
                e.preventDefault();
                self.geocodeAddress();
            });

            // Add button after longitude field
            $(this.longitudeSelector).after($button);
        },

        geocodeAddress: function () {
            var self = this;
            var address = $(this.addressSelector).val();

            if (!address) {
                alert($t('Please enter an address first.'));
                return;
            }

            $.ajax({
                url: this.geocodeUrl,
                data: {
                    address: address,
                    key: this.apiKey
                },
                success: function (response) {
                    if (response.status === 'OK' && response.results.length > 0) {
                        var location = response.results[0].geometry.location;
                        $(self.latitudeSelector).val(location.lat);
                        $(self.longitudeSelector).val(location.lng);
                    } else {
                        alert($t('Could not find coordinates for this address.'));
                    }
                },
                error: function () {
                    alert($t('Error occurred while fetching coordinates.'));
                }
            });
        }
    });
});