/**
 * events.js
 * Path: app/code/Vishal/Events/view/adminhtml/web/js/events.js
 */

define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'mage/translate'
], function ($, mageTemplate, _t) {
    'use strict';

    $.widget('vishal.eventTickets', {
        options: {
            ticketRowTemplate: '#ticket-row-template',
            ticketContainer: '#tickets-container',
            addButton: '#add-ticket-button',
            removeButton: '.remove-ticket',
            ticketPrefix: 'tickets'
        },

        /**
         * Widget creation
         * @private
         */
        _create: function () {
            this._initElements();
            this._bindEvents();
        },

        /**
         * Initialize elements
         * @private
         */
        _initElements: function () {
            this.ticketRowTemplate = mageTemplate(this.options.ticketRowTemplate);
            this.ticketContainer = $(this.options.ticketContainer);
            this.addButton = $(this.options.addButton);
            this.nextTicketId = 0;

            // Find the highest existing ticket index
            this.ticketContainer.find('[data-ticket-id]').each($.proxy(function (index, element) {
                var ticketId = parseInt($(element).attr('data-ticket-id'), 10);
                if (ticketId >= this.nextTicketId) {
                    this.nextTicketId = ticketId + 1;
                }
            }, this));
        },

        /**
         * Bind events
         * @private
         */
        _bindEvents: function () {
            this.addButton.on('click', $.proxy(this.addTicket, this));
            this.element.on('click', this.options.removeButton, $.proxy(this.removeTicket, this));
        },

        /**
         * Add a new ticket row
         */
        addTicket: function () {
            var data = {
                ticket_id: this.nextTicketId,
                prefix: this.options.ticketPrefix
            };
            
            this.ticketContainer.append(this.ticketRowTemplate({
                data: data
            }));
            
            this.nextTicketId++;
        },

        /**
         * Remove a ticket row
         * @param {Event} event
         */
        removeTicket: function (event) {
            $(event.target).closest('[data-ticket-id]').remove();
        }
    });

    return $.vishal.eventTickets;
});





