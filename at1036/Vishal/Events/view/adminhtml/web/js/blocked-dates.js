require([
    'jquery',
    'mage/calendar'
], function ($) {
    'use strict';

    $(document).ready(function() {
        // Function to initialize the blocked dates functionality
        function initBlockedDates() {
            // Create a container for the blocked dates list if it doesn't exist
            if ($('#blocked_dates_list').length === 0) {
                var container = $('<div id="blocked_dates_list" class="blocked-dates-list"></div>');
                $('.field-block_dates textarea').after(container);
            }
            
            // Parse and display existing blocked dates
            displayBlockedDates();
            
            // Set up event handlers
            setupEventHandlers();
        }
        
        // Function to set up event handlers
        function setupEventHandlers() {
            // Handle date picker change
            $(document).on('change', 'input[name="block_date_picker"]', function() {
                var date = $(this).val();
                if (date) {
                    addBlockedDate(date);
                    // Clear the date picker
                    $(this).val('');
                }
            });
            
            // Handle textarea change (manual entry)
            $(document).on('change', 'textarea[name="block_dates"]', function() {
                displayBlockedDates();
            });
        }
        
        // Function to display blocked dates from the textarea
        function displayBlockedDates() {
            var textarea = $('textarea[name="block_dates"]');
            if (!textarea.length) return;
            
            var text = textarea.val();
            var dates = [];
            
            // Try to parse as JSON first
            if (text.indexOf('[') === 0) {
                try {
                    dates = JSON.parse(text);
                } catch (e) {
                    // If not valid JSON, parse as text
                    dates = text.split(/[\n,]/).map(function(date) {
                        return date.trim();
                    }).filter(function(date) {
                        return date.length > 0;
                    });
                }
            } else if (text) {
                // Parse as newline or comma-separated text
                dates = text.split(/[\n,]/).map(function(date) {
                    return date.trim();
                }).filter(function(date) {
                    return date.length > 0;
                });
            }
            
            // Display the dates
            updateBlockedDatesList(dates);
        }
        
        // Function to add a blocked date
        function addBlockedDate(date) {
            var textarea = $('textarea[name="block_dates"]');
            if (!textarea.length) return;
            
            var text = textarea.val();
            var dates = [];
            
            // Try to parse existing dates
            if (text.indexOf('[') === 0) {
                try {
                    dates = JSON.parse(text);
                } catch (e) {
                    // If not valid JSON, parse as text
                    dates = text.split(/[\n,]/).map(function(date) {
                        return date.trim();
                    }).filter(function(date) {
                        return date.length > 0;
                    });
                }
            } else if (text) {
                // Parse as newline or comma-separated text
                dates = text.split(/[\n,]/).map(function(date) {
                    return date.trim();
                }).filter(function(date) {
                    return date.length > 0;
                });
            }
            
            // Add the new date if not already in the list
            if (dates.indexOf(date) === -1) {
                dates.push(date);
            }
            
            // Update the textarea with JSON format
            textarea.val(JSON.stringify(dates));
            
            // Update the display
            updateBlockedDatesList(dates);
        }
        
        // Function to remove a blocked date
        function removeBlockedDate(date) {
            var textarea = $('textarea[name="block_dates"]');
            if (!textarea.length) return;
            
            var text = textarea.val();
            var dates = [];
            
            // Try to parse existing dates
            if (text.indexOf('[') === 0) {
                try {
                    dates = JSON.parse(text);
                } catch (e) {
                    return; // Invalid JSON, do nothing
                }
            } else {
                return; // Invalid format, do nothing
            }
            
            // Remove the date
            var index = dates.indexOf(date);
            if (index !== -1) {
                dates.splice(index, 1);
                
                // Update the textarea with JSON format
                textarea.val(JSON.stringify(dates));
                
                // Update the display
                updateBlockedDatesList(dates);
            }
        }
        
        // Function to update the blocked dates list display
        function updateBlockedDatesList(dates) {
            var container = $('#blocked_dates_list');
            if (!container.length) return;
            
            container.empty();
            
            if (dates && dates.length) {
                var title = $('<div class="blocked-dates-title">Currently Blocked Dates:</div>');
                container.append(title);
                
                var list = $('<div class="blocked-dates-items"></div>');
                container.append(list);
                
                dates.forEach(function(date) {
                    // Format the date for display
                    var formattedDate;
                    try {
                        var dateObj = new Date(date);
                        formattedDate = dateObj.toLocaleDateString();
                    } catch (e) {
                        formattedDate = date; // Use as is if parsing fails
                    }
                    
                    var item = $('<div class="blocked-date-item"></div>');
                    var dateText = $('<span class="date-text"></span>').text(formattedDate);
                    var removeBtn = $('<button type="button" class="action-remove">Remove</button>');
                    
                    removeBtn.on('click', function() {
                        removeBlockedDate(date);
                    });
                    
                    item.append(dateText).append(removeBtn);
                    list.append(item);
                });
            } else {
                container.append('<div class="empty-dates-message">No dates selected</div>');
            }
        }
        
        // Initialize with a slight delay to ensure the DOM is fully loaded
        setTimeout(initBlockedDates, 500);
        
        // Also initialize on content updates (for when fields are dynamically loaded)
        $(document).on('contentUpdated', function() {
            initBlockedDates();
        });
    });
});