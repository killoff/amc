<?php

namespace Amc\Consultation\Block\Adminhtml\Consultation;

class ProductSelection extends \Magento\Backend\Block\Template
{
    protected $_template = 'Amc_Consultation::product_selection.phtml';

    /** @var \Magento\Backend\Model\Auth\Session */
    protected $backendAuthSession;

    /** @var \Amc\User\Model\UserProductLink */
    protected $userProductLink;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection */
    protected $productCollectionFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection */
    protected $categoryCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Amc\User\Model\UserProductLink $userProductLink,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->backendAuthSession = $backendAuthSession;
        $this->userProductLink = $userProductLink;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }


    protected function _prepareLayout()
    {
        print_r($this->userProductLink->getUserProducts($this->getUser()));
        /** @var \Magento\Backend\Block\Widget\Accordion $acc */
        $acc = $this->_layout->createBlock('Magento\Backend\Block\Widget\Accordion');
        $acc->addItem('one', ['title' => "Консультации", 'content'

        => '
        <button id="save" title="Save Category" type="button" class=" scalable  save-category" data-ui-id="category-edit-form-save-button">
    <span>'.$this->backendAuthSession->getUser()->getName().' Консультация ревматолога</span>
</button>
<button id="save" title="Save Category" type="button" class=" scalable  save-category" data-ui-id="category-edit-form-save-button">
    <span>Консультация сексопатолога</span>
</button><br/><br/>
        '

        ]);        $acc->addItem('one2', ['title' => "Консультации", 'content'

        => '
        <button id="save" title="Save Category" type="button" class="action-default scalable primary save-category" data-ui-id="category-edit-form-save-button">
    <span>Консультация ревматолога</span>
</button>
<button id="save" title="Save Category" type="button" class="action-default scalable primary save-category" data-ui-id="category-edit-form-save-button">
    <span>Консультация сексопатолога</span>
</button><br/><br/>
        '

        ]);        $acc->addItem('one3', ['title' => "Консультации", 'content'

        => '
        <button id="save" title="Save Category" type="button" class="action-default scalable primary save-category" data-ui-id="category-edit-form-save-button">
    <span>Консультация ревматолога</span>
</button>
<button id="save" title="Save Category" type="button" class="action-default scalable primary save-category" data-ui-id="category-edit-form-save-button">
    <span>Консультация сексопатолога</span>
</button><br/><br/>
        '

        ]);
//        $acc->addItem('one2', ['title' => "One acc item2", 'One acc content2']);
//        $acc->addItem('one3', ['title' => "One acc item4", 'One acc content3']);
        $this->setAccordionsHtml($acc->toHtml());
    }


    protected function getUser()
    {
        return $this->backendAuthSession->getUser();
    }

    protected function getCategoriesWithProducts()
    {

        $productCollection = $this->productCollectionFactory->create();
        $filterProductIds = $this->userProductLink->getUserProducts($this->getUser());
        $productCollection->addIdFilter($filterProductIds);
        $categoryIds = [];
        foreach ($productCollection as $product) {
            $categoryIds = arra
        }
    }

}
