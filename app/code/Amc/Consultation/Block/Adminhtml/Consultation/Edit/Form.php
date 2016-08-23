<?php

namespace Amc\Consultation\Block\Adminhtml\Consultation\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Amc\User\Model\UserProductLink
     */
    protected $relationManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Amc\User\Model\UserProductLink $relationManager
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amc\User\Model\UserProductLink $relationManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = [],
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->_productFactory = $productFactory;
        $this->relationManager = $relationManager;
        $this->authSession = $authSession;
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

        $fieldset->addField(
            'user_date',
            'date',
            [
                'name' => 'user_date',
                'label' => __('Date'),
                'id' => 'user_date',
                'title' => __('Date'),
                'date_format' => $dateFormat,
//                'time_format' => $timeFormat,
                'disabled' => $isEditMode,
//                'value' => $this->_localeDate->date(new \DateTime('now'))->format(\IntlDateFormatter::SHORT)
            ]
        );

        $fieldset->addField(
            'comment',
            'editor',
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
                'state' => 'html',
                'wysiwyg' => true,
                'required' => false,
                'style' => 'height: 250px;',
                'config' => []
            ]
        );

        $fieldset->addField(
            'recommendation',
            'editor',
            [
                'name' => 'recommendation',
                'label' => __('Recommendation'),
                'title' => __('Recommendation'),
                'wysiwyg' => true,
                'style' => 'height: 250px;',
                'required' => false
            ]
        );

        $fieldset->addType('protocol', 'Amc\Protocol\Block\Adminhtml\Renderer');

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
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @param null|string $emptyElement
     * @return array
     */
    public function getProductCollectionOptions($emptyElement = null)
    {
        $options = [];

        $productIds = $this->relationManager->getUserProducts(
            $this->authSession->getUser()
        );

        $collection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('name')
            ->addIdFilter($productIds);
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
