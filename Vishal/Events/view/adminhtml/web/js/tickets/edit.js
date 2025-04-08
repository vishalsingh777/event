/**
 * tickets.js
 * Path: app/code/Vishal/Events/view/adminhtml/web/js/tickets/edit.js
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/html',
    'mage/translate'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Vishal_Events/tickets/edit',
            ticketRows: [],
            listens: {
                '${ $.provider }:data.manual_tickets': 'loadTickets'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            this.initTickets();
            return this;
        },

        /**
         * Initialize tickets
         */
        initTickets: function () {
            this.ticketRows([]);
            
            var data = this.source.get(this.dataScope);
            if (data && data.length) {
                this.loadTickets(data);
            }
        },

        /**
         * Load tickets data
         * 
         * @param {Array} tickets
         */
        loadTickets: function (tickets) {
            if (!tickets || !tickets.length) {
                return;
            }
            
            this.ticketRows([]);
            
            tickets.forEach(function (ticket, index) {
                this.ticketRows.push({
                    ticketId: ticket.ticket_id || index,
                    name: ticket.name || '',
                    sku: ticket.sku || '',
                    price: ticket.price || '',
                    position: ticket.position || 0
                });
            }, this);
        },

        /**
         * Add new ticket
         */
        addTicket: function () {
            var newId = 0;
            
            // Find the highest ID
            this.ticketRows().forEach(function (ticket) {
                if (ticket.ticketId > newId) {
                    newId = parseInt(ticket.ticketId, 10);
                }
            });
            
            newId++;
            
            this.ticketRows.push({
                ticketId: newId,
                name: '',
                sku: '',
                price: '',
                position: this.ticketRows().length
            });
        },

        /**
         * Remove ticket
         * 
         * @param {Object} ticket
         */
        removeTicket: function (ticket) {
            this.ticketRows.remove(ticket);
        },

        /**
         * Get input name
         * 
         * @param {string} field
         * @param {number} ticketId
         * @returns {string}
         */
        getInputName: function (field, ticketId) {
            return 'manual_tickets[' + ticketId + '][' + field + ']';
        },

        /**
         * Get input ID
         * 
         * @param {string} field
         * @param {number} ticketId
         * @returns {string}
         */
        getInputId: function (field, ticketId) {
            return 'manual_tickets_' + ticketId + '_' + field;
        }
    });
});