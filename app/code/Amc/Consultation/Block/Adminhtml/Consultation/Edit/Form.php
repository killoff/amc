<?php

namespace Amc\Consultation\Block\Adminhtml\Consultation\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [],
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->_productFactory = $productFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_consultation');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->getCurrentConsultation();
        $isEditMode = (null !== $model && $model->getId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $customer = $this->getCustomer();
        $fullName = $customer->getLastname() . ' ' . $customer->getFirstname();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Consultation for %1', $fullName), 'class' => 'fieldset-wide']
        );

        if ($customer) {
            $fieldset->addField('customer_id', 'hidden', ['name' => 'customer_id', 'value' => $customer->getId()]);
        }

        if ($model) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'consultation_id', 'value' => $model->getId()]);
        }

        $fieldset->addField(
            'product_id',
            'select',
            [
                'name' => 'product_id',
                'label' => __('Product'),
                'title' => __('Product'),
                'values' => $this->getProductCollectionOptions(__('-- Please Select --')),
                'class' => 'select',
                'required' => true,
                'disabled' => $isEditMode
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('Date'),
                'id' => 'created_at',
                'title' => __('Date'),
                'date_format' => $dateFormat,
                'disabled' => $isEditMode
//                'value' => $this->_localeDate->date()->format(\IntlDateFormatter::SHORT)
            ]
        );

        $fieldset->addField(
            'comment',
            'text',
            [
                'name' => 'comment',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'conclusion',
            'editor',
            [
                'name' => 'conclusion',
                'label' => __('Conclusion'),
                'title' => __('Conclusion'),
                'wysiwyg' => false,
                'required' => false
            ]
        );

        $fieldset->addField(
            'recommendation',
            'editor',
            [
                'name' => 'recommendation',
                'label' => __('Recommendation'),
                'title' => __('Recommendation'),
                'wysiwyg' => false,
                'required' => false
            ]
        );

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
    public function getCurrentConsultation()
    {
        return $this->_coreRegistry->registry('current_consultation');
    }

    /**
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function getCustomer()
    {
        return $this->_coreRegistry->registry('current_customer');
    }

    /**
     * @param null|string $emptyElement
     * @return array
     */
    public function getProductCollectionOptions($emptyElement = null)
    {
        $options = [];
        /** @var  $collection */
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect('name');
        foreach ($collection as $product) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $options[$product->getId()] = $product->getName();
        }

        if ($emptyElement) {
            array_unshift($options, ['value' => '', 'label' => $emptyElement]);
        }

        return $options;
    }
}
