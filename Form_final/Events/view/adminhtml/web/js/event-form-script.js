/**
     * Initialize form validation
     */
    function initValidation() {
        // This would be connected to Magento's validation system
        // For demo purposes, we're just showing the error styling
    }/**
 * Event Form Enhancement Script
 * 
 * This script enhances the Magento UI components with modern styling and functionality
 * to match the reference design
 */

require([
    'jquery',
    'mage/translate',
    'domReady!'
], function ($, $t) {
    'use strict';

    /**
     * Initialize the custom form styling and behavior
     */
    function initEventFormStyling() {
        // Add custom classes to form containers
        addCustomClasses();   
        // Setup dynamic time slots functionality
        initDynamicTimeSlots();
        
        // Add error validation styling
        initValidation();
    }

    /**
     * Add custom classes for styling
     */
    function addCustomClasses() {
        // Add section headers
        $('[name="event_timezone"]').closest('.admin__field').before('<h2 class="section-title">' + $t('Date') + '</h2>');
        $('[name="date_time"]').before('<h2 class="section-title">' + $t('Time') + '</h2>');
        
        // Add time segment tabs
        const tabsHtml = `
            <div class="time-segment-tabs">
                <button type="button" class="time-segment-tab active" data-tab="single">${$t('Single time')}</button>
                <button type="button" class="time-segment-tab" data-tab="multiple">${$t('Multiple times')}</button>
            </div>
        `;
        $('[name="date_time"]').before(tabsHtml);
        
        // Add generate time slots button
        const generateSlotsHtml = `
            <button type="button" class="generate-slots-button">
                <span class="generate-slots-button-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <polyline points="19 12 12 19 5 12"></polyline>
                    </svg>
                </span>
                ${$t('Generate time slots')}
            </button>
        `;
        $('[name="date_time"]').before(generateSlotsHtml);
        
        // Style dynamic rows
        $('[name="date_time"]').addClass('time-slots-container');
        
        // Create a container for start_date and end_date fields and make them side by side
        arrangeDateFieldsSideBySide();
    }
    
    /**
     * Arrange start_date and end_date fields side by side
     */
    function arrangeDateFieldsSideBySide() {
        // Find the date fields - try various selectors to ensure compatibility
        var startDateField = $('input[id*="start_date"]').closest('.admin__field');
        var endDateField = $('input[id*="end_date"]').closest('.admin__field');
        
        console.log('Start date field found for side-by-side:', startDateField.length > 0);
        console.log('End date field found for side-by-side:', endDateField.length > 0);
        
        // Only proceed if both fields are found
        if (startDateField.length && endDateField.length) {
            // Create a container for the date fields if it doesn't exist
            if (!$('.date-fields-container').length) {
                startDateField.before('<div class="date-fields-container"></div>');
                
                // Move both fields into the container
                var container = $('.date-fields-container');
                container.append(startDateField);
                container.append(endDateField);
                
                // Add validation error container
                container.after('<div class="date-validation-error"></div>');
                
                console.log('Date fields arranged side by side');
            }
        } else {
            console.warn('Could not arrange date fields side by side - fields not found');
        }
    }

    /**
     * Initialize dynamic time slots functionality
     */
    function initDynamicTimeSlots() {
        // Transform the dynamic rows into our custom design
        $('[name="date_time"] [data-index="record"]').each(function() {
            transformTimeSlotRow($(this));
        });
        
        // Handle the "Add a time slot" button
        $('[name="date_time"] .admin__action-add').html(`
            <span class="add-icon">+</span>${$t('Add a time slot')}
        `).addClass('add-time-slot');
        
        // Handle generate time slots dropdown
        $('.generate-slots-button').on('click', function() {
            // Mock functionality - in real implementation, this would show a dropdown
            alert($t('This would show time slot generation options'));
        });
    }

    /**
     * Transform a dynamic row into our custom design
     */
    function transformTimeSlotRow(row) {
        // Add custom classes to time fields
        row.find('[name$="[time_start]"]').closest('.admin__field').addClass('time-field-container start-time');
        row.find('[name$="[time_end]"]').closest('.admin__field').addClass('time-field-container end-time');
        
        // Add error message for duplicate time slots
        row.find('.time-field-container').append('<div class="time-error-message">' + $t('Time slot is duplicated') + '</div>');
        
        // Style delete button
        row.find('.action-delete').html(`
            <svg class="delete-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
        `).addClass('delete-time-slot');
        
        // Add error icons to fields
        row.find('.admin__control-select').after(`
            <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        `);
        
        // Add error class to simulate validation
        row.find('.admin__control-select').addClass('error');
    }

    /**
     * Initialize date validation
     * 1. Start date and end date can't be the same
     * 2. End date should always be greater than start date
     */
    function initDateValidation() {
        // Keep the date validation as is - just for start_date and end_date fields
        console.log('Initializing date validation for start_date and end_date fields');
        
        // Wait for the DOM to be ready
        setTimeout(function() {
            // Try to find date fields with more specific selectors based on Magento's patterns
            var startDateField = $('input[id*="start_date"], input[name*="start_date"]').first();
            var endDateField = $('input[id*="end_date"], input[name*="end_date"]').first();
            
            console.log('Start date field found:', startDateField.length > 0, startDateField.attr('id'), startDateField.attr('name'));
            console.log('End date field found:', endDateField.length > 0, endDateField.attr('id'), endDateField.attr('name'));
            
            if (!startDateField.length || !endDateField.length) {
                // If we still can't find them, try another approach - look for datepickers
                var datepickers = $('.admin__control-text._has-datepicker');
                console.log('Found datepickers:', datepickers.length);
                
                if (datepickers.length >= 2) {
                    // Assume the first two datepickers are start and end date
                    startDateField = datepickers.eq(0);
                    endDateField = datepickers.eq(1);
                    console.log('Using fallback date fields');
                } else {
                    console.error('Could not find date fields for validation');
                    return;
                }
            }
            
            // Find or create error container
            var errorContainer = $('.date-validation-error');
            if (!errorContainer.length) {
                // Try to place it after the date-fields-container or after the end date field
                var targetElement = $('.date-fields-container').length ? 
                    $('.date-fields-container') : 
                    endDateField.closest('.admin__field');
                
                errorContainer = $('<div class="date-validation-error"></div>').insertAfter(targetElement);
            }
            
            // Define the validation function
            function validateDates() {
                var startDateVal = startDateField.val();
                var endDateVal = endDateField.val();
                
                console.log('Validating dates:', startDateVal, endDateVal);
                
                // Skip validation if either field is empty
                if (!startDateVal || !endDateVal) {
                    errorContainer.hide();
                    return true;
                }
                
                // Convert to Date objects
                var startDate = new Date(startDateVal);
                var endDate = new Date(endDateVal);
                
                // Check if the dates are valid
                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                    console.log('Invalid date format');
                    errorContainer.hide();
                    return true; // Can't validate
                }
                
                // Normalize dates to midnight for comparison
                startDate.setHours(0, 0, 0, 0);
                endDate.setHours(0, 0, 0, 0);
                
                console.log('Comparing dates:', startDate, endDate);
                
                // Clear previous validation
                startDateField.removeClass('validation-failed');
                endDateField.removeClass('validation-failed');
                errorContainer.hide();
                
                // Check if dates are the same
                if (startDate.getTime() === endDate.getTime()) {
                    endDateField.addClass('validation-failed');
                    errorContainer.text('Start date and end date cannot be the same').show();
                    console.log('Validation failed: Same dates');
                    return false;
                }
                
                // Check if end date is before start date
                if (endDate < startDate) {
                    endDateField.addClass('validation-failed');
                    errorContainer.text('End date must be greater than start date').show();
                    console.log('Validation failed: End date before start date');
                    return false;
                }
                
                console.log('Date validation passed');
                return true;
            }
            
            // Attach validation to date change events
            startDateField.add(endDateField).on('change blur', function() {
                console.log('Date field changed or blurred');
                validateDates();
            });
            
            // For Magento's datepicker, we need to handle calendar selection
            $(document).on('click', '.ui-datepicker-calendar td', function() {
                console.log('Calendar date clicked');
                setTimeout(validateDates, 100);
            });
            
            // Add validation to form submission
            var form = startDateField.closest('form');
            form.on('submit', function(e) {
                console.log('Form submission - validating dates');
                if (!validateDates()) {
                    console.log('Preventing form submission - date validation failed');
                    e.preventDefault();
                    return false;
                }
            });
            
            // Run initial validation
            setTimeout(validateDates, 500);
        }, 1000); // Delay to ensure DOM is ready
    }
    
    /**
     * Initialize recurring dependency
     * If 'recurring' value is 'yes', show "Add a time slot", otherwise hide it
     * Using your validated working code from the console
     */
    function initRecurringDependency() {
        // Wait for DOM to be fully loaded
        setTimeout(function() {
            console.log('Initializing recurring dependency with verified working code');
            
            // Using the exact code you confirmed works in the console
            // Listen for change on select element with name "recurring"
            $(document).on('change', 'select[name="recurring"]', function () {
                // Get the selected value from the dropdown
                var selectedValue = $(this).val();
                console.log('Recurring value changed to:', selectedValue);
                
                // Find the button with data-action="add_new_row"
                var $button = $('button[data-action="add_new_row"]');
                // Enable or disable the button based on the selected value
                if (selectedValue === '0') {
                    // Disable the button if value is '0'
                    $button.prop('disabled', true);
                    console.log('Disabled add time slot button');
                } else if (selectedValue === '1') {
                    // Enable the button if value is '1'
                    $button.prop('disabled', false);
                    console.log('Enabled add time slot button');
                }
                
                // Now handle the rows with data-repeat-index
                $('tr[data-repeat-index]').each(function() {
                    const index = parseInt($(this).attr('data-repeat-index'), 10);
                    
                    // Check if data-repeat-index is greater than 0 and hide the row
                    if (index > 0) {
                        $(this).hide();  // Hide the row
                        console.log('Hiding row with index:', index);
                    } else {
                        $(this).show();  // Ensure the row is visible if index is 0 or not present
                        console.log('Showing row with index:', index);
                    }
                });
                
                // Also update tab states
                if (selectedValue === '0') {
                    $('.time-segment-tab[data-tab="single"]').addClass('active');
                    $('.time-segment-tab[data-tab="multiple"]').removeClass('active');
                } else {
                    $('.time-segment-tab[data-tab="multiple"]').addClass('active');
                    $('.time-segment-tab[data-tab="single"]').removeClass('active');
                }
            });
            
            // Also trigger the change event on page load to set initial state
            setTimeout(function() {
                console.log('Triggering initial state');
                $('select[name="recurring"]').trigger('change');
            }, 500);
            
        }, 1000); // Delay to ensure DOM is ready
    }

    // Initialize everything when DOM is ready
    $(document).ready(function() {
        initEventFormStyling();
        console.log(111111);
        $(document).on('change', 'select[name="recurring"]', function () {
            // Get the selected value from the dropdown
            var selectedValue = $(this).val();
           console.log(selectedValue); 
            // Find the button with data-action="add_new_row"
            var $button = $('button[data-action="add_new_row"]');

            // Enable or disable the button based on the selected value
            if (selectedValue === '0') {
                // Disable the button if value is '0'
                $button.prop('disabled', true);
            } else if (selectedValue === '1') {
                // Enable the button if value is '1'
                $button.prop('disabled', false);
            }
            // Now handle the rows with data-repeat-index
            document.querySelectorAll('tr[data-repeat-index]').forEach(row => {
                const index = parseInt(row.getAttribute('data-repeat-index'), 10);
                
                // Check if data-repeat-index is greater than 0 and hide the row
                if (index > 0) {
                    row.hide();  // Hide the row
                } else {
                    row.show();  // Ensure the row is visible if index is 0 or not present
                }
            });
        });
    });
});