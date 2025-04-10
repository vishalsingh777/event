define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'mage/calendar'
], function ($, _, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            dateFormat: 'yyyy-MM-dd',
            selectedDates: [],
            elementTmpl: 'Vishal_Events/form/element/multidate',
            listens: {
                value: 'onValueChange'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            this.selectedDates = [];
            
            // Parse initial value
            this.onValueChange(this.value());
            
            return this;
        },
        
        /**
         * Initialization of the datepicker widget
         */
        initDatepicker: function (element) {
            var self = this;
            
            $(element).calendar({
                dateFormat: this.dateFormat,
                showButtonPanel: true,
                showOn: 'both',
                buttonText: 'Select Date',
                closeText: 'Done',
                beforeShowDay: function (date) {
                    var dateStr = $.datepicker.formatDate(self.dateFormat, date);
                    
                    // Check if date is in selected dates
                    var isSelected = _.contains(self.selectedDates, dateStr);
                    
                    return [true, isSelected ? 'ui-state-active' : ''];
                },
                onSelect: function (dateText) {
                    self.toggleDate(dateText);
                }
            });
        },
        
        /**
         * Handler for value change
         */
        onValueChange: function (value) {
            if (value) {
                try {
                    // Try to parse as JSON
                    if (typeof value === 'string' && value.indexOf('[') === 0) {
                        this.selectedDates = JSON.parse(value);
                    } else {
                        // Try to parse as comma-separated or line-separated string
                        this.selectedDates = value.split(/[,\n]/).map(function (item) {
                            return item.trim();
                        }).filter(function (item) {
                            return item.length > 0;
                        });
                    }
                } catch (e) {
                    console.error('Error parsing dates:', e);
                    this.selectedDates = [];
                }
            } else {
                this.selectedDates = [];
            }
            
            // Update UI if calendar is initialized
            this.updateCalendarUI();
        },
        
        /**
         * Update calendar UI to reflect selected dates
         */
        updateCalendarUI: function () {
            var element = $('#' + this.uid);
            if (element.hasClass('hasDatepicker')) {
                element.datepicker('refresh');
            }
        },
        
        /**
         * Toggle date selection
         */
        toggleDate: function (dateText) {
            var dates = this.selectedDates.slice();
            var index = _.indexOf(dates, dateText);
            
            if (index === -1) {
                // Add date
                dates.push(dateText);
            } else {
                // Remove date
                dates.splice(index, 1);
            }
            
            this.selectedDates = dates;
            
            // Update value
            this.value(JSON.stringify(dates));
            
            // Update UI
            this.updateCalendarUI();
            
            // Update the visual list
            this.updateVisualList();
        },
        
        /**
         * Update visual list of selected dates
         */
        updateVisualList: function () {
            var list = $('#' + this.uid + '_selected_dates');
            if (list.length) {
                list.empty();
                
                if (this.selectedDates.length) {
                    _.each(this.selectedDates, function (date) {
                        var dateObj = new Date(date);
                        var formattedDate = dateObj.toLocaleDateString();
                        
                        var item = $('<div class="selected-date-item"></div>');
                        var dateText = $('<span></span>').text(formattedDate);
                        var removeBtn = $('<button type="button" class="action-remove"></button>')
                            .text('Remove')
                            .on('click', this.removeDate.bind(this, date));
                        
                        item.append(dateText).append(removeBtn);
                        list.append(item);
                    }, this);
                } else {
                    list.append('<div class="empty-dates-message">No dates selected</div>');
                }
            }
        },
        
        /**
         * Initialize element
         */
        initElement: function (element) {
            var self = this;
            this._super();
            
            // Initialize datepicker after element is rendered
            setTimeout(function () {
                self.initDatepicker(element);
                self.updateVisualList();
            }, 100);
        },
        
        /**
         * Remove date
         */
        removeDate: function (date) {
            var dates = this.selectedDates.slice();
            var index = _.indexOf(dates, date);
            
            if (index !== -1) {
                dates.splice(index, 1);
                this.selectedDates = dates;
                this.value(JSON.stringify(dates));
                this.updateCalendarUI();
                this.updateVisualList();
            }
        }
    });
});