define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getLabel: function (row) {
            return row[this.index + '_html'];
        },
        getAjaxUrl: function (row) {
            return row[this.index + '_ajax_url'];
        },
        preview: function (row) {
            var self = this;
            $.get(this.getAjaxUrl(row), function(data) {
                var previewPopup = $('<div/>').html(data);
                previewPopup.modal({
                    innerScroll: true,
                    modalClass: '_image-box',
                    buttons: []}).trigger('openModal');
            });
        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});
