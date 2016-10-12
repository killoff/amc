<?php

namespace Amc\Consultation\Block\Adminhtml\Consultation\Edit;

use Magento\Framework\Exception\NoSuchEntityException;

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
        $model = $this->getConsultation();
        $isEditMode = (null !== $model && $model->getId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getData('action'),
                ]
            ]
        );

        $customer = $this->getCustomer();
        $fullName = implode(' ', [$customer->getLastname(), $customer->getFirstname(), $customer->getMiddlename()]);
        $fieldSet = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('%1 for %2',$this->getOrderItem()->getName(), $fullName), 'class' => 'fieldset-wide']
        );

        $fieldSet->addField('order_id', 'hidden', ['name' => 'order_id', 'value' => $this->getOrder()->getId()]);
        $fieldSet->addField('product_id', 'hidden', ['name' => 'product_id', 'value' => $this->getProduct()->getId()]);
        $fieldSet->addField('order_item_id', 'hidden', ['name' => 'order_item_id', 'value' => $this->getOrderItem()->getId()]);

        $fieldSet->addType('protocol', 'Amc\Protocol\Block\Adminhtml\Renderer');

        $layoutConfig = $this->getLayoutConfig();
        foreach ($layoutConfig['fields'] as $field) {
            $type = $field['type'];
            $fieldConfig = $this->getFieldConfig($field);
            $fieldSet->addField($field['name'], $type, $fieldConfig);
        }

        if (null !== $model) {
            $form->setValues($model->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getConsultation()
    {
        $consultation = $this->_coreRegistry->registry('current_consultation');
        if (null === $consultation) {
            throw new NoSuchEntityException('Consultation object not found in registry');
        }
        return $consultation;
    }

    /**
     * @return \Magento\Customer\Model\Data\Customer
     */
    private function getCustomer()
    {
        return $this->getConsultation()->getCustomer();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        return $this->getConsultation()->getProduct();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder()
    {
        return $this->getConsultation()->getOrder();
    }

    /**
     * @return \Magento\Sales\Model\Order\Item
     */
    private function getOrderItem()
    {
        return $this->getConsultation()->getOrderItem();
    }

    /**
     * @return \Magento\Customer\Model\Data\Customer
     */
    private function getLayoutConfig()
    {
        $layoutName = $this->getProduct()->getData('consultation_layout');
        try {
            return $this->consultationLayout->getLayoutConfig($layoutName);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    private function getFieldConfig($options)
    {
//        'value' => $this->_localeDate->date(new \DateTime('now'))->format(\IntlDateFormatter::SHORT)
//        for wysiwyg:
//          'config' => []


        // accomplish complex UI elements with necessary options
        $type = $options['type'];
        switch ($type) {
            case 'date':
                $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
                $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::MEDIUM);
                $options['date_format'] = $dateFormat;
                $options['time_format'] = $timeFormat;
                break;
            case 'editor':
                // ...
                break;
            case 'protocol':
                // ...
                break;
            default:
                break;
        }
        unset($options['type']);

        // wrap all option names with data[]
        $options['name'] = 'data[' . $options['name'] . ']';

        return $options;
    }
}
