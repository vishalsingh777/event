define([
    'jquery',
    'mage/calendar',
    'mage/translate',
    'mage/url',
    'mage/mage'
], function ($, calendar, $t, urlBuilder) {
    'use strict';

    return function (config) {
        $(document).ready(function () {
            // Default configuration - these will be overridden by server data
            var defaultConfig = {
                dateFormat: 'yyyy-MM-dd',
                timeSlots: [],  // Empty array, will be populated from backend
                availableDays: {
                    monday: true,
                    tuesday: true,
                    wednesday: true,
                    thursday: true,
                    friday: true,
                    saturday: true,
                    sunday: true
                },
                blockDates: []
            };
            
            // Initialize variables
            var selectedDate = null;
            var weekDayMap = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            var eventId = $('input[name="event_id"]').val();
            var isCalendarInitialized = false;
            var serverConfig = null; // Store the server config globally

            // Always load schedule data from server for the event
            loadEventSchedule(eventId);

            // Load event schedule data from server
            function loadEventSchedule(eventId) {
                var calendarContainer = $('#event-date-picker');
                calendarContainer.addClass('loading');

                $.ajax({
                    url: urlBuilder.build('events/event/schedule'),
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        event_id: eventId
                    },
                    success: function(response) {
                        calendarContainer.removeClass('loading');
                        
                        var calendarConfig = $.extend({}, defaultConfig);
                        
                        if (response.success && response.data) {
                            console.log('Server response:', response.data);
                            
                            // Update config with server data
                            if (response.data.availableDays) {
                                calendarConfig.availableDays = response.data.availableDays;
                            }
                            
                            if (response.data.timeSlots && response.data.timeSlots.length > 0) {
                                calendarConfig.timeSlots = response.data.timeSlots;
                            }
                            
                            if (response.data.blockDates && response.data.blockDates.length > 0) {
                                calendarConfig.blockDates = response.data.blockDates;
                            }

                            // For recurring events, track generated dates
                            if (response.data.isRecurring && response.data.generatedDates && response.data.generatedDates.length > 0) {
                                calendarConfig.availableDates = response.data.generatedDates;
                            }
                            
                            // Store the config for later use
                            serverConfig = calendarConfig;
                            
                            // Initialize calendar with the server data
                            initializeCalendar(calendarConfig);
                        } else {
                            console.error('Error loading event schedule:', response.message || 'Unknown error');
                            initializeCalendar(calendarConfig);
                        }
                    },
                    error: function(xhr, status, error) {
                        calendarContainer.removeClass('loading');
                        console.error('AJAX request failed when loading event schedule:', status, error);
                        initializeCalendar(defaultConfig);
                    }
                });
            }

            // Initialize the date picker
            function initializeCalendar(config) {
                if (isCalendarInitialized) {
                    // Destroy existing calendar if already initialized
                    $("#event-date-picker").calendar('destroy');
                }
                
                console.log('Initializing calendar with config:', config);
                
                $("#event-date-picker").calendar({
                    dateFormat: config.dateFormat,
                    showsTime: false,
                    buttonText: $t('Select Date'),
                    minDate: new Date(),
                    beforeShowDay: function(date) {
                        var dateStr = $.datepicker.formatDate('yy-mm-dd', date);

                        // Check if date is blocked
                        if ($.inArray(dateStr, config.blockDates) !== -1) {
                            return [false, 'date-blocked'];
                        }

                        // Check if we have explicitly available dates (for recurring events)
                        if (config.availableDates && config.availableDates.length > 0) {
                            var isAvailableDate = $.inArray(dateStr, config.availableDates) !== -1;
                            return [isAvailableDate, isAvailableDate ? 'ui-state-available' : ''];
                        }

                        // Check if day of week is available
                        var dayOfWeek = date.getDay();
                        var dayName = weekDayMap[dayOfWeek];
                        var isDayAvailable = config.availableDays[dayName];

                        // Determine CSS class
                        var cssClass = isDayAvailable ? 'ui-state-available' : '';

                        return [isDayAvailable, cssClass];
                    },
                    onSelect: function(dateText) {
                        selectedDate = dateText;
                        updateSelectedDateDisplay(dateText);
                        
                        // Always use the most up to date config when loading time slots
                        loadTimeSlots(dateText, serverConfig || config);
                    }
                });
                
                isCalendarInitialized = true;
            }

            // Update the selected date display
            function updateSelectedDateDisplay(dateStr) {
                var formattedDate = new Date(dateStr);
                var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                $('#selected-date-display').text(formattedDate.toLocaleDateString(undefined, options));
                $('.time-selector-prompt').hide();
            }

            // Load time slots for the selected date based on config
            function loadTimeSlots(dateStr, config) {
                // Clear any existing time slots first
                var $container = $('#time-slots-container');
                $container.empty();
                
                // No need to check day of week - use the time slots from config
                var timeSlots = config.timeSlots || [];
                console.log('Loading time slots for date', dateStr, 'slots:', timeSlots);
                
                if (!timeSlots || timeSlots.length === 0) {
                    $container.append('<p class="no-time-slots">' + $t('No time slots available for this date.') + '</p>');
                    return;
                }

                var $slotsList = $('<div class="time-slots-list"></div>');

                timeSlots.forEach(function(timeSlot) {
                    var formattedTime = formatTime(timeSlot);
                    var $slot = $('<div class="time-slot" data-time="' + timeSlot + '">' + formattedTime + '</div>');
                    $slotsList.append($slot);
                });

                $container.append($slotsList);

                // Attach click event to time slots
                $('.time-slot').on('click', function() {
                    $('.time-slot').removeClass('selected');
                    $(this).addClass('selected');

                    // Store the selected date and time
                    var selectedTime = $(this).data('time');
                    $('#selected-datetime').val(selectedDate + ' ' + selectedTime);
                });
            }

            // Format time for display (24h to 12h format)
            function formatTime(time) {
                // Handle different possible time formats
                if (!time) return "12:00 AM";
                
                var timeParts;
                if (time.indexOf(':') !== -1) {
                    timeParts = time.split(':');
                } else {
                    // Try to handle time as a number (hours only)
                    var hour = parseInt(time);
                    if (!isNaN(hour)) {
                        timeParts = [hour, "00"];
                    } else {
                        console.error('Could not parse time:', time);
                        return time; // Return it as is if we can't parse
                    }
                }
                
                var hour = parseInt(timeParts[0]);
                var minute = timeParts[1] || '00';
                
                if (isNaN(hour)) {
                    console.error('Invalid hour in time:', time);
                    return time; // Return it as is if we can't parse
                }

                var period = hour >= 12 ? 'PM' : 'AM';
                var displayHour = hour % 12;
                displayHour = displayHour ? displayHour : 12; // Convert 0 to 12
                
                // Make sure minute is always 2 digits
                if (minute.length === 1) {
                    minute = '0' + minute;
                }
                
                return displayHour + ':' + minute + ' ' + period;
            }

            // Handle registration button click
            $('#register-event').on('click', function() {
                var selectedDateTime = $('#selected-datetime').val();
                if (!selectedDateTime) {
                    // Show error message if no date/time selected
                    alert($t('Please select a date and time before registering.'));
                    return;
                }

                // Here you would typically trigger your registration process
                // This could be an AJAX call to a controller action that processes the registration
                // For demo purposes, we'll just show an alert
                alert($t('Thank you for registering for this event on ') + formatSelectedDateTime(selectedDateTime));
            });

            // Format the selected date and time for display
            function formatSelectedDateTime(datetime) {
                var parts = datetime.split(' ');
                if (parts.length < 2) {
                    return datetime; // Return as is if invalid format
                }
                
                var datePart = parts[0];
                var timePart = parts[1];

                try {
                    var date = new Date(datePart);
                    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    var formattedDate = date.toLocaleDateString(undefined, options);
                    return formattedDate + ' at ' + formatTime(timePart);
                } catch (e) {
                    console.error('Error formatting date time:', e);
                    return datetime;
                }
            }
        });
    };
});