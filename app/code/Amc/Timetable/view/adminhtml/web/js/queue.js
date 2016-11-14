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
                this.customer_dues = ko.observableArray([]);
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
                    this.log('orders', response);
                    this.customer_dues(response);

                    var invoiceForm = $("#invoice-form");
                    invoiceForm.find('.customer-name').html(entity.customer.name);
                    invoiceForm.modal({
                        autoOpen: true,
                        buttons: [
                            {
                                text: 'Pay',
                                class: 'primary',
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

            pay: function() {

            },

            reload: function() {
                $.getJSON( this.source_url, function(response) {
                    var queue = response.map(function (customer) {
                        customer.events = customer.events.map(function (e) {
                            e.start_at_time = moment(e.start_at).format('HH:mm');
                            e.minutes =  moment.duration(moment(e.end_at).diff(moment(e.start_at))).asMinutes();
                            return e;
                        });
                        return customer;
                    });
                    this.queue(queue);
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            _handleFail: function (exception) {
                var errorMessage = '';
                var errorCode = '';

                this.error('caught an exception', exception);

                // maybe a better way?
                if (exception && exception.responseJSON) {
                    errorMessage = exception.responseJSON.errorMessage? exception.responseJSON.errorMessage : exception.responseJSON.message? exception.responseJSON.message : exception.responseText;
                    errorCode = exception.responseJSON.errorCode? exception.responseJSON.errorCode : '';
                }
                else {
                    errorMessage = exception && exception.responseText? exception.responseText : exception && exception.statusText? exception.statusText : exception;
                }

                this.setAlert(errorMessage, errorCode, 'error');
            },

            error: function (message, data) {
                if (this.debug) {
                    console.error(message, data);
                }
            },

            log: function (message, data) {
                if (this.debug) {
                    console.log(message, data);
                }
            }
        });
    }
);
