define([
    'jquery',
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function ($, _, DynamicRows) {
    'use strict';

    return DynamicRows.extend({
        defaults: {
            template: 'Vishal_Events/form/field/time-slots-dynamic',
            defaultTimeStart: '19:00',
            defaultTimeEnd: '22:00',
            errorMessage: 'Time slot is duplicated',
            timeSlots: [],
            duplicateErrors: {},
            listens: {
                '${ $.provider }:data.validate': 'validate'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            // If empty, add first time slot
            if (!this.getChildItems().length) {
                this.addChild();
            }
            
            return this;
        },
        
        /**
         * Check for duplicate time slots
         */
        checkDuplicates: function () {
            var self = this;
            var items = this.getChildItems();
            var timeSlots = [];
            
            this.duplicateErrors = {};
            
            _.each(items, function (item, index) {
                var startTime = item.start_time;
                var endTime = item.end_time;
                var timeSlot = startTime + '-' + endTime;
                
                if (_.contains(timeSlots, timeSlot)) {
                    self.duplicateErrors[index] = true;
                } else {
                    timeSlots.push(timeSlot);
                }
            });
            
            this.timeSlots = timeSlots;
            
            return Object.keys(this.duplicateErrors).length === 0;
        },
        
        /**
         * Add child with default values
         */
        addChild: function (data, index) {
            var defaultData = {
                start_time: this.defaultTimeStart,
                end_time: this.defaultTimeEnd
            };
            
            data = data || defaultData;
            
            this._super(data, index);
            
            // Check for duplicates after adding
            this.checkDuplicates();
        },
        
        /**
         * Validate time slots
         */
        validate: function () {
            return this.checkDuplicates();
        },
        
        /**
         * Check if item has duplicate error
         */
        hasError: function (index) {
            return this.duplicateErrors[index] === true;
        }
    });
});