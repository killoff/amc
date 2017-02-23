define([
    "jquery",
    "jquery/ui",
    'Magento_Ui/js/modal/modal',
    "mage/translate"
], function($){
    "use strict";
    $.widget('mage.orderItemCancelDialog', {
        options: {
            url:     null,
            message: null,
            modal:  null
        },

        /**
         * @protected
         */
        _create: function () {
            this._prepareDialog();
        },

        /**
         * Show modal
         */
        showDialog: function() {
            this.options.dialog.html(this.options.message).modal('openModal');
        },

        /**
         * Redirect to edit page
         */
        redirect: function() {
            window.location = this.options.url;
        },

        /**
         * Prepare modal
         * @protected
         */
        _prepareDialog: function() {
            var self = this;

            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>').modal({
                type: 'popup',
                modalClass: 'item-cancel-popup',
                title: $.mage.__('Cancel Item?'),
                buttons: [{
                    text: $.mage.__('Yes, cancel'),
                    'class': 'action-primary',
                    click: function(){
                        self.redirect();
                    }
                }]
            });
        }
    });

    return $.mage.orderItemCancelDialog;
});
