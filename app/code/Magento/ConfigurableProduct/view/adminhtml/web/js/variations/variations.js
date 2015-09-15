/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore'
], function (Component, $, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            opened: false,
            attributes: [],
            productMatrix: [],
            variations: [],
            productAttributes: [],
            rowIndexToEdit: false,
            productAttributesMap: null,
            modules: {
                associatedProductsFilter: '${ $.associatedProductsFilter }',
                associatedProductsProvider: '${ $.associatedProductsProvider }'
            }
        },
        initialize: function () {
            this._super();
            if (this.variations.length) {
                this.render(this.variations, this.productAttributes);
            }
            this.initProductAttributesMap();
        },
        /**
         * Select different product in configurations section
         * @param rowIndex
         */
        selectProduct: function (rowIndex) {
            var productToEdit = this.productMatrix.splice(this.rowIndexToEdit, 1)[0],
                newProduct = this.associatedProductsProvider().data.items[rowIndex];

            newProduct = _.extend(productToEdit, newProduct);
            newProduct.productId = productToEdit.entity_id;
            newProduct.productUrl = this.buildProductUrl(newProduct.entity_id);
            newProduct.editable = false;
            newProduct.images = {preview: newProduct.thumbnail_src};
            this.productAttributesMap[this.getVariationKey(newProduct.options)] = newProduct.productId;
            this.productMatrix.splice(this.rowIndexToEdit, 0, newProduct);
            $('#associated-products-container').trigger('closeModal');
        },
        initObservable: function () {
            this._super().observe('actions opened attributes productMatrix');
            return this;
        },
        getProductValue: function (name) {
            return $('[name="product[' + name.split('/').join('][') + ']"]', this.productForm).val();
        },
        getRowId: function (data, field) {
            var key = data.variationKey;
            return 'variations-matrix-' + key + '-' + field;
        },
        getVariationRowName: function(variation, field) {
            if (variation.productId) {
                return 'configurations[' + variation.productId + '][' + field.split('/').join('][') + ']';
            } else {
                var key = variation.variationKey;
                return 'variations-matrix[' + key + '][' + field.split('/').join('][') + ']';
            }
        },
        getAttributeRowName: function (attribute, field) {
            return 'product[configurable_attributes_data][' + attribute.id + '][' + field + ']';
        },
        getOptionRowName: function (attribute, option, field) {
            return 'product[configurable_attributes_data][' + attribute.id + '][values][' + option.value + ']['
                + field + ']';
        },
        render: function (variations, attributes) {
            this.changeButtonWizard();
            this.populateVariationMatrix(variations);
            this.attributes(attributes);
            this.initImageUpload();
            this.disableConfigurableAttributes(attributes);
        },
        changeButtonWizard: function () {
            var $button = $('[data-action=open-steps-wizard] [data-role=button-label]');
            $button.text($button.attr('data-edit-label'));
        },
        getAttributesOptions: function () {
            return this.showVariations() ? this.productMatrix()[0].options : [];
        },
        showVariations: function () {
            return this.productMatrix().length > 0;
        },
        populateVariationMatrix: function (variations) {
            this.productMatrix([]);
            _.each(variations, function (variation) {
                var attributes = _.reduce(variation.options, function (memo, option) {
                    var attribute = {};
                    attribute[option.attribute_code] = option.value;
                    return _.extend(memo, attribute);
                }, {});
                this.productMatrix.push(_.extend(variation, {
                    productId: variation.productId || null,
                    name: variation.name || variation.sku,
                    weight: variation.weight,
                    attribute: JSON.stringify(attributes),
                    variationKey: _.values(attributes).join('-'),
                    editable: variation.editable === undefined ? !variation.productId : variation.editable,
                    productUrl: this.buildProductUrl(variation.productId),
                    status: variation.status === undefined ? 1 : parseInt(variation.status)
                }));
            }, this);
        },
        buildProductUrl: function (productId) {
            return this.productUrl.replace('%id%', productId);
        },
        removeProduct: function (rowIndex) {
            this.opened(false);
            this.productMatrix.splice(rowIndex, 1);
            if (this.productMatrix().length === 0) {
                this.attributes.each(function(attribute) {
                    $('[data-attribute-code="' + attribute.code + '"] select').removeProp('disabled');
                });
            }
        },
        showGrid: function (rowIndex) {
            var attributes = JSON.parse(this.productMatrix()[rowIndex].attribute);
            this.rowIndexToEdit = rowIndex;
            this.associatedProductsProvider().params.attribute_ids = _.keys(attributes);
            this.associatedProductsFilter().set('filters', attributes).apply();
            $('#associated-products-container').trigger('openModal');
        },
        toggleProduct: function (rowIndex) {
            var productChanged = {};
            if (this.productMatrix()[rowIndex].editable) {
                var row = $('[data-row-number=' + rowIndex + ']');
                _.each('name,sku,qty,weight,price'.split(','), function (column) {
                    productChanged[column] = $(
                        'input[type=text]',
                        row.find($('[data-column="%s"]'.replace('%s', column)))
                    ).val();
                });
            }
            var product = this.productMatrix.splice(rowIndex, 1)[0];
            product = _.extend(product, productChanged);
            product.status = !product.status * 1;
            this.productMatrix.splice(rowIndex, 0, product);
        },
        toggleList: function (rowIndex) {
            var state = false;
            if (rowIndex !== this.opened()) {
                state = rowIndex;
            }
            this.opened(state);

            return this;
        },
        closeList: function (rowIndex) {
            if (this.opened() === rowIndex()) {
                this.opened(false);
            }

            return this;
        },
        getVariationKey: function (options) {
            return _.pluck(options, 'value').sort().join('-');
        },
        getProductIdByOptions: function (options) {
            return this.productAttributesMap[this.getVariationKey(options)] || null;
        },
        initProductAttributesMap: function () {
            if (null === this.productAttributesMap) {
                this.productAttributesMap = {};
                _.each(this.variations, function(product) {
                    this.productAttributesMap[this.getVariationKey(product.options)] = product.productId;
                }.bind(this));
            }
        },
        isShowPreviewImage: function (variation) {
            return variation.images.preview && (!variation.editable || variation.images.file);
        },
        generateImageGallery: function (variation) {
            var gallery = [];
            var imageFields = ['position', 'file', 'disabled', 'label'];
            _.each(variation.images.images, function (image) {
                _.each(imageFields, function (field) {
                    gallery.push(
                        '<input type="hidden" name="'
                        + this.getVariationRowName(variation, 'media_gallery/images/' + image.file_id + '/' + field)
                        + '" value="' + (image[field] || '') + '" />'
                    );
                }, this);
                _.each(image.galleryTypes, function (imageType) {
                    gallery.push(
                        '<input type="hidden" name="' + this.getVariationRowName(variation, imageType)
                        + '" value="' + image.file + '" />'
                    );
                }, this);
            }, this);
            return gallery.join('\n');
        },
        initImageUpload: function () {
            require([
                "jquery",
                "mage/template",
                "jquery/file-uploader",
                "mage/mage",
                "mage/translate"
            ], function (jQuery, mageTemplate) {

                jQuery(function ($) {
                    var matrix = $('[data-role=product-variations-matrix]');
                    matrix.find('[data-action=upload-image]').find('[name=image]').each(function () {
                        var imageColumn = $(this).closest('[data-column=image]');
                        if (imageColumn.find('[data-role=image]').length) {
                            imageColumn.find('[data-toggle=dropdown]').dropdown().show();
                        }
                        $(this).fileupload({
                            dataType: 'json',
                            dropZone: $(this).closest('[data-role=row]'),
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                            done: function (event, data) {
                                var tmpl;

                                if (!data.result) {
                                    return;
                                }
                                if (!data.result.error) {
                                    var parentElement = $(event.target).closest('[data-column=image]'),
                                        uploaderControl = parentElement.find('[data-action=upload-image]'),
                                        imageElement = parentElement.find('[data-role=image]');

                                    if (imageElement.length) {
                                        imageElement.attr('src', data.result.url);
                                    } else {
                                        tmpl = mageTemplate(matrix.find('[data-template-for=variation-image]').html());

                                        $(tmpl({
                                            data: data.result
                                        })).prependTo(uploaderControl);
                                    }
                                    parentElement.find('[name$="[image]"]').val(data.result.file);
                                    parentElement.find('[data-toggle=dropdown]').dropdown().show();
                                } else {
                                    alert($.mage.__('We don\'t recognize or support this file extension type.'));
                                }
                            },
                            start: function (event) {
                                $(event.target).closest('[data-action=upload-image]').addClass('loading');
                            },
                            stop: function (event) {
                                $(event.target).closest('[data-action=upload-image]').removeClass('loading');
                            }
                        });
                    });
                    matrix.find('[data-action=no-image]').click(function (event) {
                        var parentElement = $(event.target).closest('[data-column=image]');
                        parentElement.find('[data-role=image]').remove();
                        parentElement.find('[name$="[image]"]').val('');
                        parentElement.find('[data-toggle=dropdown]').trigger('close.dropdown').hide();
                    });
                });
            });
        },
        disableConfigurableAttributes: function(attributes) {
            _.each(attributes, function (attribute) {
                $('[data-attribute-code="' + attribute.code + '"] select').prop('disabled', true);
            });
        }
    });
});
