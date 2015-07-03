/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiComponent'
], function (_, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            visible: true,
            label: '',
            required: false,
            template: 'ui/group/group',
            fieldTemplate: 'ui/form/field',
            breakLine: true,
            validateWholeGroup: false
        },

        /**
         * Extends this with defaults and config.
         * Then calls initObservable, iniListenes and extractData methods.
         */
        initialize: function () {
            return this._super();
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         *
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this._super()
                .observe('visible required');

            return this;
        },

        /**
         * Defines if group has only one element.
         * @return {Boolean}
         */
        isSingle: function () {
            return this.elems.getLength() === 1;
        },

        /**
         * Defines if group has multiple elements.
         * @return {Boolean}
         */
        isMultiple: function () {
            return this.elems.getLength() > 1;
        }
    });
});
