define([
    'jquery',
    'underscore',
    'uiComponent',
    'mage/translate'
], function ($, _, Component, $t) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Vishal_Events/dynamic-timeslots',
            timeSlots: [
                {
                    id: 'initial',
                    startTime: '',
                    endTime: ''
                }
            ],
            links: {
                timeSlots: '${ $.provider }:${ $.dataScope }'
            },
            listens: {
                timeSlots: 'updateTimeSlotData'
            }
        },
        
        initialize: function () {
            this._super();
            
            // Ensure the template is properly set
            if (!this.template) {
                this.template = 'Vishal_Events/dynamic-timeslots';
            }
            
            return this;
        },
        
        initObservable: function () {
            this._super().observe(['timeSlots']);
            return this;
        },
        
        /**
         * Add a new time slot pair
         * @returns {Object} - reference to this for chaining
         */
        addTimeSlot: function () {
            var slots = this.timeSlots();
            slots.push({
                id: 'timeslot_' + this.generateUniqueId(),
                startTime: '',
                endTime: ''
            });
            this.timeSlots(slots);
            this.updateTimeSlotData();
            return this;
        },
        
        /**
         * Remove a time slot pair
         * @param {Number} index - index of the time slot to remove
         * @returns {Object} - reference to this for chaining
         */
        removeTimeSlot: function (index) {
            var slots = this.timeSlots();
            if (slots.length > 1) { // Always keep at least one time slot
                slots.splice(index, 1);
                this.timeSlots(slots);
                this.updateTimeSlotData();
            }
            return this;
        },
        
        /**
         * Update start time for a specific time slot
         * @param {Number} index - index of time slot to update
         * @param {String} value - new start time value
         */
        updateStartTime: function (index, value) {
            var slots = this.timeSlots();
            slots[index].startTime = value;
            this.timeSlots(slots);
            this.updateTimeSlotData();
        },
        
        /**
         * Update end time for a specific time slot
         * @param {Number} index - index of time slot to update
         * @param {String} value - new end time value
         */
        updateEndTime: function (index, value) {
            var slots = this.timeSlots();
            slots[index].endTime = value;
            this.timeSlots(slots);
            this.updateTimeSlotData();
        },
        
        /**
         * Update the data source with the current time slots
         */
        updateTimeSlotData: function () {
            // Properly set data in the provider
            this.source.set(this.dataScope, this.timeSlots());
        },
        
        /**
         * Generate a unique ID for time slots
         * @returns {String} - unique ID
         */
        generateUniqueId: function () {
            return Math.random().toString(36).substr(2, 9);
        }
    });
});