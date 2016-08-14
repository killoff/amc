<?php
// @codingStandardsIgnoreFile

namespace Amc\User\Block\User\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Address extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('permissions_user');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('address_');

        $baseFieldset = $form->addFieldset('address_fieldset', ['legend' => __('Address Information')]);

        $baseFieldset->addField(
            'user_fathername',
            'text',
            [
                'name' => 'user_fathername',
                'label' => __('Father\'s Name'),
                'id' => 'fathername',
                'title' => __('Father\'s Name'),
                'required' => false
            ]
        );

        $baseFieldset->addField(
            'user_position',
            'text',
            [
                'name' => 'user_position',
                'label' => __('Position'),
                'id' => 'position',
                'title' => __('Position'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'user_phone',
            'text',
            [
                'name' => 'user_phone',
                'label' => __('Phone'),
                'id' => 'phone',
                'title' => __('Phone'),
            ]
        );

        $baseFieldset->addField(
            'user_country',
            'text',
            [
                'name' => 'user_country',
                'label' => __('Country'),
                'id' => 'country',
                'title' => __('Country')
            ]
        );

        $baseFieldset->addField(
            'user_city',
            'text',
            [
                'name' => 'user_city',
                'label' => __('City'),
                'id' => 'city',
                'title' => __('City')
            ]
        );

        $baseFieldset->addField(
            'user_street',
            'text',
            [
                'name' => 'user_street',
                'label' => __('Street'),
                'id' => 'street',
                'title' => __('Street')
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $baseFieldset->addField(
            'user_dob',
            'date',
            [
                'name' => 'user_dob',
                'label' => __('Date of Birth'),
                'id' => 'dob',
                'title' => __('Date of Birth'),
                'date_format' => $dateFormat
            ]
        );

        $baseFieldset->addField(
            'user_license_valid_date',
            'date',
            [
                'name' => 'user_license_valid_date',
                'label' => __('License Valid Date'),
                'id' => 'license_valid_date',
                'title' => __('License Valid Date'),
                'date_format' => $dateFormat
            ]
        );

        $data = $model->getData();
        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
