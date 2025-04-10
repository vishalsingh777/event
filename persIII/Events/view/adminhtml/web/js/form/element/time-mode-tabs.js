define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select'
], function ($, _, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            elementTmpl: 'Vishal_Events/form/field/time-mode-tabs',
            activeTab: 'single'
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            
            // Initialize value if empty
            if (!this.value()) {
                this.value('single');
            }
            
            this.activeTab = this.value();
            
            return this;
        },
        
        /**
         * Called when element is rendered
         */
        initElement: function () {
            this._super();
            
            var self = this;
            
            setTimeout(function () {
                self.initTabs();
            }, 100);
            
            return this;
        },
        
        /**
         * Initialize tabs
         */
        initTabs: function () {
            var self = this;
            var $tabs = $('.time-mode-tab');
            
            // Set initial active tab
            $tabs.filter('[data-value="' + this.activeTab + '"]').addClass('active');
            
            // Add click event
            $tabs.on('click', function () {
                var value = $(this).data('value');
                
                // Update active tab
                $tabs.removeClass('active');
                $(this).addClass('active');
                
                // Update value
                self.value(value);
                self.activeTab = value;
                
                // Update time slots visibility
                self.toggleTimeSlotsMode(value);
            });
        },
        
        /**
         * Toggle time slots mode
         */
        toggleTimeSlotsMode: function (mode) {
            // Logic to show/hide appropriate time slot inputs
            // When dynamic rows are implemented, this will control visibility
            // For now, just a placeholder
        }
    });
});