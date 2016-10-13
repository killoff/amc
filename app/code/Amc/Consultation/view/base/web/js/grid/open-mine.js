define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Amc_Consultation/templates/grid/open.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, sendmailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getAjaxUrl: function (row) {
            return row[this.index + '_ajax_url'];
        },
        preview: function (row) {
            var self = this;
            $.get(this.getAjaxUrl(row), function(data) {
                var previewPopup = $('<div/>').html(data);
                previewPopup.modal({
                    title: self.getTitle(row),
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
