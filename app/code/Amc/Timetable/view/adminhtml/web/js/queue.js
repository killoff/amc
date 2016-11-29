define(
    [
        'jquery',
        'ko',
        'moment',
        'uiComponent'
    ],
    function ($, ko, moment, Component) {

        return Component.extend({

            initialize: function () {
                this._super();
                this.debug = true;
                this.queue = ko.observableArray([]);
                this.current_customer_id = ko.observable();
                this.current_customer_name = ko.observable();
                this.customer_invoices = ko.observableArray([]);
                // hack to observe object => wrap object with array
                this.customer_totals = ko.observableArray([]);
                this.form_key = $.cookie('form_key');
                this.reload();
            },

            changeStatus: function (entity) {
                var statusForm = $("#change-status-form");
                statusForm.find('input[type="radio"]').prop('checked', false);
                statusForm.find('.customer-name').html(entity.customer.name);
                statusForm.find('.customer-status').html(entity.customer.status);
                statusForm.find('#customer_id').val(entity.customer.id);
                statusForm.modal({
                    autoOpen: true,
                    buttons: [
                        {
                            text: 'Apply',
                            class: 'primary',
                            click: this.changeStatusSubmit.bind(this)
                        },
                        {
                            text: 'Cancel',
                            click: this.closeModal
                        }
                    ],
                    opened: function () {
                    }.bind(this),
                    closed: function () {
                    }.bind(this)
                });

                statusForm.modal('openModal');
            },

            changeStatusSubmit: function() {
                var statusForm = $("#change-status-form");
                var newStatus = statusForm.find('input[name="status"]:checked').val();
                var customerId = statusForm.find('#customer_id').val();
                var data = {customer_id: customerId, status: newStatus, form_key: FORM_KEY};
                this.log('data', data);
                this.log('form', FORM_KEY);
                $.ajax({
                    url: this.change_status_url,
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        this.log('response', response);
                        this.reload();
                        statusForm.modal('closeModal');
                    }.bind(this)
                })
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            invoice: function(entity) {
                $.getJSON( this.invoice_url, {customer_id: entity.customer.id}, function(response) {
                    //var orders = response.map(function (order) {
                    //    order.items = order.items.map(function (item) {
                    //        //item.start_at_time = moment(item.start_at).format('HH:mm');
                    //        //item.minutes =  moment.duration(moment(item.end_at).diff(moment(item.start_at))).asMinutes();
                    //        return item;
                    //    });
                    //    return order;
                    //});
                    this.log('invoices', response.invoices);
                    this.log('totals', response.totals);
                    this.customer_invoices(response.invoices);
                    this.customer_totals([response.totals]);
                    this.current_customer_id(entity.customer.id);
                    this.current_customer_name(entity.customer.name);

                    var invoiceForm = $("#invoice-form");
                    invoiceForm.find('.customer-name').html(entity.customer.name);
                    invoiceForm.modal({
                        autoOpen: true,
                        buttons: [
                            {
                                text: 'Pay',
                                class: 'primary',
                                id: 'pay-btn',
                                click: this.pay.bind(this)
                            },
                            {
                                text: 'Cancel',
                                click: this.closeModal
                            }
                        ],
                        opened: function () {
                        }.bind(this),
                        closed: function () {
                        }.bind(this)
                    });

                    invoiceForm.modal('openModal');

                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            updateQty: function() {
                var queryParams = jQuery('#invoice-form').find('input, select').serialize();
                $.post(this.invoice_url, queryParams, function (response) {
                    this.customer_invoices(response.invoices);
                    this.customer_totals([response.totals]);
                    this.log('update items qty', response.invoices);
                    $('.modal-footer .primary').removeClass('disabled');
                    $('#update-qty-btn').removeClass('primary');
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            changeQty: function() {
                $('.modal-footer .primary').addClass('disabled');
                $('#update-qty-btn').addClass('primary');
            },

            pay: function() {
                $('.modal-footer .primary').addClass('disabled');
                $('#update-qty-btn').addClass('disabled');
                var queryParams = jQuery('#invoice-form').find('input, select').serialize();
                $.post(this.pay_url, queryParams, function (response) {
                    var invoicedOrderItems = response.map(function(item) {
                        return item.order_item_id;
                    });
                    var oldCustomerInvoices = this.customer_invoices();
                    var newCustomerInvoices = [];
                    var invoiceItems = [];
                    $.each(oldCustomerInvoices, function(i, invoice) {
                        invoiceItems = invoice.items.filter(function(item) {
                            return invoicedOrderItems.indexOf(item.order_item_id) !== -1;
                        });
                        if (invoiceItems.length > 0) {

                            invoice.items = invoiceItems;
                            invoice.paid = true;
                            newCustomerInvoices.push(invoice);
                            console.log('new invoice:');
                            console.log(invoice);
                        }
                    });
                    this.customer_invoices(newCustomerInvoices);
                    this.log('pay response', response);
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            reload: function() {
                $.getJSON( this.source_url, function(response) {
                    var queue = response.map(function (entity) {
                        entity.events = entity.events.map(function (e) {
                            e.start_at_time = moment(e.start_at).format('HH:mm');
                            e.minutes =  moment.duration(moment(e.end_at).diff(moment(e.start_at))).asMinutes();
                            return e;
                        });
                        entity.customer.url = this.edit_customer_url_prefix + entity.customer.id;
                        return entity;
                    }.bind(this));
                    this.queue(queue);
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            _handleFail: function (exception) {
                if(exception.responseJSON.error) {
                    alert(exception.responseJSON.error);
                }
                this.log('exception:', exception.responseJSON.error);
            },

            log: function (message, data) {
                if (this.debug) {
                    console.log(message, data);
                }
            }
        });
    }
);
