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
                    this.queue(response);
                    this.log('loaded queue on init', this.queue());
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
