require([
    'jquery',
    'mage/calendar'
], function ($) {
    'use strict';

    $(document).ready(function() {
        // Initialize datepicker on the field
        $('#block_date_picker').calendar({
            dateFormat: 'yyyy-MM-dd',
            showButtonPanel: true,
            changeMonth: true,
            changeYear: true,
            onSelect: function(dateText) {
                addBlockedDate(dateText);
            }
        });

        // Function to add a blocked date
        function addBlockedDate(date) {
            var blockedDatesField = $('#block_dates');
            var currentDates = blockedDatesField.val();
            
            // Try to parse as JSON if it starts with [
            var blockedDates = [];
            if (currentDates) {
                if (currentDates.indexOf('[') === 0) {
                    try {
                        blockedDates = JSON.parse(currentDates);
                    } catch (e) {
                        // If JSON parsing fails, treat as comma-separated list
                        blockedDates = currentDates.split(',').map(function(item) {
                            return item.trim();
                        }).filter(function(item) {
                            return item.length > 0;
                        });
                    }
                } else {
                    // Split by comma or newline
                    blockedDates = currentDates.split(/[,\n]/).map(function(item) {
                        return item.trim();
                    }).filter(function(item) {
                        return item.length > 0;
                    });
                }
            }

            // Don't add duplicate dates
            if (blockedDates.indexOf(date) === -1) {
                blockedDates.push(date);
            }
            
            // Update the field value as JSON
            blockedDatesField.val(JSON.stringify(blockedDates));
            
            // Update the visual display of blocked dates
            updateBlockedDatesList(blockedDates);
        }
        
        // Function to display the blocked dates list
        function updateBlockedDatesList(dates) {
            var container = $('#blocked_dates_list');
            if (!container.length) {
                // Create container if it doesn't exist
                container = $('<div id="blocked_dates_list" class="blocked-dates-list"></div>');
                $('#block_dates').after(container);
            }
            
            container.empty();
            
            if (dates && dates.length) {
                var title = $('<div class="blocked-dates-title">Selected Blocked Dates:</div>');
                container.append(title);
                
                dates.forEach(function(date) {
                    var item = $('<div class="blocked-date-item"></div>');
                    var dateText = $('<span></span>').text(formatDate(date));
                    var removeBtn = $('<button type="button" class="action-remove">Remove</button>');
                    
                    removeBtn.on('click', function() {
                        removeBlockedDate(date);
                    });
                    
                    item.append(dateText).append(removeBtn);
                    container.append(item);
                });
            } else {
                container.append('<div class="empty-dates-message">No dates selected</div>');
            }
        }
        
        // Function to remove a blocked date
        function removeBlockedDate(date) {
            var blockedDatesField = $('#block_dates');
            var currentDates = blockedDatesField.val();
            
            // Parse current dates
            var blockedDates = [];
            try {
                if (currentDates.indexOf('[') === 0) {
                    blockedDates = JSON.parse(currentDates);
                } else {
                    blockedDates = currentDates.split(/[,\n]/).map(function(item) {
                        return item.trim();
                    }).filter(function(item) {
                        return item.length > 0;
                    });
                }
            } catch (e) {
                console.error('Error parsing dates:', e);
                return;
            }
            
            // Remove the date
            var index = blockedDates.indexOf(date);
            if (index !== -1) {
                blockedDates.splice(index, 1);
                
                // Update the field value
                blockedDatesField.val(JSON.stringify(blockedDates));
                
                // Update the visual display
                updateBlockedDatesList(blockedDates);
            }
        }
        
        // Format date for display
        function formatDate(dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString();
        }
        
        // Initialize the blocked dates list on page load
        $(document).on('contentUpdated', function() {
            var blockedDatesField = $('#block_dates');
            if (blockedDatesField.length) {
                var currentDates = blockedDatesField.val();
                
                // Try to parse dates
                var blockedDates = [];
                try {
                    if (currentDates && currentDates.indexOf('[') === 0) {
                        blockedDates = JSON.parse(currentDates);
                    } else if (currentDates) {
                        blockedDates = currentDates.split(/[,\n]/).map(function(item) {
                            return item.trim();
                        }).filter(function(item) {
                            return item.length > 0;
                        });
                    }
                    
                    // Update the visual display
                    updateBlockedDatesList(blockedDates);
                } catch (e) {
                    console.error('Error parsing dates:', e);
                }
            }
        });
        
        // Initialize on load
        var blockedDatesField = $('#block_dates');
        if (blockedDatesField.length) {
            var currentDates = blockedDatesField.val();
            
            // Try to parse dates
            var blockedDates = [];
            try {
                if (currentDates && currentDates.indexOf('[') === 0) {
                    blockedDates = JSON.parse(currentDates);
                } else if (currentDates) {
                    blockedDates = currentDates.split(/[,\n]/).map(function(item) {
                        return item.trim();
                    }).filter(function(item) {
                        return item.length > 0;
                    });
                }
                
                // Update the visual display
                updateBlockedDatesList(blockedDates);
            } catch (e) {
                console.error('Error parsing dates:', e);
            }
        }
    });
});