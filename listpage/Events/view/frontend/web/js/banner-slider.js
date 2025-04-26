define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        var $element = $(element),
            $slider = $('.banner-slider-container', $element),
            $slides = $('.banner-slide', $slider),
            $dots = $('.banner-dot', $element),
            $prevBtn = $('.banner-nav.prev', $element),
            $nextBtn = $('.banner-nav.next', $element),
            currentSlide = 0,
            slideCount = $slides.length,
            autoplayInterval,
            autoplayDelay = 5000; // 5 seconds

        /**
         * Initialize the slider
         */
        function init() {
            // Hide all slides except the first one
            $slides.hide();
            $slides.eq(0).show();

            // Set up navigation events
            $prevBtn.on('click', function () {
                goToSlide(currentSlide - 1);
            });

            $nextBtn.on('click', function () {
                goToSlide(currentSlide + 1);
            });

            $dots.on('click', function () {
                goToSlide($(this).data('slide'));
            });

            // Keyboard navigation
            $(document).on('keydown', function (e) {
                if ($element.is(':visible')) {
                    if (e.keyCode === 37) { // Left arrow
                        goToSlide(currentSlide - 1);
                    } else if (e.keyCode === 39) { // Right arrow
                        goToSlide(currentSlide + 1);
                    }
                }
            });

            // Start autoplay
            startAutoplay();

            // Pause autoplay on hover
            $element.hover(
                function () {
                    clearInterval(autoplayInterval);
                },
                function () {
                    startAutoplay();
                }
            );

            // Touch swipe support
            let touchStartX = 0;
            let touchEndX = 0;

            $slider.on('touchstart', function (e) {
                touchStartX = e.originalEvent.touches[0].clientX;
            });

            $slider.on('touchend', function (e) {
                touchEndX = e.originalEvent.changedTouches[0].clientX;
                handleSwipe();
            });

            function handleSwipe() {
                if (touchEndX < touchStartX - 50) {
                    // Swipe left
                    goToSlide(currentSlide + 1);
                } else if (touchEndX > touchStartX + 50) {
                    // Swipe right
                    goToSlide(currentSlide - 1);
                }
            }

            // Add accessibility features
            $slider.attr('aria-live', 'polite');
            $slides.each(function (index) {
                $(this).attr({
                    'role': 'tabpanel',
                    'aria-hidden': index === 0 ? 'false' : 'true',
                    'id': 'banner-slide-' + index
                });
            });

            $dots.each(function (index) {
                $(this).attr({
                    'role': 'tab',
                    'aria-selected': index === 0 ? 'true' : 'false',
                    'aria-controls': 'banner-slide-' + index,
                    'tabindex': index === 0 ? '0' : '-1'
                });
            });
        }

        /**
         * Go to a specific slide
         * @param {number} index - The slide index to go to
         */
        function goToSlide(index) {
            // Handle circular navigation
            if (index < 0) {
                index = slideCount - 1;
            } else if (index >= slideCount) {
                index = 0;
            }

            // Update slides
            $slides.eq(currentSlide).fadeOut(300);
            $slides.eq(index).fadeIn(300);

            // Update ARIA attributes
            $slides.eq(currentSlide).attr('aria-hidden', 'true');
            $slides.eq(index).attr('aria-hidden', 'false');

            // Update dots
            $dots.eq(currentSlide).removeClass('active')
                .attr('aria-selected', 'false')
                .attr('tabindex', '-1');
            $dots.eq(index).addClass('active')
                .attr('aria-selected', 'true')
                .attr('tabindex', '0');

            // Update current slide index
            currentSlide = index;

            // Reset autoplay timer
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                startAutoplay();
            }
        }

        /**
         * Start autoplay functionality
         */
        function startAutoplay() {
            autoplayInterval = setInterval(function () {
                goToSlide(currentSlide + 1);
            }, autoplayDelay);
        }

        // Initialize the slider
        if (slideCount > 0) {
            init();
        }
    };
});