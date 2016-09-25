<?php

namespace Amc\Consultation\Block\Adminhtml\Consultation\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Amc\Consultation\Model\Layout
     */
    protected $consultationLayout;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amc\Consultation\Model\Layout $consultationLayout,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->consultationLayout = $consultationLayout;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_consultation');
    }

    protected function _prepareForm()
    {
        $model = $this->getCurrentConsultation();
        $isEditMode = (null !== $model && $model->getId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post'
                ]
            ]
        );

        $customer = $this->getCustomer();
        $fullName = implode(' ', [$customer->getLastname(), $customer->getFirstname(), $customer->getMiddlename()]);
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('%1 for %2',$this->getProduct()->getName(), $fullName), 'class' => 'fieldset-wide']
        );

        $fieldset->addField('order_id', 'hidden', ['name' => 'order_id', 'value' => $this->_request->getParam('order_id')]);
        $fieldset->addField('product_id', 'hidden', ['name' => 'product_id', 'value' => $this->_request->getParam('product_id')]);
        $fieldset->addField('order_item_id', 'hidden', ['name' => 'order_item_id', 'value' => $this->_request->getParam('order_item_id')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::MEDIUM);
//        'date_format' => $dateFormat,
//        'time_format' => $timeFormat,
//        'value' => $this->_localeDate->date(new \DateTime('now'))->format(\IntlDateFormatter::SHORT)
//        for wysiwyg:
//          'config' => []

        // todo accomplish wysiwyg fields
        // todo accomplish date fields

//        $fieldset->addType('protocol', 'Amc\Protocol\Block\Adminhtml\Renderer');
//        $fieldset->addField(
//            'dialog',
//            'protocol',
//            [
//                'name' => 'dialog',
//                'label' => __(''),
//                'title' => __(''),
//                'required' => false,
//            ]
//        );


        $layoutConfig = $this->getLayoutConfig();
        foreach ($layoutConfig['fields'] as $field) {
            $type = $field['type'];
            unset($field['type']);
            $fieldOptions = $field;
            if ($type == 'date') {
                $fieldOptions['date_format'] = $dateFormat;
                $fieldOptions['time_format'] = $timeFormat;
            }
            $fieldset->addField($field['name'], $type, $fieldOptions);
        }

//

        if (null !== $model) {
            $form->setValues($model->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Amc\Consultation\Model\Consultation|null
     */
    private function getCurrentConsultation()
    {
        return $this->_coreRegistry->registry('current_consultation');
    }

    /**
     * @return \Magento\Customer\Model\Data\Customer
     */
    private function getCustomer()
    {
        return $this->_coreRegistry->registry('current_customer');
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @return \Magento\Customer\Model\Data\Customer
     */
    private function getLayoutConfig()
    {
        $layoutName = $this->getProduct()->getData('consultation_layout_name');
        if (!$layoutName) {
            $layoutName = 'generic';
        }
        return $this->consultationLayout->getLayoutConfig($layoutName);
    }
}
