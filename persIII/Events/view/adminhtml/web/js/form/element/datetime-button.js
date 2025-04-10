define([
    'Magento_Ui/js/form/components/button',
    'jquery',
    'Vishal_Events/js/event/datetime-modal'
], function (Button, $) {
    'use strict';

    return Button.extend({
        defaults: {
            elementTmpl: 'Vishal_Events/form/element/datetime-button',
            modalWidget: null
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            // Initialize modal widget
            var self = this;
            
            $(document).ready(function () {
                self.initModal();
            });
            
            return this;
        },
        
        /**
         * Initialize the modal widget
         */
        initModal: function () {
            var self = this;
            
            // Initialize the modal widget
            $('#event-datetime-modal').datetimeModal({
                triggerButtonSelector: '#add-event-datetime'
            });
            
            // Handle save event
            $(document).on('datetimeModalSave', function (event, data) {
                self.processModalData(data);
            });
        },
        
        /**
         * Process data from the modal
         * 
         * @param {Object} data - Form data from the modal
         */
        processModalData: function (data) {
            var formData = this.source.get('data');
            
            // Update form data with modal values
            formData.start_date = data.start_date;
            formData.repeat = data.repeats;
            
            if (data.repeats !== 'once') {
                formData.recurring = 1;
            } else {
                formData.recurring = 0;
            }
            
            // Process time slots
            var timeSlots = [];
            
            if (data.time_slots && data.time_slots.length) {
                data.time_slots.forEach(function (slot) {
                    timeSlots.push(slot.start_time + '-' + slot.end_time);
                });
            }
            
            formData.time_slots = JSON.stringify(timeSlots);
            formData.selected_time_slots = formData.time_slots;
            
            // Update UI values
            this.source.set('data', formData);
        },
        
        /**
         * Trigger the modal
         */
        trigger: function () {
            $('#event-datetime-modal').trigger('openModal');
        }
    });
});
