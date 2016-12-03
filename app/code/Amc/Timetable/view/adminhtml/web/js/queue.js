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

                // reload queue every 30 seconds
                window.setInterval(function() {
                    this.reload();
                }.bind(this), 30000);
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

            makeIn: function(entity) {
                this.submitStatus(entity.customer.id, '1');
            },

            makePending: function(entity) {
                this.submitStatus(entity.customer.id, '0');
            },

            submitStatus: function(customerId, newStatus) {
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

                    var invoiceForm = response.invoices.length > 0 ? $("#invoice-form") : $("#invoice-form-empty");
                    invoiceForm.find('.customer-name').html(entity.customer.name);
                    invoiceForm.modal({
                        autoOpen: true,
                        buttons: [
                            {
                                text: 'Pay',
                                class: 'primary' + (response.invoices.length > 0 ? '' : ' hidden'),
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
                queryParams = queryParams + '&context=update_qty';
                $.post(this.invoice_url, queryParams, function (response) {
                    this.customer_invoices(response.invoices);
                    this.customer_totals([response.totals]);
                    $('.modal-footer .primary').removeClass('disabled');
                    $('#update-qty-btn').removeClass('primary');
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            pay: function() {
                $('.modal-footer .primary').addClass('disabled');
                $('#update-qty-btn').addClass('disabled');
                var queryParams = jQuery('#invoice-form').find('input, select').serialize();
                queryParams = queryParams + '&context=pay';
                $.post(this.invoice_url, queryParams, function (response) {
                    this.customer_invoices(response.invoices);
                    this.customer_totals([response.totals]);
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            onChangeQty: function() {
                $('.modal-footer .primary').addClass('disabled');
                $('#update-qty-btn').addClass('primary');
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
