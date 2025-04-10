define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'mage/translate',
    'mage/calendar'
], function ($, _, Component, ko, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Vishal_Events/form/event-scheduler',
            timeSlotOptions: [
                {value: '08:00', label: '8:00 AM'},
                {value: '09:00', label: '9:00 AM'},
                {value: '10:00', label: '10:00 AM'},
                {value: '11:00', label: '11:00 AM'},
                {value: '12:00', label: '12:00 PM'},
                {value: '13:00', label: '1:00 PM'},
                {value: '14:00', label: '2:00 PM'},
                {value: '15:00', label: '3:00 PM'},
                {value: '16:00', label: '4:00 PM'},
                {value: '17:00', label: '5:00 PM'},
                {value: '18:00', label: '6:00 PM'},
                {value: '19:00', label: '7:00 PM'},
                {value: '20:00', label: '8:00 PM'}
            ],
            selectedTimeSlots: [],
            selectedDates: [],
            blockedDates: [],
            isRecurring: false,
            links: {
                selectedTimeSlots: '${ $.provider }:${ $.dataScope }.selected_time_slots',
                blockedDates: '${ $.provider }:${ $.dataScope }.block_dates',
                isRecurring: '${ $.provider }:${ $.dataScope }.recurring'
            },
            listens: {
                isRecurring: 'onRecurringChange'
            }
        },

        /**
         * Initialize observable properties
         */
        initObservable: function () {
            this._super()
                .observe([
                    'selectedTimeSlots',
                    'selectedDates',
                    'blockedDates',
                    'isRecurring'
                ]);

            return this;
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            // Convert stored JSON data to arrays when component loads
            this.loadStoredData();

            return this;
        },

        /**
         * Load stored data after component is loaded
         */
        loadStoredData: function() {
            var self = this;
            
            // Wait for DOM to be ready
            setTimeout(function() {
                // Load and parse time slots
                var storedTimeSlots = self.selectedTimeSlots();
                if (typeof storedTimeSlots === 'string') {
                    try {
                        self.selectedTimeSlots(JSON.parse(storedTimeSlots));
                    } catch (e) {
                        self.selectedTimeSlots([]);
                    }
                }
                
                // Load and parse blocked dates
                var storedBlockedDates = self.blockedDates();
                if (typeof storedBlockedDates === 'string') {
                    try {
                        self.blockedDates(JSON.parse(storedBlockedDates));
                    } catch (e) {
                        self.blockedDates([]);
                    }
                }
                
                // Initialize calendars after data is loaded
                self.initDatePickers();
            }, 500);
        },

        /**
         * Initialize date pickers
         */
        initDatePickers: function() {
            var self = this;
            
            // Initialize event date picker
            $('#event-date-picker').calendar({
                dateFormat: 'yyyy-MM-dd',
                showsTime: false,
                buttonText: $t('Select Date'),
                onSelect: function(date) {
                    self.onDateSelect(date);
                }
            });
            
            // Initialize blocked date picker
            $('#blocked-dates-picker').calendar({
                dateFormat: 'yyyy-MM-dd',
                showsTime: false,
                buttonText: $t('Select Date to Block'),
                onSelect: function(date) {
                    self.onBlockedDateSelect(date);
                }
            });
        },

        /**
         * Handle date selection
         */
        onDateSelect: function(date) {
            var dates = this.selectedDates();
            if (!_.contains(dates, date)) {
                dates.push(date);
                this.selectedDates(dates);
            }
        },

        /**
         * Handle blocked date selection
         */
        onBlockedDateSelect: function(date) {
            var dates = this.blockedDates();
            if (!_.contains(dates, date)) {
                dates.push(date);
                this.blockedDates(dates);
                
                // Update the hidden input for block_dates
                this.updateBlockedDatesField();
            }
        },

        /**
         * Update blocked dates field with JSON data
         */
        updateBlockedDatesField: function() {
            var blockDatesJson = JSON.stringify(this.blockedDates());
            this.source.set(this.dataScope + '.block_dates', blockDatesJson);
        },

        /**
         * Toggle time slot selection
         */
        toggleTimeSlot: function(timeSlot) {
            var slots = this.selectedTimeSlots();
            var index = _.indexOf(slots, timeSlot.value);
            
            if (index === -1) {
                slots.push(timeSlot.value);
            } else {
                slots.splice(index, 1);
            }
            
            this.selectedTimeSlots(slots);
            
            // Update the hidden input for selected_time_slots
            var slotsJson = JSON.stringify(slots);
            this.source.set(this.dataScope + '.selected_time_slots', slotsJson);
            
            // Update the time_slots field too (for backward compatibility)
            this.source.set(this.dataScope + '.time_slots', slotsJson);
        },

        /**
         * Check if a time slot is selected
         */
        isTimeSlotSelected: function(timeSlot) {
            return _.contains(this.selectedTimeSlots(), timeSlot.value);
        },

        /**
         * Remove a date from selected dates
         */
        removeDate: function(date) {
            var dates = this.selectedDates();
            var index = _.indexOf(dates, date);
            
            if (index !== -1) {
                dates.splice(index, 1);
                this.selectedDates(dates);
            }
        },

        /**
         * Remove a date from blocked dates
         */
        removeBlockedDate: function(date) {
            var dates = this.blockedDates();
            var index = _.indexOf(dates, date);
            
            if (index !== -1) {
                dates.splice(index, 1);
                this.blockedDates(dates);
                this.updateBlockedDatesField();
            }
        },

        /**
         * Handler for recurring checkbox change
         */
        onRecurringChange: function(isRecurring) {
            // Convert to boolean
            this.isRecurring(Boolean(parseInt(isRecurring)));
        },

        /**
         * Get formatted date
         */
        formatDate: function(dateStr) {
            var date = new Date(dateStr);
            return date.toLocaleDateString();
        }
    });
});