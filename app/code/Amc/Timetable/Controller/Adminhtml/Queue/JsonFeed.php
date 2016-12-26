<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory as OrderEventCollectionFactory;
use Amc\Timetable\Model\ResourceModel\QueueStatus;
use Amc\User\Helper\Data as UserHelper;
use Magento\Framework\Controller\Result\JsonFactory;

class JsonFeed extends \Magento\Backend\App\Action
{
    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    private $orderEventCollectionFactory;

    /** @var UserHelper */
    private $userHelper;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    /** @var \DateTime */
    private $now;

    public function __construct(
        OrderEventCollectionFactory $orderEventCollectionFactory,
        UserHelper $userHelper,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->userHelper = $userHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->now = new \DateTime();
    }

    public function execute()
    {
        $date = $this->getRequest()->getParam('date');
        $from = (new \DateTime($date))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $to = (new \DateTime($date))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        // handle context as integer
        $context = (new \DateTime($date))->format('Ymd');

        /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $collection */
        $collection = $this->orderEventCollectionFactory->create();
        $collection->joinCustomersInformation();
        $collection->joinOrderItemsInformation();
        $collection->joinUsersInformation();
        $collection->joinQueueStatus($context);
        $collection->addFieldToFilter('start_at', ['gt' => $from]);
        $collection->addFieldToFilter('start_at', ['lt' => $to]);
        $collection->addOrder('start_at', 'ASC');
        $collection->addOrder('customer_name', 'ASC');
        $events = $collection->getData();
        array_walk($events, function(&$e) {
            $e['user_fullname'] = $this->userHelper->shortenUserName(
                $e['user_firstname'], $e['user_lastname'], $e['user_fathername']
            );
        });
        $result = $this->prepareEvents($events);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( array_values($result) );
    }

    private function prepareEvents(array $collectionData)
    {
        $result = [];
        foreach($collectionData as $item) {
            $customerId = $item['customer_id'];
            if (!isset($result[$customerId])) {
                $result[$customerId] = [
                    'customer' => [
                        'id' => $customerId,
                        'name' => $item['customer_name'],
                        'status' => (string)QueueStatus::STATUS_PAID, // initialize as paid, will be redefined later
                        'nearest_time' => false
                    ],
                    'events' => []
                ];
            }
            unset($item['customer_name']);
            $result[$customerId]['events'][] = $item;

            // there are unpaid items - reset status to queue.status
            if ($item['qty_ordered'] > $item['qty_invoiced']) {
                $result[$customerId]['customer']['status'] = $item['timetable_status'];
            }

            // in case of 'idle' status check if customer is late
            if (false === $result[$customerId]['customer']['nearest_time']
                || (new \DateTime($item['start_at'])) < (new \DateTime($result[$customerId]['customer']['nearest_time']))
            ) {
                $result[$customerId]['customer']['nearest_time'] = $item['start_at'];
                if ($result[$customerId]['customer']['status'] == (string)QueueStatus::STATUS_IDLE
                    && (new \DateTime($result[$customerId]['customer']['nearest_time'])) < $this->now
                ) {
                    $result[$customerId]['customer']['status'] = (string)QueueStatus::STATUS_LATE;
                }
            }

        }
        return $result;
    }
}
