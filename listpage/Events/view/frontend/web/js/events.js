/**
 * INSEAD Events JavaScript
 */
define([
    'jquery',
    'mage/url',
    'mage/translate',
    'mage/cookies'
], function($, url, $t) {
    'use strict';
    
    return function(config) {
        var EventsModule = {
            config: $.extend({
                ajaxUrl: 'events/ajax',
                currentView: 'grid',
                perPage: 6,
                animationDelay: 100
            }, config),
            
            init: function() {
                this.bindEvents();
                this.initAnimation();
                this.checkEnvironment();
                this.initMobileView();
                
                // Initialize any custom views
                if (this.config.currentView === 'calendar') {
                    this.initCalendarView();
                }
                
                return this;
            },
            
            bindEvents: function() {
                // Filter functionality
                $(document).on('change', '.filter-select', this.handleFilterChange.bind(this));
                
                // View toggle functionality
                $(document).on('click', '.toggle-btn', this.handleViewToggle.bind(this));
                
                // Campus tabs functionality
                $(document).on('click', '.tab-btn', this.handleCampusTabClick.bind(this));
                
                // Calendar functionality
                $(document).on('click', '.calendar-cell:not(.disabled)', this.handleCalendarCellClick.bind(this));
                $(document).on('click', '.calendar-nav-btn', this.handleCalendarNavigation.bind(this));
                
                // Hero search functionality
                $(document).on('keyup', '#hero-search-input', this.handleHeroSearch.bind(this));
                $(document).on('click', '.hero-search-button', this.handleSearchButtonClick.bind(this));
                
                // Newsletter subscription
                $(document).on('submit', '.newsletter-form', this.handleNewsletterSubscription.bind(this));
                
                // Date filter listeners
                $(document).on('change', '#filter-date', this.handleDateFilterChange.bind(this));
                $(document).on('click', '.apply-date-range', this.handleDateRangeApply.bind(this));
                
                // Mobile tab toggle click handler
                $(document).on('click', '.mobile-tab-toggle', this.handleMobileTabToggle.bind(this));
                
                // Window resize event
                $(window).on('resize', this.handleWindowResize.bind(this)).trigger('resize');
            },
            
            /**
             * Initialize animation for event cards and other elements
             */
            initAnimation: function() {
                $('.featured-event-hero, .featured-event-card, .event-card, .category-card, .event-list-item').each(function(index) {
                    var $element = $(this);
                    setTimeout(function() {
                        $element.addClass('appear');
                    }, index * EventsModule.config.animationDelay);
                });
            },
            
            /**
             * Check if we're in a development environment
             */
            checkEnvironment: function() {
                this.isLocalEnvironment = (
                    window.location.hostname === 'localhost' || 
                    window.location.hostname === '127.0.0.1' ||
                    window.location.hostname.includes('.local') ||
                    window.location.hostname.includes('.test')
                );
                
                if (this.isLocalEnvironment) {
                    console.log('INSEAD Events: Running in local/development environment');
                }
            },
            
            /**
             * Initialize mobile-specific components
             */
            initMobileView: function() {
                if ($(window).width() < 768) {
                    if (!$('.tab-nav').hasClass('mobile-ready')) {
                        $('.tab-nav').addClass('mobile-ready');
                        $('.tab-nav').before('<div class="mobile-tab-toggle">' + $t('Select Campus') + ' <i class="material-icons">keyboard_arrow_down</i></div>');
                    }
                } else {
                    $('.tab-nav').removeClass('mobile-ready');
                    $('.mobile-tab-toggle').remove();
                }
            },
            
            /**
             * Initialize calendar view specific functionality
             */
            initCalendarView: function() {
                // Additional calendar initialization if needed
                console.log('Calendar view initialized');
                
                // Highlight selected date if any
                var urlParams = new URLSearchParams(window.location.search);
                var selectedDate = urlParams.get('date');
                
                if (selectedDate) {
                    $('.calendar-cell[data-date="' + selectedDate + '"]').addClass('active');
                    this.loadEventsForDate(selectedDate);
                } else {
                    // Highlight today by default
                    var today = new Date().toISOString().slice(0, 10);
                    $('.calendar-cell[data-date="' + today + '"]').addClass('active');
                }
            },
            
            /**
             * Handle filter changes
             */
            handleFilterChange: function(e) {
                var params = this.collectFilterParams();
                
                // If it's not a date filter or it has a value that isn't custom, apply immediately
                if ($(e.target).attr('id') !== 'filter-date' || 
                    ($(e.target).attr('id') === 'filter-date' && $(e.target).val() !== 'custom')) {
                    this.applyFilters(params);
                }
            },
            
            /**
             * Handle date filter type change
             */
            handleDateFilterChange: function(e) {
                if ($(e.target).val() === 'custom') {
                    // Show date range picker
                    this.showDateRangePicker();
                } else {
                    // Remove date range picker if not using custom date
                    $('.date-range-picker').remove();
                }
            },
            
            /**
             * Show date range picker
             */
            showDateRangePicker: function() {
                if ($('.date-range-picker').length === 0) {
                    var dateRangeHtml = '<div class="date-range-picker">';
                    dateRangeHtml += '<div class="date-from"><label>' + $t('From') + '</label><input type="date" id="date-from" name="from"></div>';
                    dateRangeHtml += '<div class="date-to"><label>' + $t('To') + '</label><input type="date" id="date-to" name="to"></div>';
                    dateRangeHtml += '<button type="button" class="apply-date-range">' + $t('Apply') + '</button>';
                    dateRangeHtml += '</div>';
                    
                    $('#filter-date').after(dateRangeHtml);
                    
                    // Set default dates (today and 30 days from now)
                    var today = new Date();
                    var futureDate = new Date();
                    futureDate.setDate(today.getDate() + 30);
                    
                    $('#date-from').val(today.toISOString().slice(0, 10));
                    $('#date-to').val(futureDate.toISOString().slice(0, 10));
                    
                    // Get values from URL if they exist
                    var urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('from')) {
                        $('#date-from').val(urlParams.get('from'));
                    }
                    if (urlParams.has('to')) {
                        $('#date-to').val(urlParams.get('to'));
                    }
                }
            },
            
            /**
             * Handle apply button click for date range
             */
            handleDateRangeApply: function() {
                var params = this.collectFilterParams();
                
                // Add date range parameters
                if ($('#date-from').val()) {
                    params['from'] = $('#date-from').val();
                }
                
                if ($('#date-to').val()) {
                    params['to'] = $('#date-to').val();
                }
                
                this.applyFilters(params);
            },
            
            /**
             * Collect all filter parameters
             */
            collectFilterParams: function() {
                var params = {};
                
                // Collect all filter values
                $('.filter-select').each(function() {
                    var param = $(this).data('param');
                    var value = $(this).val();
                    
                    if (value && value !== 'all') {
                        params[param] = value;
                    }
                });
                
                // Add current view mode
                var currentView = $('.toggle-btn.active').data('view');
                if (currentView) {
                    params['view'] = currentView;
                }
                
                // If search term exists, add it
                var urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('search')) {
                    params['search'] = urlParams.get('search');
                }
                
                return params;
            },
            
            /**
             * Apply filters by redirecting with params
             */
            applyFilters: function(params) {
                // Build the URL
                var filterUrl = url.build('events');
                
                // Redirect to filtered URL
                window.location.href = filterUrl + (Object.keys(params).length > 0 ? '?' + $.param(params) : '');
            },
            
            /**
             * Handle view toggle button click
             */
            handleViewToggle: function(e) {
                var viewUrl = $(e.currentTarget).data('url');
                if (viewUrl) {
                    window.location.href = viewUrl;
                }
            },
            
            /**
             * Handle campus tab click
             */
            handleCampusTabClick: function(e) {
                var campus = $(e.currentTarget).data('campus');
                $('.tab-btn').removeClass('active');
                $(e.currentTarget).addClass('active');
                
                $('.event-list').removeClass('active');
                $('.event-list[data-campus="' + campus + '"]').addClass('active');
            },
            
            /**
             * Handle calendar cell click
             */
            handleCalendarCellClick: function(e) {
                $('.calendar-cell').removeClass('active');
                $(e.currentTarget).addClass('active');
                
                var date = $(e.currentTarget).data('date');
                if (date) {
                    this.loadEventsForDate(date);
                }
            },
            
            /**
             * Load events for a specific date
             */
            loadEventsForDate: function(date) {
                $.ajax({
                    url: url.build(this.config.ajaxUrl + '/calendar'),
                    data: { date: date },
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update the today's events section with the response data
                            var dateObj = new Date(date);
                            var options = { year: 'numeric', month: 'long', day: 'numeric' };
                            var formattedDate = dateObj.toLocaleDateString('en-US', options);
                            
                            $('.today-events h4').text($t('Events on %1').replace('%1', formattedDate));
                            
                            if (response.events && response.events.length > 0) {
                                var eventsHtml = '';
                                $.each(response.events, function(index, event) {
                                    eventsHtml += '<div class="today-event-item">';
                                    eventsHtml += '<span class="event-time">' + event.time + '</span>';
                                    eventsHtml += '<div class="event-info">';
                                    eventsHtml += '<h5 class="event-name"><a href="' + event.url + '">' + event.title + '</a></h5>';
                                    eventsHtml += '<span class="event-location"><i class="material-icons">' + event.location_icon + '</i> ' + event.location + '</span>';
                                    eventsHtml += '</div>';
                                    eventsHtml += '<span class="event-category-indicator ' + event.category_class + '"></span>';
                                    eventsHtml += '</div>';
                                });
                                $('.today-event-list').html(eventsHtml);
                            } else {
                                $('.today-event-list').html('<div class="no-events-today"><p>' + $t('No events scheduled for this date.') + '</p></div>');
                            }
                        }
                    }
                });
            },
            
            /**
             * Handle calendar navigation buttons
             */
            handleCalendarNavigation: function(e) {
                var action = $(e.currentTarget).data('action');
                var currentMonth = parseInt($('.calendar-month').data('month') || new Date().getMonth() + 1);
                var currentYear = parseInt($('.calendar-month').data('year') || new Date().getFullYear());
                
                var newMonth, newYear;
                
                if (action === 'prev-month') {
                    newMonth = currentMonth === 1 ? 12 : currentMonth - 1;
                    newYear = currentMonth === 1 ? currentYear - 1 : currentYear;
                } else {
                    newMonth = currentMonth === 12 ? 1 : currentMonth + 1;
                    newYear = currentMonth === 12 ? currentYear + 1 : currentYear;
                }
                
                this.loadCalendarMonth(newMonth, newYear);
            },
            
            /**
             * Load a specific calendar month
             */
            loadCalendarMonth: function(month, year) {
                $.ajax({
                    url: url.build(this.config.ajaxUrl + '/calendar'),
                    data: { month: month, year: year, format: 'calendar' },
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update calendar month header
                            $('.calendar-month')
                                .text(response.calendar.monthName + ' ' + response.calendar.year)
                                .data('month', response.calendar.month)
                                .data('year', response.calendar.year);
                            
                            // Update calendar grid
                            var calendarHtml = '';
                            $.each(response.calendar.weeks, function(weekIndex, week) {
                                $.each(week, function(dayIndex, day) {
                                    var cellClass = [];
                                    if (!day.current) cellClass.push('disabled');
                                    if (day.events > 0) cellClass.push('has-event');
                                    if (day.isToday) cellClass.push('active');
                                    
                                    calendarHtml += '<div class="calendar-cell ' + cellClass.join(' ') + '" data-date="' + day.date + '">';
                                    calendarHtml += day.day;
                                    
                                    if (day.events > 0 && day.eventCategories) {
                                        $.each(day.eventCategories, function(i, category) {
                                            if (i < 3) { // Limit to 3