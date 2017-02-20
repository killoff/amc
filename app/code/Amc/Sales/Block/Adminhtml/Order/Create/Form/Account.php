<?php

// @codingStandardsIgnoreFile

namespace Amc\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Sales\Block\Adminhtml\Order\Create\Form\Account as RewrittenAccount;

/**
 * Create order account form
 */
class Account extends RewrittenAccount
{
    // attributes that are not required but nice to have
    private $exceptionalAttributes = ['dob', 'gender'];
    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Customer\Model\Metadata\Form $customerForm */
        $customerForm = $this->_metadataFormFactory->create('customer', 'adminhtml_checkout');

        // prepare customer attributes to show
        $attributes = [];

        // add system required attributes
        foreach ($customerForm->getSystemAttributes() as $attribute) {
            if ($attribute->isRequired() || in_array($attribute->getAttributeCode(), $this->exceptionalAttributes)) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        if ($this->getQuote()->getCustomerIsGuest()) {
            unset($attributes['group_id']);
        }

        // add user defined attributes
        foreach ($customerForm->getUserAttributes() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        $fieldset = $this->_form->addFieldset('main', []);

        $this->_addAttributesToForm($attributes, $fieldset);

        $this->_form->addFieldNameSuffix('order[account]');
        $this->_form->setValues($this->getFormValues());

        return $this;
    }
}
