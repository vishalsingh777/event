define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'Vishal_Events/form/field/time-slots-display',
            elementTmpl: 'Vishal_Events/form/field/time-slots-display',
            timeSlots: [],
            listens: {
                '${ $.provider }:data.time_slots': 'onTimeSlotChange'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            // Parse initial time slots
            this.parseTimeSlots(this.value());
            
            return this;
        },
        
        /**
         * Parse time slots from JSON or string
         */
        parseTimeSlots: function (value) {
            var slots = [];
            
            if (value) {
                try {
                    if (typeof value === 'string') {
                        if (value.indexOf('[') === 0) {
                            // Parse JSON string
                            slots = JSON.parse(value);
                        } else if (value.indexOf('-') !== -1) {
                            // Single time slot string
                            slots = [value];
                        }
                    } else if (Array.isArray(value)) {
                        slots = value;
                    }
                } catch (e) {
                    console.error('Error parsing time slots:', e);
                }
            }
            
            this.timeSlots = slots;
        },
        
        /**
         * Handle time slots change
         */
        onTimeSlotChange: function (value) {
            this.parseTimeSlots(value);
        },
        
        /**
         * Check if there are any time slots
         */
        hasTimeSlots: function () {
            return this.timeSlots && this.timeSlots.length > 0;
        },
        
        /**
         * Get time slots for display
         */
        getTimeSlots: function () {
            var formattedSlots = [];
            
            _.each(this.timeSlots, function (slot) {
                // Format time if needed
                formattedSlots.push(this.formatTimeSlot(slot));
            }, this);
            
            return formattedSlots;
        },
        
        /**
         * Format time slot for display
         */
        formatTimeSlot: function (slot) {
            // For now, just return the slot as is
            // Could implement 24h to 12h conversion here if desired
            return slot;
        }
    });
});