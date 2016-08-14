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

    /** @var \Magento\Framework\Registry */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Amc\User\Model\UserProductLink $userProductLink,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->backendAuthSession = $backendAuthSession;
        $this->userProductLink = $userProductLink;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry = $registry;
    }


    protected function _prepareLayout()
    {
        $customer = $this->registry->registry('current_customer');

        $pageTitle = $this->_layout->getBlock('page.title');
        if ($pageTitle) {
            $pageTitle->setPageTitle('Create Consultation for ' . $customer->getLastname().' '.$customer->getFirstname());
        }

        $categoriesWithProducts = $this->getCategoriesWithProducts();
        /** @var \Magento\Backend\Block\Widget\Accordion $accordion */
        $accordion = $this->_layout->createBlock('Magento\Backend\Block\Widget\Accordion');
        foreach ($categoriesWithProducts as $category) {
            $config = ['title' => $category->getName(), 'open' => 1, 'content' => ''];
            foreach ($category->getProducts() as $product) {
                $config['content'] .=
                    $this->_layout->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                        [
                            'label' => $product->getName(),
                            'onclick' => 'setLocation("'.$this->getUrl('*/*/edit', ['product_id' => $product->getId(), 'customer_id' => $customer->getId()]).'")',
                            'class' => 'task',
                        ]
                    )->toHtml();
            }
            $config['content'] .= '<br/><br/>';
            $accordion->addItem('category' . $category->getId(), $config);
        }

        $this->setAccordionsHtml($accordion->toHtml());
    }


    protected function getUser()
    {
        return $this->backendAuthSession->getUser();
    }

    protected function getCategoriesWithProducts()
    {
        // todo: exception for admin user
        $filterProductIds = $this->userProductLink->getUserProducts($this->getUser());
        $productCollection = $this->productCollectionFactory->create()
            ->addIdFilter($filterProductIds)
            ->addAttributeToSelect('name')
            ->addCategoryIds();

        $categoryIds = [];
        foreach ($productCollection as $product) {
            $categoryIds = array_merge($categoryIds, $product->getCategoryIds());
        }
        $categoryIds = array_unique($categoryIds);

        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addNameToResult()
            ->addIdFilter($categoryIds);

        foreach ($categoryCollection as $category) {
            $categoryProducts = [];
            foreach ($productCollection as $product) {
                if ( in_array($category->getId(), $product->getCategoryIds()) ) {
                    $categoryProducts[] = $product;
                }
            }
            $category->setProducts($categoryProducts);
        }

        return $categoryCollection;
    }
}
