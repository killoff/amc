<?php
namespace Amc\Timetable\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory as OrderEventCollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class OrderEventsJson extends Action
{
    /** @var OrderEventCollectionFactory */
    private $orderEventCollectionFactory;

    /** @var JsonFactory */
    private $jsonFactory;

    public function __construct(
        Action\Context $context,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->jsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $collection */
            $collection = $this->orderEventCollectionFactory->create();
            $collection->joinOrderItemsInformation();
            $collection->whereOrderId($orderId);
            $collection->joinUsersInformation();
            foreach ($collection as $item) {

                $data = $item->getData();
                $data['sales_item_id'] = $data['order_item_id'];
                $data['user_name'] = $data['username'];
                $data['product_unique_id'] = 'p' . $data['order_item_id'];
                $data['user_unique_id'] = $data['product_unique_id'].'_'.$data['user_id'];
                $result[] = $data;

            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }

        return $this->jsonFactory->create()
            ->setData($result);
    }
}
