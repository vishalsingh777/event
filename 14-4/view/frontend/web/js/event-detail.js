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
            repeatType = config.repeatType;
            
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
        });
        
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
                $(this).closest('.time-slot-option').addClass('selected');
                
                // Update the hidden input for selected time slot
                $('#selected-time-slot').val($(this).val());
            });
            
            // Initialize with pre-selected slot highlighted
            $('.time-slot-option input[type="radio"]:checked').closest('.time-slot-option').addClass('selected');
            
            // If only one time slot, make sure it's selected
            if ($('.time-slot-option').length === 1) {
                var singleSlot = $('.time-slot-option');
                singleSlot.addClass('selected');
                singleSlot.find('input[type="radio"]').prop('checked', true);
                $('#selected-time-slot').val(singleSlot.find('input[type="radio"]').val());
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
         * @param {string} repeatType - The repeat type (0=once, 1=daily, 2=weekly, 3=monthly)
         */
        function updateTimeSlotsForDate(selectedDate, repeatType) {
            $.ajax({
                url: BASE_URL + 'events/ajax/timeslots',
                data: {
                    event_id: eventId,
                    selected_date: selectedDate
                },
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
                    
                    timeSlotHtml += '<label class="time-slot-option ' + isSelected + '" data-date="' + selectedDate + '">' +
                        '<input type="radio" name="event_time_slot" value="' + slot.index + '" ' + isChecked + '>' +
                        '<span class="time-slot-text">' + slot.formatted + '</span>' +
                        '</label>';
                }
            } else {
                timeSlotHtml = '<div class="no-slots-message">' + $t('No time slots available for this date.') + '</div>';
            }
            
            timeSlotContainer.html(timeSlotHtml);
            
            // Update selected time slot field
            if (slots && slots.length > 0) {
                $('#selected-time-slot').val(slots[0].index);
            } else {
                $('#selected-time-slot').val('');
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
                    
                    timeSlotHtml += '<label class="time-slot-option ' + isSelected + '" data-date="' + selectedDate + '">' +
                        '<input type="radio" name="event_time_slot" value="' + radio.val() + '" ' + isChecked + '>' +
                        '<span class="time-slot-text">' + newSlotText + '</span>' +
                        '</label>';
                });
                
                // Update selected time slot field with first slot
                var firstSlot = existingSlots.eq(0).find('input[type="radio"]').val();
                $('#selected-time-slot').val(firstSlot);
            } else {
                timeSlotHtml = '<div class="no-slots-message">' + $t('No time slots available for this date.') + '</div>';
                $('#selected-time-slot').val('');
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
            
            // Add to cart with selected time slot
            if (registerPayBtn.length) {
                registerPayBtn.on('click', function(e) {
                    if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Get current URL
                    var url = $(this).attr('href');
                    
                    // Get selected time slot and date
                    var selectedSlot = $('input[name="event_time_slot"]:checked').val() || '';
                    var selectedDate = $('#event-date-selector').length ? $('#event-date-selector').val() : '';
                    
                    // Build parameters
                    var params = {};
                    
                    // Only add parameters if they have values
                    if (selectedSlot) {
                        params.selected_time_slot = selectedSlot;
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
            var selectedTimeSlot = $('#selected-time-slot');
            var selectedDate = $('#selected-date');
            var dateSelector = $('#event-date-selector');
            
            // Get selected time slot
            function getSelectedTimeSlot() {
                return $('input[name="event_time_slot"]:checked').val() || '';
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
                
                if (!getSelectedTimeSlot()) {
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
            
            // Show "Register Only" form
            if (registerOnlyBtn.length) {
                registerOnlyBtn.on('click', function(e) {
                    // Check if time slot validation is required
                    if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                        e.preventDefault();
                        return false;
                    }
                    
                    formTitle.text($t('Event Registration'));
                    registrationType.val('1'); // Register Only
                    selectedTimeSlot.val(getSelectedTimeSlot());
                    selectedDate.val(getSelectedDateValue());
                    modal.fadeIn(300);
                    $('body').addClass('modal-open');
                    centerModalContent();
                });
            }
            
            // Show "Register with Approval" form
            if (registerApprovalBtn.length) {
                registerApprovalBtn.on('click', function(e) {
                    // Check if time slot validation is required
                    if ($(this).data('validate-time-slot') && !validateTimeSlotSelection()) {
                        e.preventDefault();
                        return false;
                    }
                    
                    formTitle.text($t('Request Event Registration'));
                    registrationType.val('2'); // Register with Approval
                    selectedTimeSlot.val(getSelectedTimeSlot());
                    selectedDate.val(getSelectedDateValue());
                    modal.fadeIn(300);
                    $('body').addClass('modal-open');
                    centerModalContent();
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
                firstAvailableSlot.find('input[type="radio"]').prop('checked', true);
                
                // Update selected time slot field
                $('#selected-time-slot').val(firstAvailableSlot.find('input[type="radio"]').val());
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
         * Parse a slot date from text format
         * @param {string} slotText - The text description of the time slot
         * @returns {Date|null} - Date object or null if parsing failed
         */
        function parseSlotDate(slotText) {
            try {
                // First try to extract date part (if exists)
                var dateMatch = slotText.match(/([A-Za-z]+\s+\d{1,2},\s+\d{4})/);
                var dateStr = dateMatch ? dateMatch[1] : '';
                
                // Extract time (looking for formats like "4:00 PM")
                var timeMatch = slotText.match(/(\d{1,2}:\d{2}\s*(?:AM|PM))/i);
                if (!timeMatch) return null;
                
                var dateTimeStr = dateStr + ' ' + timeMatch[1];
                var parsedDate = new Date(dateTimeStr);
                
                // If valid date
                if (!isNaN(parsedDate.getTime())) {
                    return parsedDate;
                }
                return null;
            } catch (e) {
                console.error('Error parsing slot date:', e);
                return null;
            }
        }
    };
});