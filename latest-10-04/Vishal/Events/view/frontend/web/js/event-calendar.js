define([
    'jquery',
    'mage/calendar',
    'mage/translate',
    'mage/mage'
], function ($, calendar, $t) {
    'use strict';

    return function (config) {
        $(document).ready(function () {
            // Default configuration
            config = $.extend({
                dateFormat: 'yyyy-MM-dd',
                timeSlots: {
                    weekdays: ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'],
                    weekends: ['10:00', '11:00', '12:00', '13:00']
                },
                availableDays: {
                    monday: true,
                    tuesday: true,
                    wednesday: true,
                    thursday: true,
                    friday: true,
                    saturday: true,
                    sunday: true
                },
                blockDates: [],
                specialDates: {}
            }, config);

            // Initialize variables
            var selectedDate = null;
            var weekDayMap = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

            // Initialize the date picker
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

                    // Check if day of week is available
                    var dayOfWeek = date.getDay();
                    var dayName = weekDayMap[dayOfWeek];
                    var isDayAvailable = config.availableDays[dayName];

                    // Check if it's a special date
                    var isSpecialDate = dateStr in config.specialDates;

                    // Determine CSS class
                    var cssClass = '';
                    if (isSpecialDate) {
                        cssClass = 'special-date';
                    } else if (isDayAvailable) {
                        cssClass = 'ui-state-available';
                    }

                    return [isDayAvailable || isSpecialDate, cssClass];
                },
                onSelect: function(dateText) {
                    selectedDate = dateText;
                    updateSelectedDateDisplay(dateText);
                    loadTimeSlots(dateText);
                }
            });

            // Update the selected date display
            function updateSelectedDateDisplay(dateStr) {
                var formattedDate = new Date(dateStr);
                var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                $('#selected-date-display').text(formattedDate.toLocaleDateString(undefined, options));
                $('.time-selector-prompt').hide();
            }

            // Load time slots for the selected date
            function loadTimeSlots(dateStr) {
                var timeSlots = [];
                var date = new Date(dateStr);
                var dayOfWeek = date.getDay();
                var isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

                // Check if it's a special date with custom time slots
                if (dateStr in config.specialDates) {
                    timeSlots = config.specialDates[dateStr];
                } else {
                    // Use regular time slots based on weekday/weekend
                    timeSlots = isWeekend ? config.timeSlots.weekends : config.timeSlots.weekdays;
                }

                var $container = $('#time-slots-container');

                $container.empty();

                if (timeSlots.length === 0) {
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
                var timeParts = time.split(':');
                var hour = parseInt(timeParts[0]);
                var minute = timeParts[1];

                var period = hour >= 12 ? $t('PM') : $t('AM');
                hour = hour % 12;
                hour = hour ? hour : 12; // Convert 0 to 12
                return hour + ':' + minute + ' ' + period;
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

                // In a real implementation, you'd do something like:
                /*
                require(['mage/url'], function(url) {
                    $.ajax({
                        url: url.build('events/event/register'),
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            event_id: $('input[name="event_id"]').val(),
                            datetime: selectedDateTime
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message or redirect
                            } else {
                                // Show error message
                            }
                        }
                    });
                });
                */
            });

            // Format the selected date and time for display
            function formatSelectedDateTime(datetime) {
                var parts = datetime.split(' ');
                var datePart = parts[0];
                var timePart = parts[1];

                var date = new Date(datePart);
                var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                var formattedDate = date.toLocaleDateString(undefined, options);

                return formattedDate + ' at ' + formatTime(timePart);
            }
        });
    };
});
