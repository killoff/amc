<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;

class JsonFeed extends \Magento\Backend\App\Action
{
    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    private $orderEventCollectionFactory;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }


    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $date = $this->getRequest()->getParam('date');
        $from = (new \DateTime($date))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $to = (new \DateTime($date))->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $collection */
        $collection = $this->orderEventCollectionFactory->create();
//        $collection->setPageSize(50);
//        $collection->setCurPage(1);
        $collection->joinCustomersInformation();
        $collection->joinOrderItemsInformation();
        $collection->joinUsersInformation();
        $collection->addFieldToFilter('start_at', ['gt' => $from]);
        $collection->addFieldToFilter('start_at', ['lt' => $to]);
        $collection->addOrder('start_at', 'ASC');
        $collection->addOrder('customer_name', 'ASC');
        $groupedByCustomer = $this->groupByCustomer($collection->getData());
//        print_r($groupedByCustomer);
//        exit;

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( array_values($groupedByCustomer) );
    }

    private function groupByCustomer(array $collectionData)
    {
        $result = [];
        foreach($collectionData as $item) {
            $customerId = $item['customer_id'];
            if (!isset($result[$customerId])) {
                $result[$customerId] = [
                    'customer' => [
                        'id' => $customerId,
                        'name' => $item['customer_name'],
                        'status' => $item['timetable_status']
                    ],
                    'events' => []
                ];
            }
            unset($item['customer_name']);
            $result[$customerId]['events'][] = $item;
        }
        return $result;
    }
}
