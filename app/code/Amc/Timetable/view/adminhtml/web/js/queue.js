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
                this.form_key = $.cookie('form_key');

                $.getJSON( this.source_url, function(response) {
                    var queue = response.map(function (customer) {
                        console.log(customer);
                        customer.events = customer.events.map(function (e) {
                            e.start_at_time = moment(e.start_at).format('HH:mm');
                            e.minutes =  moment.duration(moment(e.end_at).diff(moment(e.start_at))).asMinutes();
                            return e;
                        });
                        return customer;
                    });
                    this.queue(queue);
                    this.log('loaded queue on init', this.queue());
                }.bind(this))
                .fail(function(exception) {
                    this._handleFail(exception);
                }.bind(this));
            },

            changeStatus: function () {
                var changeStatusForm = $("#change-status-form");
                this.log('open', 'changeStatus');
                changeStatusForm.modal({
                    //modalClass: 'modal-timetable modal-' + proposal.id + '-is-initialized',
                    autoOpen: true,
                    buttons: [{
                        text: 'Close',
                        class: '',
                        click: function() {
                            //this.closeModal();
                        }
                    }],
                    opened: function () {
                        this.log('proposal was opened: ' + proposal.id);
                    }.bind(this),
                    closed: function () {
                        this.log('proposal was closed: ' + proposal.id);
                        //this.stopTimer();
                        //this.packagesResult([]);
                    }.bind(this)
                });

                changeStatusForm.modal('openModal');
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
