define([
    'jquery',
    'mage/translate',
    'mage/calendar',
    'mage/url'
], function ($, $t, calendar, urlBuilder) {
    'use strict';
    
    return function (config) {
        var eventId = config.eventId,
            isRecurring = config.isRecurring,
            registrationType = config.registrationType,
            startDate = config.startDate,
            endDate = config.endDate,
            timezone = config.timezone,
            repeatType = config.repeatType,
            customDays = config.customDays || []; // Add this line for custom days
            
        // Get base URL for AJAX requests    
        var BASE_URL = urlBuilder.build('');
        
        $(document).ready(function() {
            // Add mobile class for responsive handling
            if (window.innerWidth <= 767) {
                $('body').addClass('mobile-view');
            }

            if (registrationType != 0) {
                // Hide the minicart
                $('.minicart-wrapper').hide();
            }
            
            // Listen for resize events
            $(window).on('resize', function() {
                if (window.innerWidth <= 767) {
                    $('body').addClass('mobile-view');
                } else {
                    $('body').removeClass('mobile-view');
                }
                
                // Adjust modal position if open
                if ($('#registration-form-modal').is(':visible')) {
                    centerModalContent();
                }
            });
            
            // Time slot selection handling
            handleTimeSlotSelection();
            
            // Date selection handling for recurring events
            handleDateSelection();
            
            // Registration button handling
            setupRegistrationButtons();
            
            // Registration modal handling
            setupRegistrationModal();
            
            // Form validation
            setupFormValidation();
            
            // Update past time slots appearance
            updatePastTimeSlots();
            
            // Map link handling
            $('#show-map').on('click', function(e) {
                // For external map links, this will just follow the href
                // For modal maps, add custom functionality here
            });
            
            // Better scroll handling for mobile
            improveScrollingOnMobile();
            
            // Handle form submission with AJAX for "Register Only" type
            setupRegisterOnlySubmission();
            
            // Setup Stripe Payment Button
            setupStripePaymentButton();
        });
        
        /**
         * Extract time slot data from the radio button value
         * @param {string} radioValue - Radio button value containing time_start|time_end|date|index
         * @return {object} Object containing the parsed values
         */
        function parseTimeSlotValue(radioValue) {
            if (!radioValue) {
                return { 
                    index: '', 
                    timeStart: '', 
                    timeEnd: '', 
                    date: '' 
                };
            }
            
            var parts = radioValue.split('|');
            
            // Handle various formats
            if (parts.length >= 4) {
                // Full format: time_start|time_end|date|index
                return {
                    timeStart: parts[0],
                    timeEnd: parts[1],
                    date: parts[2],
                    index: parts[3]
                };
            } else if (parts.length === 3) {
                // time_start|time_end|date
                return {
                    timeStart: parts[0],
                    timeEnd: parts[1],
                    date: parts[2],
                    index: '0'
                };
            } else if (parts.length === 2) {
                // Possible time_start|time_end
                return {
                    timeStart: parts[0],
                    timeEnd: parts[1],
                    date: '',
                    index: '0'
                };
            } else {
                // Just index
                return {
                    timeStart: '',
                    timeEnd: '',
                    date: '',
                    index: parts[0]
                };
            }
        }
        
        /**
         * Handle time slot selection behavior
         */
        function handleTimeSlotSelection() {
            // Remove any existing handlers to prevent duplicates
            $(document).off('click', '.time-slot-option:not(.past)');
            $(document).off('change', '.time-slot-option input[type="radio"]');
            
            // Handle clicks on the entire time slot
            $(document).on('click', '.time-slot-option:not(.past)', function(e) {
                // Don't trigger if clicking directly on radio button (it will handle itself)
                if (e.target.type !== 'radio') {
                    var radio = $(this).find('input[type="radio"]');
                    radio.prop('checked', true);
                    radio.trigger('change');
                }
                
                // Update UI for all slots
                $('.time-slot-option').removeClass('selected');
                $(this).addClass('selected');
                
                // Hide any validation messages
                $('.time-slot-validation-message').hide();
            });
            
            // Handle radio button changes
            $(document).on('change', '.time-slot-option input[type="radio"]', function() {
                $('.time-slot-option').removeClass('selected');
                var selectedSlot = $(this).closest('.time-slot-option');
                selectedSlot.addClass('selected');
                
                // Parse the time slot value
                var radioValue = $(this).val();
                var slotData = parseTimeSlotValue(radioValue);
                
                // If no time data in radioValue, try to get from data attributes
                if (!slotData.timeStart || !slotData.timeEnd) {
                    slotData.timeStart = selectedSlot.data('start') || '';
                    slotData.timeEnd = selectedSlot.data('end') || '';
                    slotData.date = selectedSlot.data('date') || '';
                }
                
                // Update the hidden inputs
                $('#selected-time-slot').val(slotData.index);
                $('#selected-time-start').val(slotData.timeStart);
                $('#selected-time-end').val(slotData.timeEnd);
                if ($('#selected-date').length && slotData.date) {
                    $('#selected-date').val(slotData.date);
                }
                
                console.log('Selected time slot:', slotData);
            });
            
            // Initialize with pre-selected slot highlighted
            $('.time-slot-option input[type="radio"]:checked').trigger('change');
            
            // If only one time slot, make sure it's selected
            if ($('.time-slot-option').length === 1) {
                var singleSlot = $('.time-slot-option');
                singleSlot.addClass('selected');
                singleSlot.find('input[type="radio"]').prop('checked', true).trigger('change');
            }
        }
        
        /**
         * Handle date selection for recurring events
         */
        function handleDateSelection() {
            var dateSelector = $('#event-date-selector');
            var timeSlotContainer = $('#time-slots-container');
            
            if (dateSelector.length) {
                dateSelector.on('change', function() {
                    var selectedDate = $(this).val();
                    
                    // Show loading indicator
                    timeSlotContainer.html('<div class="loading-slots">' + $t('Loading time slots...') + '</div>');
                    
                    // Update time slots based on selected date
                    updateTimeSlotsForDate(selectedDate, repeatType);
                    
                    // Also update hidden input for forms
                    $('#selected-date').val(selectedDate);
                });
                
                // Set initial date value
                $('#selected-date').val(dateSelector.val());
            }
        }
        
        /**
         * Update time slots for the selected date
         * @param {string} selectedDate - Selected date in YYYY-MM-DD format
         * @param {string} repeatType - The repeat type (0=once, 1=daily, 2=weekly, 3=monthly, 4=custom)
         */
        function updateTimeSlotsForDate(selectedDate, repeatType) {
            var isLocalEnvironment = (
                window.location.hostname === 'localhost' || 
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname.includes('.local') ||
                window.location.hostname.includes('.test')
            );
            
            // Prepare the data with custom repeat type info if needed
            var data = {
                event_id: eventId,
                selected_date: selectedDate
            };
            
            // For custom repeat type, add the flag
            if (repeatType === '4') {
                data.custom_repeat = true;
                data.custom_days = customDays;
            }
            
            if(isLocalEnvironment){
                var endpoint = 'ajax/timeslots';
            } else {
                var endpoint = 'ajax/timeslots';
            }
            
            $.ajax({
                url: BASE_URL + endpoint,
                data: data,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success && response.slots) {
                        renderTimeSlots(response.slots, selectedDate);
                    } else {
                        // If no slots returned or error, use client-side approach
                        renderTimeSlotsFallback(selectedDate);
                    }
                },
                error: function() {
                    // If AJAX fails, use client-side approach
                    renderTimeSlotsFallback(selectedDate);
                }
            });
        }
        
        /**
         * Render time slots from AJAX response
         * @param {Array} slots - Time slots array from server
         * @param {string} selectedDate - Selected date
         */
        function renderTimeSlots(slots, selectedDate) {
            var timeSlotContainer = $('#time-slots-container');
            var timeSlotHtml = '';
            
            if (slots && slots.length > 0) {
                for (var i = 0; i < slots.length; i++) {
                    var slot = slots[i];
                    var isSelected = (i === 0 || slots.length === 1) ? 'selected' : '';
                    var isChecked = (i === 0 || slots.length === 1) ? 'checked' : '';
                    
                    // Create a value that includes time_start, time_end, date and index
                    var timeStart = slot.time_start || '';
                    var timeEnd = slot.time_end || '';
                    var radioValue = timeStart && timeEnd ? 
                        timeStart + '|' + timeEnd + '|' + selectedDate + '|' + slot.index :
                        slot.index;
                    
                    timeSlotHtml += '<label class="time-slot-option ' + isSelected + '" ' +
                        'data-date="' + selectedDate + '" ' +
                        'data-start="' + timeStart + '" ' +
                        'data-end="' + timeEnd + '">' +
                        '<input type="radio" name="event_time_slot" value="' + radioValue + '" ' + isChecked + '>' +
                        '<span class="time-slot-text">' + slot.formatted + '</span>' +
                        '</label>';
                }
            } else {
                timeSlotHtml = '<div class="no-slots-message">' + $t('No time slots available for this date.') + '</div>';
            }
            
            timeSlotContainer.html(timeSlotHtml);
            
            // Update selected time slot field when there are slots
            if (slots && slots.length > 0) {
                var firstSlot = slots[0];
                $('#selected-time-slot').val(firstSlot.index);
                
                if (firstSlot.time_start && firstSlot.time_end) {
                    $('#selected-time-start').val(firstSlot.time_start);
                    $('#selected-time-end').val(firstSlot.time_end);
                }
                
                $('#selected-date').val(selectedDate);
            } else {
                $('#selected-time-slot').val('');
                $('#selected-time-start').val('');
                $('#selected-time-end').val('');
            }
            
            // Reinitialize time slot selection handlers
            handleTimeSlotSelection();
            
            // Update past time slots
            updatePastTimeSlots();
        }
        
        /**
         * Client-side fallback for rendering time slots
         * This uses the same time slots from the initial page load but updates the date
         * @param {string} selectedDate - Selected date in YYYY-MM-DD format
         */
        function renderTimeSlotsFallback(selectedDate) {
            var timeSlotContainer = $('#time-slots-container');
            var existingSlots = $('.time-slot-option');
            var timeSlotHtml = '';
            
            // For custom repeat type, check if the selected date is allowed
            if (repeatType === '4' && customDays.length > 0) {
                var selectedDayOfWeek = new Date(selectedDate).getDay().toString();
                if (customDays.indexOf(selectedDayOfWeek) === -1) {
                    // This day is not in custom days
                    timeSlotHtml = '<div class="no-slots-message">' + $t('No time slots available for this date.') + '</div>';
                    timeSlotContainer.html(timeSlotHtml);
                    
                    $('#selected-time-slot').val('');
                    $('#selected-time-start').val('');
                    $('#selected-time-end').val('');
                    
                    return;
                }
            }
            
            // Format the selected date
            var selectedDateObj = new Date(selectedDate);
            var formattedDate = formatDate(selectedDateObj);
            
            if (existingSlots.length > 0) {
                existingSlots.each(function(index) {
                    var slot = $(this);
                    var radio = slot.find('input[type="radio"]');
                    var slotText = slot.find('.time-slot-text').text();
                    
                    // Extract the time part from the existing slot text
                    var timeParts = slotText.split('·');
                    var timeText = timeParts.length > 1 ? timeParts[1].trim() : slotText;
                    
                    // Create new formatted text with updated date
                    var newSlotText = formattedDate + ' · ' + timeText;
                    
                    // Check if the time contains timezone, if not add it
                    if (newSlotText.indexOf(timezone) === -1) {
                        newSlotText += ' ' + timezone;
                    }
                    
                    var isSelected = (index === 0 || existingSlots.length === 1) ? 'selected' : '';
                    var isChecked = (index === 0 || existingSlots.length === 1) ? 'checked' : '';
                    
                    // Get time data from data attributes
                    var timeStart = slot.data('start') || '';
                    var timeEnd = slot.data('end') || '';
                    
                    // Create a value that includes time_start, time_end, date and index
                    var radioValue = timeStart && timeEnd ? 
                        timeStart + '|' + timeEnd + '|' + selectedDate + '|' + index :
                        index;
                    
                    timeSlotHtml += '<label class="time-slot-option ' + isSelected + '" ' +
                        'data-date="' + selectedDate + '" ' +
                        'data-start="' + timeStart + '" ' +
                        'data-end="' + timeEnd + '">' +
                        '<input type="radio" name="event_time_slot" value="' + radioValue + '" ' + isChecked + '>' +
                        '<span class="time-slot-text">' + newSlotText + '</span>' +
                        '</label>';
                });
                
                // Update selected time slot field with first slot
                var firstSlot = existingSlots.eq(0);
                $('#selected-time-slot').val('0');
                $('#selected-time-start').val(firstSlot.data('start') || '');
                $('#selected-time-end').val(firstSlot.data('end') || '');
                $('#selected-date').val(selectedDate);
            } else {
                timeSlotHtml = '<div class="no-slots-message">' + $t('No time slots available for this date.') + '</div>';
                $('#selected-time-slot').val('');
                $('#selected-time-start').val('');
                $('#selected-time-end').val('');
            }
            
            timeSlotContainer.html(timeSlotHtml);
            
            // Reinitialize time slot selection handlers
            handleTimeSlotSelection();
            
            // Update past time slots
            updatePastTimeSlots();
        }
        
        /**
         * Setup registration buttons
         */
        function setupRegistrationButtons() {
            var registerPayBtn = $('#register-pay-button');
            
            // Time slot validation
            function validateTimeSlotSelection() {
                var selectedSlot = $('input[name="event_time_slot"]:checked');
                var validationMessage;
                
                if ($('.time-slot-validation-message').length === 0) {
                    validationMessage = $('<div class="time-slot-validation-message">' + $t('Please select a time slot.') + '</div>')
                        .insertAfter('#time-slots-container');
                } else {
                    validationMessage = $('.time-slot-validation-message');
                }
                
                if (selectedSlot.length === 0) {
                    validationMessage.show();
                    
                    // Scroll to time slots
                    $('html, body').animate({
                        scrollTop: $('.time-slots-section').offset().top - 20
                    }, 300);
                    
                    return false;
                }
                
                validationMessage.hide();
                return true;
            }
            
            // Get selected time slot data
            function getSelectedTimeSlotData() {
                var selectedSlot = $('.time-slot-option.selected');
                if (selectedSlot.length === 0) {
                    return {
                        index: '',
                        timeStart: '',
                        timeEnd: '',
                        date: ''
                    };
                }
                
                var radio = selectedSlot.find('input[type="radio"]');
                var radioValue = radio.val();
                var slotData = parseTimeSlotValue(radioValue);
                
                // If no time data in radioValue, try to get from data attributes
                if (!slotData.timeStart || !slotData.timeEnd) {
                    slotData.timeStart = selectedSlot.data('start') || '';
                    slotData.timeEnd = selectedSlot.data('end') || '';
                    slotData.date = selectedSlot.data('date') || '';
                }
                
                return slotData;
            }
            
            // Add to cart with selected time slot
            if (registerPayBtn.length) {
                registerPayBtn.on('click', function(e) {
                    // Check if this is a button (not a link) or if it doesn't have an href attribute
                    if ($(this).is('button') || !$(this).attr('href')) {
                        e.preventDefault();
                        
                        // For button type - validation only, opening modal or other action
                        if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                            return false;
                        }
                        
                        // Handle button action (e.g., open modal)
                        if (typeof openModal === 'function') {
                            // Get selected time slot data
                            var slotData = getSelectedTimeSlotData();
                            
                            // Set form values
                            $('#registration-form-title').text($t('Event Registration'));
                            $('#registration-type').val('0'); // Paid registration
                            $('#selected-time-slot').val(slotData.index);
                            $('#selected-time-start').val(slotData.timeStart);
                            $('#selected-time-end').val(slotData.timeEnd);
                            $('#selected-date').val(slotData.date || getSelectedDate());
                            
                            // Update button visibility
                            if (window.stripeEnabled) {
                                $('#stripe-payment-btn').show();
                                $('#standard-submit-btn').hide();
                            } else {
                                $('#stripe-payment-btn').hide();
                                $('#standard-submit-btn').show();
                            }
                            
                            // Open the modal
                            openModal();
                        }
                        
                        return false;
                    }
                    
                    // For link-based button - redirect with parameters
                    if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Get current URL
                    var url = $(this).attr('href');
                    
                    // Make sure url is defined
                    if (!url) {
                        console.error('Button href attribute is undefined');
                        return true; // Let default behavior happen
                    }
                    
                    // Get selected time slot data
                    var slotData = getSelectedTimeSlotData();
                    var selectedDate = $('#event-date-selector').length ? $('#event-date-selector').val() : slotData.date;
                    
                    // Build parameters
                    var params = {};
                    
                    // Only add parameters if they have values
                    if (slotData.index) {
                        params.selected_time_slot = slotData.index;
                    }
                    
                    if (slotData.timeStart) {
                        params.selected_time_start = slotData.timeStart;
                    }
                    
                    if (slotData.timeEnd) {
                        params.selected_time_end = slotData.timeEnd;
                    }
                    
                    if (selectedDate) {
                        params.selected_date = selectedDate;
                    }
                    
                    // Check if we need to add parameters
                    if (Object.keys(params).length === 0) {
                        // No parameters to add, use the original URL
                        return true;
                    }
                    
                    // Handle URL modification
                    if (url.indexOf('?') === -1) {
                        url += '?';
                    } else {
                        url += '&';
                    }
                    
                    // Add parameters to URL
                    var paramStrings = [];
                    for (var key in params) {
                        if (params.hasOwnProperty(key) && params[key]) {
                            paramStrings.push(key + '=' + encodeURIComponent(params[key]));
                        }
                    }
                    
                    // Complete URL
                    url += paramStrings.join('&');
                    
                    // Navigate to modified URL
                    window.location.href = url;
                    
                    return false;
                });
            }
        }
        
        /**
         * Setup the registration modal
         */
        function setupRegistrationModal() {
            var modal = $('#registration-form-modal');
            var registerOnlyBtn = $('#register-only-button');
            var registerApprovalBtn = $('#register-approval-button');
            var closeModalBtn = $('.close-modal');
            var formTitle = $('#registration-form-title');
            var registrationType = $('#registration-type');
            var dateSelector = $('#event-date-selector');
            var stripePaymentBtn = $('#stripe-payment-btn');
            var standardSubmitBtn = $('#standard-submit-btn');
            
            // Get selected time slot data
            function getSelectedTimeSlotData() {
                var selectedSlot = $('.time-slot-option.selected');
                if (selectedSlot.length === 0) {
                    return {
                        index: '',
                        timeStart: '',
                        timeEnd: '',
                        date: ''
                    };
                }
                
                var radio = selectedSlot.find('input[type="radio"]');
                var radioValue = radio.val();
                var slotData = parseTimeSlotValue(radioValue);
                
                // If no time data in radioValue, try to get from data attributes
                if (!slotData.timeStart || !slotData.timeEnd) {
                    slotData.timeStart = selectedSlot.data('start') || '';
                    slotData.timeEnd = selectedSlot.data('end') || '';
                    slotData.date = selectedSlot.data('date') || '';
                }
                
                return slotData;
            }
            
            // Get selected date
            function getSelectedDateValue() {
                return dateSelector.length ? dateSelector.val() : '';
            }
            
            // Validate time slot selection
            function validateTimeSlotSelection() {
                var validationMessage;
                
                if ($('.time-slot-validation-message').length === 0) {
                    validationMessage = $('<div class="time-slot-validation-message">' + $t('Please select a time slot.') + '</div>')
                        .insertAfter('#time-slots-container');
                } else {
                    validationMessage = $('.time-slot-validation-message');
                }
                
                if (!$('input[name="event_time_slot"]:checked').length) {
                    validationMessage.show();
                    
                    // Scroll to time slots
                    $('html, body').animate({
                        scrollTop: $('.time-slots-section').offset().top - 20
                    }, 300);
                    
                    return false;
                } else {
                    validationMessage.hide();
                    return true;
                }
            }
            
            // Define openModal function globally - IMPROVED VERSION TO FIX DATA TRANSFER
            window.openModal = function() {
                // Check if values are already set in hidden fields on the main page
                var mainPageTimeSlot = $('#selected-time-slot').val();
                var mainPageTimeStart = $('#selected-time-start').val(); 
                var mainPageTimeEnd = $('#selected-time-end').val();
                var mainPageDate = $('#selected-date').val();
                
                console.log('Main page hidden field values:', {
                    slot: mainPageTimeSlot,
                    start: mainPageTimeStart,
                    end: mainPageTimeEnd,
                    date: mainPageDate
                });
                
                // If values exist in main page hidden fields, we need to ensure they're set in modal fields
                // This can happen if the modal fields have different IDs or are in a different form
                
                // Identify modal fields - adjust these selectors if needed
                var modalTimeSlot = $('#registration-form-modal #selected-time-slot');
                var modalTimeStart = $('#registration-form-modal #selected-time-start');
                var modalTimeEnd = $('#registration-form-modal #selected-time-end');
                var modalDate = $('#registration-form-modal #selected-date');
                
                // Transfer values to modal fields
                if (modalTimeSlot.length && mainPageTimeSlot) {
                    modalTimeSlot.val(mainPageTimeSlot);
                }
                
                if (modalTimeStart.length && mainPageTimeStart) {
                    modalTimeStart.val(mainPageTimeStart);
                }
                
                if (modalTimeEnd.length && mainPageTimeEnd) {
                    modalTimeEnd.val(mainPageTimeEnd);
                }
                
                if (modalDate.length && mainPageDate) {
                    modalDate.val(mainPageDate);
                }
                
                // Log the modal field values after transfer
                console.log('Modal hidden field values after transfer:', {
                    slot: modalTimeSlot.val(),
                    start: modalTimeStart.val(),
                    end: modalTimeEnd.val(),
                    date: modalDate.val()
                });
                
                // Show the modal
                modal.fadeIn(300);
                $('body').addClass('modal-open');
                centerModalContent();
            };
            
            // Show "Register Only" form
            if (registerOnlyBtn.length) {
                registerOnlyBtn.on('click', function(e) {
                    // Prevent default behavior for button
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Check if time slot validation is required
                    if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                        return false;
                    }
                    
                    // Get selected time slot data
                    var slotData = getSelectedTimeSlotData();
                    
                    formTitle.text($t('Event Registration'));
                    registrationType.val('1'); // Register Only
                    
                    // Check if Stripe is enabled
                    if (window.stripeEnabled === true) {
                        // This is a paid event with Stripe enabled
                        stripePaymentBtn.show();
                        standardSubmitBtn.hide();
                    } else {
                        // This is a free event or Stripe is not enabled
                        stripePaymentBtn.hide();
                        standardSubmitBtn.show();
                    }
                    
                    window.openModal();
                    return false;
                });
            }
            
            // Show "Register with Approval" form
            if (registerApprovalBtn.length) {
                registerApprovalBtn.on('click', function(e) {
                    // Prevent default behavior for button
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Check if time slot validation is required
                    if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                        return false;
                    }
                    
                    // Get selected time slot data
                    var slotData = getSelectedTimeSlotData();
                    
                    formTitle.text($t('Request Event Registration'));
                    registrationType.val('2'); // Register with Approval
                    
                    // Always show standard submit button for approval registrations
                    stripePaymentBtn.hide();
                    standardSubmitBtn.show();
                    
                    window.openModal();
                    return false;
                });
            }
            
            // Close modal functions
            function closeModal() {
                modal.fadeOut(200);
                $('body').removeClass('modal-open');
            }
            
            // Close modal
            closeModalBtn.on('click', function() {
                closeModal();
            });
            
            // Close modal when clicking outside of it
            $(window).on('click', function(event) {
                if (event.target == modal[0]) {
                    closeModal();
                }
            });
            
            // Close on escape key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27 && modal.is(':visible')) { // ESC key
                    closeModal();
                }
            });
            
            // Add listener for form submission to verify data is included
            $('#event-registration-form').on('submit', function() {
                // Get the values from the hidden fields
                var modalTimeSlot = $('#registration-form-modal #selected-time-slot').val();
                var modalTimeStart = $('#registration-form-modal #selected-time-start').val();
                var modalTimeEnd = $('#registration-form-modal #selected-time-end').val();
                var modalDate = $('#registration-form-modal #selected-date').val();
                
                // If modal fields don't have values, try to get from main page
                if (!modalTimeSlot || !modalTimeStart || !modalTimeEnd || !modalDate) {
                    var mainPageTimeSlot = $('#selected-time-slot').val();
                    var mainPageTimeStart = $('#selected-time-start').val();
                    var mainPageTimeEnd = $('#selected-time-end').val();
                    var mainPageDate = $('#selected-date').val();
                    
                    // Set modal field values if main page has values
                    if (mainPageTimeSlot) {
                        $('#registration-form-modal #selected-time-slot').val(mainPageTimeSlot);
                    }
                    
                    if (mainPageTimeStart) {
                        $('#registration-form-modal #selected-time-start').val(mainPageTimeStart);
                    }
                    
                    if (mainPageTimeEnd) {
                        $('#registration-form-modal #selected-time-end').val(mainPageTimeEnd);
                    }
                    
                    if (mainPageDate) {
                        $('#registration-form-modal #selected-date').val(mainPageDate);
                    }
                }
                
                // Log form field values before submission
                console.log('Form submission values:', {
                    slot: $('#registration-form-modal #selected-time-slot').val(),
                    start: $('#registration-form-modal #selected-time-start').val(),
                    end: $('#registration-form-modal #selected-time-end').val(),
                    date: $('#registration-form-modal #selected-date').val()
                });
                
                // ADD THIS NEW CODE HERE - Reset form after submission
                // Use setTimeout to ensure it happens after form submission
                var form = this;
                var modal = $('#registration-form-modal');
                
                // Add a "processing" message to the form if needed
                var processingMessage = $('<div class="form-message success" style="background-color: #e8f6e8; color: #1b5e20; padding: 15px; margin-bottom: 20px; border-left: 3px solid #4caf50;"></div>')
                    .text('Registration submitted successfully! Please wait...');
                
                // Add the message to the form
                $(form).prepend(processingMessage);
                
                // Disable the submit button to prevent multiple submissions
                $(form).find('button[type="submit"]').prop('disabled', true);
                
                // Hide modal and reset form after 2 seconds
                setTimeout(function() {
                    // Reset the form
                    form.reset();
                    
                    // Hide the modal
                    modal.fadeOut(500);
                    $('body').removeClass('modal-open');
                    
                    // Re-enable the submit button after modal is closed
                    $(form).find('button[type="submit"]').prop('disabled', false);
                    
                    // Remove the processing message
                    processingMessage.remove();
                }, 2000);
                
                return true;
            });
        }
        
        /**
         * Setup Stripe payment button
         */
        function setupStripePaymentButton() {
            var stripePaymentBtn = $('#stripe-payment-btn');
            var registrationForm = $('#event-registration-form');
            
            if (stripePaymentBtn.length) {
                stripePaymentBtn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Validate the form
                    if (!registrationForm[0].checkValidity()) {
                        // Trigger browser's native form validation
                        $('<input type="submit">').hide().appendTo(registrationForm).click().remove();
                        return false;
                    }
                    
                    // Try to get values from modal fields first
                    var timeSlot = $('#registration-form-modal #selected-time-slot').val();
                    var timeStart = $('#registration-form-modal #selected-time-start').val();
                    var timeEnd = $('#registration-form-modal #selected-time-end').val();
                    var date = $('#registration-form-modal #selected-date').val();
                    
                    // If modal fields don't have values, try to get from main page
                    if (!timeSlot || !timeStart || !timeEnd || !date) {
                        timeSlot = $('#selected-time-slot').val();
                        timeStart = $('#selected-time-start').val();
                        timeEnd = $('#selected-time-end').val();
                        date = $('#selected-date').val();
                    }
                    
                    // Log values before proceeding
                    console.log('Stripe payment with values:', {
                        slot: timeSlot,
                        start: timeStart,
                        end: timeEnd,
                        date: date
                    });
                    
                    // Collect form data
                    var formData = {
                        first_name: $('#first_name').val(),
                        last_name: $('#last_name').val(),
                        email: $('#email').val(),
                        street: $('#street').val(),
                        city: $('#city').val(),
                        zipcode: $('#zipcode').val(),
                        country: $('#country').val()
                    };
                    
                    // Build the Stripe URL
                    var stripeUrl = window.stripeCheckoutUrl;
                    var separator = stripeUrl.indexOf('?') !== -1 ? '&' : '?';
                    
                    // Add time slot data
                    if (timeSlot) {
                        stripeUrl += separator + 'selected_time_slot=' + encodeURIComponent(timeSlot);
                        separator = '&';
                    }
                    
                    if (timeStart) {
                        stripeUrl += separator + 'selected_time_start=' + encodeURIComponent(timeStart);
                        separator = '&';
                    }
                    
                    if (timeEnd) {
                        stripeUrl += separator + 'selected_time_end=' + encodeURIComponent(timeEnd);
                        separator = '&';
                    }
                    
                    if (date) {
                        stripeUrl += separator + 'selected_date=' + encodeURIComponent(date);
                        separator = '&';
                    }
                    
                    // Add form data
                    stripeUrl += separator + 'customer_email=' + encodeURIComponent(formData.email);
                    separator = '&';
                    stripeUrl += separator + 'customer_name=' + 
                                 encodeURIComponent(formData.first_name + ' ' + formData.last_name);
                    
                    // Redirect to Stripe
                    window.location.href = stripeUrl;
                });
            }
        }
        
        /**
         * Center modal content on mobile
         */
        function centerModalContent() {
            if ($('body').hasClass('mobile-view')) {
                var windowHeight = window.innerHeight;
                var modalHeight = $('.modal-content').outerHeight();
                
                if (modalHeight < windowHeight) {
                    // Center vertically if modal is shorter than the window
                    $('.modal-content').css({
                        'margin-top': Math.max(10, (windowHeight - modalHeight) / 2) + 'px'
                    });
                } else {
                    // Position at top with small margin if modal is taller
                    $('.modal-content').css({
                        'margin-top': '10px'
                    });
                }
            } else {
                // Reset for desktop
                $('.modal-content').css({
                    'margin-top': '5%'
                });
            }
        }
        
        /**
         * Setup form validation
         */
        function setupFormValidation() {
            $('#event-registration-form').on('submit', function(e) {
                var isValid = true;
                var firstError = null;
                
                // Clear previous error messages
                $('.validation-message').remove();
                
                // Validate required fields
                $(this).find('.required-entry').each(function() {
                    if ($(this).val().trim() === '') {
                        $(this).addClass('validation-failed');
                        appendErrorMessage($(this), $t('This is a required field.'));
                        isValid = false;
                        
                        if (!firstError) {
                            firstError = $(this);
                        }
                    } else {
                        $(this).removeClass('validation-failed');
                    }
                });
                
                // Validate email format
                $(this).find('.validate-email').each(function() {
                    if ($(this).val().trim() !== '') {
                        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                        if (!emailRegex.test($(this).val())) {
                            $(this).addClass('validation-failed');
                            appendErrorMessage($(this), $t('Please enter a valid email address.'));
                            isValid = false;
                            
                            if (!firstError) {
                                firstError = $(this);
                            }
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    if (firstError) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 300);
                        firstError.focus();
                    }
                    
                    return false;
                }
                
                return true;
            });
            
            // Helper function to append error message
            function appendErrorMessage(element, message) {
                $('<div class="validation-message"></div>')
                    .text(message)
                    .insertAfter(element);
            }
        }
        
        /**
         * Setup AJAX submission for "Register Only" registration type
         * This allows for order creation on the server after successful registration
         */
        function setupRegisterOnlySubmission() {
            $('#event-registration-form').on('submit', function(e) {
                // Only handle "Register Only" type with AJAX, let other types submit normally
                if ($('#registration-type').val() === '1') {
                    var form = $(this);
                    
                    // Check if the form is valid (already handled by setupFormValidation)
                    if (!form[0].checkValidity()) {
                        return true; // Let the browser handle validation
                    }
                    
                    // Verify time slot data is set in modal form
                    var modalTimeSlot = $('#registration-form-modal #selected-time-slot').val();
                    var modalTimeStart = $('#registration-form-modal #selected-time-start').val();
                    var modalTimeEnd = $('#registration-form-modal #selected-time-end').val();
                    var modalDate = $('#registration-form-modal #selected-date').val();
                    
                    // If modal fields don't have values, copy from main page
                    if (!modalTimeSlot || !modalTimeStart || !modalTimeEnd || !modalDate) {
                        var mainPageTimeSlot = $('#selected-time-slot').val();
                        var mainPageTimeStart = $('#selected-time-start').val();
                        var mainPageTimeEnd = $('#selected-time-end').val();
                        var mainPageDate = $('#selected-date').val();
                        
                        // Set modal field values
                        if (mainPageTimeSlot) {
                            $('#registration-form-modal #selected-time-slot').val(mainPageTimeSlot);
                        }
                        
                        if (mainPageTimeStart) {
                            $('#registration-form-modal #selected-time-start').val(mainPageTimeStart);
                        }
                        
                        if (mainPageTimeEnd) {
                            $('#registration-form-modal #selected-time-end').val(mainPageTimeEnd);
                        }
                        
                        if (mainPageDate) {
                            $('#registration-form-modal #selected-date').val(mainPageDate);
                        }
                    }
                    
                    // Debug log values
                    console.log('Register Only submission with values:', {
                        slot: $('#registration-form-modal #selected-time-slot').val(),
                        start: $('#registration-form-modal #selected-time-start').val(),
                        end: $('#registration-form-modal #selected-time-end').val(),
                        date: $('#registration-form-modal #selected-date').val()
                    });
                    
                    // Show processing state
                    var submitButton = form.find('button[type="submit"]');
                    var originalText = submitButton.find('span').text();
                    submitButton.find('span').text($t('Processing...'));
                    submitButton.prop('disabled', true);
                    
                    // Remove any existing form messages
                    form.find('.form-message').remove();
                    
                    // Get form data
                    var formData = form.serialize();
                    
                    // Submit the form via AJAX
                    $.ajax({
                        url: form.attr('action'),
                        data: formData,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Show brief success message before redirecting
                                showFormMessage(form, response.message || $t('Registration successful! Creating your order...'), 'success');
                                
                                // Redirect to success page after a brief delay
                                setTimeout(function() {
                                    window.location.href = response.redirect || (BASE_URL + 'events/registration/success?registration_id=' + response.registration_id);
                                }, 1000);
                            } else {
                                // Show error message
                                showFormMessage(form, response.message || $t('An error occurred. Please try again.'), 'error');
                                
                                // Reset button
                                submitButton.find('span').text(originalText);
                                submitButton.prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            // Handle error response
                            var errorMessage = $t('An error occurred during registration. Please try again.');
                            
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response && response.message) {
                                    errorMessage = response.message;
                                }
                            } catch(e) {
                                // Use default error message
                            }
                            
                            showFormMessage(form, errorMessage, 'error');
                            
                            // Reset button
                            submitButton.find('span').text(originalText);
                            submitButton.prop('disabled', false);
                        }
                    });
                    
                    // Prevent default form submission
                    e.preventDefault();
                    return false;
                }
                
                // For other registration types, let the form submit normally
                return true;
            });
            
            /**
             * Show form message
             * @param {jQuery} form - The form element
             * @param {string} message - Message to display
             * @param {string} type - Message type ('success' or 'error')
             */
            function showFormMessage(form, message, type) {
                // Remove any existing messages
                form.find('.form-message').remove();
                
                // Create message element
                var messageEl = $('<div class="form-message"></div>').text(message);
                
                // Style based on type
                if (type === 'success') {
                    messageEl.css({
                        'background-color': '#e8f6e8',
                        'color': '#1b5e20',
                        'padding': '15px',
                        'margin-bottom': '20px',
                        'border-left': '3px solid #4caf50'
                    });
                } else {
                    messageEl.css({
                        'background-color': '#fdecea',
                        'color': '#c62828',
                        'padding': '15px',
                        'margin-bottom': '20px',
                        'border-left': '3px solid #e53935'
                    });
                }
                
                // Add to the form
                form.prepend(messageEl);
                
                // Scroll to message
                $('html, body').animate({
                    scrollTop: messageEl.offset().top - 100
                }, 300);
            }
        }
        
        /**
         * Update past time slots to appear differently
         */
        function updatePastTimeSlots() {
            var now = new Date();
            now.setHours(0, 0, 0, 0); // Compare dates only, not times
            
            $('.time-slot-option').each(function() {
                var slotDate = $(this).data('date');
                if (slotDate) {
                    var dateParts = slotDate.split('-');
                    var dateOnly = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // Year, month (0-based), day
                    
                    if (dateOnly < now) {
                        $(this).addClass('past');
                        $(this).find('input[type="radio"]').prop('disabled', true);
                    } else {
                        $(this).removeClass('past');
                        $(this).find('input[type="radio"]').prop('disabled', false);
                    }
                }
            });
            
            // Ensure at least one non-past slot is selected if available
            var availableSlots = $('.time-slot-option:not(.past)');
            if (availableSlots.length > 0 && $('.time-slot-option.selected:not(.past)').length === 0) {
                var firstAvailableSlot = availableSlots.first();
                firstAvailableSlot.addClass('selected');
                firstAvailableSlot.find('input[type="radio"]').prop('checked', true).trigger('change');
            }
        }
        
        /**
         * Improve scrolling behavior on mobile devices
         */
        function improveScrollingOnMobile() {
            if ($('body').hasClass('mobile-view')) {
                // Smooth scroll to time slots when selecting a date
                $('#event-date-selector').on('change', function() {
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: $('.time-slots-section').offset().top - 20
                        }, 300);
                    }, 100);
                });
                
                // Smooth scroll to registration button after selecting time slot
                $('.time-slot-option').on('click', function() {
                    if (!$(this).hasClass('past')) {
                        setTimeout(function() {
                            $('html, body').animate({
                                scrollTop: $('.register-button').offset().top - 20
                            }, 300);
                        }, 200);
                    }
                });
                
                // Better modal scrolling
                $('.modal-content').on('touchmove', function(e) {
                    e.stopPropagation();
                });
            }
        }
        
        /**
         * Format date in Month Day, Year format
         * @param {Date} date - Date object
         * @return {string} Formatted date string
         */
        function formatDate(date) {
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        }
        
        /**
         * Format time in 12-hour format
         * @param {string} time - Time in HH:MM format
         * @return {string} Formatted time string
         */
        function formatTime(time) {
            if (!time) return '';
            
            // Parse time components
            var parts = time.split(':');
            var hours = parseInt(parts[0], 10);
            var minutes = parts[1] || '00';
            var ampm = hours >= 12 ? 'PM' : 'AM';
            
            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // Handle midnight (0:00)
            
            return hours + ':' + minutes + ' ' + ampm;
        }
        
        /**
         * Get selected date value
         * @return {string} Selected date in YYYY-MM-DD format
         */
        function getSelectedDate() {
            return $('#event-date-selector').length ? $('#event-date-selector').val() : $('#selected-date').val();
        }
    };
});