define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/calendar',
    'mage/translate'
], function ($, modal, calendar, $t) {
    'use strict';
    
    $.widget('vishal.datetimeModal', {
        options: {
            modalSelector: '#event-datetime-modal',
            triggerButtonSelector: '#add-event-datetime',
            startDateFieldSelector: '#modal-start-date',
            repeatsFieldSelector: '#modal-repeats',
            timeModeSelectorSelector: '.time-mode-tab',
            timeSlotsContainerSelector: '.time-slots-container',
            timeSlotRowSelector: '.time-slot-row',
            addTimeSlotSelector: '#add-time-slot',
            removeTimeSlotSelector: '.action-remove',
            saveButtonSelector: '.action-save',
            cancelButtonSelector: '.action-cancel',
            timeInputSelector: 'input[type="text"].validate-time',
            singleTimeModeSelector: '.single-time-tab',
            multipleTimeModeSelector: '.multiple-times-tab',
            modalOptions: {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: $.mage.__('Add dates and times'),
                buttons: []
            }
        },
        
        /**
         * Widget initialization
         * @private
         */
        _create: function() {
            this.modalElement = $(this.options.modalSelector);
            this.triggerButton = $(this.options.triggerButtonSelector);
            
            this._initModal();
            this._initDatepicker();
            this._bindEvents();
        },
        
        /**
         * Initialize modal dialog
         * @private
         */
        _initModal: function() {
            var self = this;
            
            this.modal = this.modalElement.modal(this.options.modalOptions);
            
            // Remove default modal buttons (we'll use our own in the template)
            this.modal.find('.modal-footer').hide();
        },
        
        /**
         * Initialize datepicker
         * @private
         */
        _initDatepicker: function() {
            $(this.options.startDateFieldSelector).calendar({
                dateFormat: 'mm/dd/yyyy',
                showsTime: false,
                buttonText: $t('Select Date')
            });
        },
        
        /**
         * Bind widget events
         * @private
         */
        _bindEvents: function() {
            var self = this;
            
            // Open modal on trigger button click
            this.triggerButton.on('click', function(e) {
                e.preventDefault();
                self.openModal();
            });
            
            // Time mode tabs
            this.modalElement.on('click', this.options.timeModeSelectorSelector, function() {
                self._switchTimeMode($(this));
            });
            
            // Add time slot
            this.modalElement.on('click', this.options.addTimeSlotSelector, function() {
                self._addTimeSlot();
            });
            
            // Remove time slot
            this.modalElement.on('click', this.options.removeTimeSlotSelector, function() {
                self._removeTimeSlot($(this).closest(self.options.timeSlotRowSelector));
            });
            
            // Save button
            this.modalElement.on('click', this.options.saveButtonSelector, function() {
                self.saveAndClose();
            });
            
            // Cancel button
            this.modalElement.on('click', this.options.cancelButtonSelector, function() {
                self.cancel();
            });
            
            // Time input change - calculate duration and validate
            this.modalElement.on('change', this.options.timeInputSelector, function() {
                var $row = $(this).closest(self.options.timeSlotRowSelector);
                self._calculateDuration($row);
                self._validateTimeSlots();
            });
        },
        
        /**
         * Switch between single and multiple time modes
         * @private
         * @param {jQuery} $tab - The clicked tab
         */
        _switchTimeMode: function($tab) {
            var mode = $tab.data('mode');
            
            // Update active tab
            this.modalElement.find(this.options.timeModeSelectorSelector).removeClass('active');
            $tab.addClass('active');
            
            // Show/hide appropriate UI elements
            if (mode === 'single') {
                // Keep only one time slot in single mode
                var $firstRow = this.modalElement.find(this.options.timeSlotRowSelector).first();
                this.modalElement.find(this.options.timeSlotRowSelector).not($firstRow).remove();
                this.modalElement.find(this.options.addTimeSlotSelector).parent().hide();
            } else {
                // Show add button in multiple mode
                this.modalElement.find(this.options.addTimeSlotSelector).parent().show();
            }
        },
        
        /**
         * Add a new time slot row
         * @private
         */
        _addTimeSlot: function() {
            var $container = this.modalElement.find(this.options.timeSlotsContainerSelector);
            var $lastRow = $container.find(this.options.timeSlotRowSelector).last();
            var index = $container.find(this.options.timeSlotRowSelector).length;
            
            // Clone the last row and reset error messages
            var $newRow = $lastRow.clone();
            $newRow.find('.error-message').hide();
            
            // Update IDs and names
            $newRow.find('input, select').each(function() {
                var name = $(this).attr('name');
                var id = $(this).attr('id');
                
                if (name) {
                    name = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', name);
                }
                
                if (id) {
                    id = id.replace(/-\d+$/, '-' + index);
                    $(this).attr('id', id);
                }
            });
            
            // Insert before the add button
            $newRow.insertBefore($container.find('.add-time-slot-container'));
            
            // Validate the time slots
            this._validateTimeSlots();
        },
        
        /**
         * Remove a time slot row
         * @private
         * @param {jQuery} $row - The row to remove
         */
        _removeTimeSlot: function($row) {
            var $rows = this.modalElement.find(this.options.timeSlotRowSelector);
            
            // Don't remove the last row
            if ($rows.length <= 1) {
                return;
            }
            
            $row.remove();
            
            // Re-index the rows
            this._reindexTimeSlots();
            
            // Validate the time slots
            this._validateTimeSlots();
        },
        
        /**
         * Re-index time slot rows after removal
         * @private
         */
        _reindexTimeSlots: function() {
            var self = this;
            
            this.modalElement.find(this.options.timeSlotRowSelector).each(function(index) {
                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    var id = $(this).attr('id');
                    
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', name);
                    }
                    
                    if (id) {
                        id = id.replace(/-\d+$/, '-' + index);
                        $(this).attr('id', id);
                    }
                });
            });
        },
        
        /**
         * Calculate time duration for display
         * @private
         * @param {jQuery} $row - The time slot row
         */
        _calculateDuration: function($row) {
            var $startTime = $row.find('input[name$="[start_time]"]');
            var $endTime = $row.find('input[name$="[end_time]"]');
            var $duration = $row.find('.time-duration');
            
            var startTime = $startTime.val();
            var endTime = $endTime.val();
            
            if (startTime && endTime) {
                try {
                    // Parse times to calculate duration
                    var startParts = startTime.split(':');
                    var endParts = endTime.split(':');
                    
                    var startHour = parseInt(startParts[0]);
                    var startMinute = parseInt(startParts[1] || 0);
                    var endHour = parseInt(endParts[0]);
                    var endMinute = parseInt(endParts[1] || 0);
                    
                    // Calculate duration in minutes
                    var durationMinutes = (endHour * 60 + endMinute) - (startHour * 60 + startMinute);
                    
                    // Handle overnight durations
                    if (durationMinutes < 0) {
                        durationMinutes += 24 * 60;
                    }
                    
                    // Convert to hours and minutes
                    var durationHours = Math.floor(durationMinutes / 60);
                    var durationRemMinutes = durationMinutes % 60;
                    
                    // Format the duration text
                    var durationText = '(' + durationHours;
                    
                    if (durationHours === 1) {
                        durationText += ' hr';
                    } else {
                        durationText += ' hrs';
                    }
                    
                    if (durationRemMinutes > 0) {
                        durationText += ' ' + durationRemMinutes + ' min';
                    }
                    
                    durationText += ')';
                    
                    $duration.text(durationText);
                } catch (e) {
                    console.error('Error calculating duration:', e);
                    $duration.text('');
                }
            } else {
                $duration.text('');
            }
        },
        
        /**
         * Validate time slots for duplicates
         * @private
         */
        _validateTimeSlots: function() {
            var timeSlots = [];
            var duplicates = {};
            
            // Find duplicates
            this.modalElement.find(this.options.timeSlotRowSelector).each(function(index) {
                var $row = $(this);
                var startTime = $row.find('input[name$="[start_time]"]').val();
                var endTime = $row.find('input[name$="[end_time]"]').val();
                
                if (startTime && endTime) {
                    var timeSlot = startTime + '-' + endTime;
                    
                    if (timeSlots.indexOf(timeSlot) !== -1) {
                        duplicates[index] = true;
                    } else {
                        timeSlots.push(timeSlot);
                    }
                }
            });
            
            // Update UI for validation
            this.modalElement.find(this.options.timeSlotRowSelector).each(function(index) {
                var $row = $(this);
                var $errorMessages = $row.find('.error-message');
                
                if (duplicates[index]) {
                    $errorMessages.show();
                    $row.addClass('has-error');
                } else {
                    $errorMessages.hide();
                    $row.removeClass('has-error');
                }
            });
            
            return Object.keys(duplicates).length === 0;
        },
        
        /**
         * Open the modal
         */
        openModal: function() {
            this.modal.modal('openModal');
        },
        
        /**
         * Save data and close modal
         */
        saveAndClose: function() {
            // Validate before saving
            if (!this._validateTimeSlots()) {
                return;
            }
            
            // Get form data
            var data = {
                start_date: $(this.options.startDateFieldSelector).val(),
                repeats: $(this.options.repeatsFieldSelector).val(),
                time_mode: this.modalElement.find(this.options.timeModeSelectorSelector + '.active').data('mode'),
                time_slots: []
            };
            
            // Collect time slots
            this.modalElement.find(this.options.timeSlotRowSelector).each(function() {
                var startTime = $(this).find('input[name$="[start_time]"]').val();
                var endTime = $(this).find('input[name$="[end_time]"]').val();
                
                if (startTime && endTime) {
                    data.time_slots.push({
                        start_time: startTime,
                        end_time: endTime
                    });
                }
            });
            
            // Trigger event with the data
            this.element.trigger('datetimeModalSave', [data]);
            
            // Close modal
            this.modal.modal('closeModal');
        },
        
        /**
         * Cancel and close modal
         */
        cancel: function() {
            this.modal.modal('closeModal');
        }
    });
    
    return $.vishal.datetimeModal;
});