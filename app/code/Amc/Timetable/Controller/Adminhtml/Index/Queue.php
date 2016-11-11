<?php
namespace Amc\Timetable\Controller\Adminhtml\Index;

class Queue extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $time = $this->getRequest()->getParam('time', false);


        $collection = $this->collectionFactory->create();
        $collection->setPageSize(10);
        $collection->setCurPage(1);
        $collection->joinCustomersInformation();
        $collection->joinOrderItemsInformation();
        $collection->joinUsersInformation();
        print_r($collection->getData());
        exit;

        $layout = $this->layoutFactory->create();
        $productsGrid = $layout->createBlock(
            'Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser',
            '',
            [
                'data' => [
                    'id' => $uniqId,
                    'use_massaction' => $massAction,
                    'product_type_id' => $productTypeId,
                    'category_id' => $this->getRequest()->getParam('category_id'),
                ]
            ]
        );

        $html = $productsGrid->toHtml();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($html);
    }
}
