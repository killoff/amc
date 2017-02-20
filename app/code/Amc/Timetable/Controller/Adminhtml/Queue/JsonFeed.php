<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory as OrderEventCollectionFactory;
use Amc\Timetable\Model\ResourceModel\QueueStatus;
use Amc\User\Helper\Data as UserHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class JsonFeed extends \Magento\Backend\App\Action
{
    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    private $orderEventCollectionFactory;

    /** @var UserHelper */
    private $userHelper;

    /** @var TimezoneInterface */
    private $localeDate;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        OrderEventCollectionFactory $orderEventCollectionFactory,
        UserHelper $userHelper,
        TimezoneInterface $localeDate,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->userHelper = $userHelper;
        $this->localeDate = $localeDate;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        try {
            $date = $this->getRequest()->getParam('date');
            $from = (new \DateTime($date))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $to = (new \DateTime($date))->setTime(23, 59, 59)->format('Y-m-d H:i:s');
            // handle context as integer
            $context = (new \DateTime($date))->format('Ymd');

            /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $collection */
            $collection = $this->orderEventCollectionFactory->create();
            $collection->joinCustomersInformation();
            $collection->joinOrderItemsInformation();
            $collection->excludeCancelledOrderItems();
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

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( array_values($result) );
    }

    private function prepareEvents(array $collectionData)
    {
        $result = [];
        // group by customer
        foreach($collectionData as $item) {
            $customerId = $item['customer_id'];
            if (!isset($result[$customerId])) {
                $result[$customerId] = [
                    'customer' => [
                        'id' => $customerId,
                        'name' => $item['customer_name'],
                        'status' => $item['timetable_status'],
                        'nearest_time' => false
                    ],
                    'events' => []
                ];
            }
            unset($item['customer_name']);
            $result[$customerId]['events'][] = $item;

            // in case of 'idle' status check if customer is late
            if (false === $result[$customerId]['customer']['nearest_time']
                || (new \DateTime($item['start_at'])) < (new \DateTime($result[$customerId]['customer']['nearest_time']))
            ) {
                $result[$customerId]['customer']['nearest_time'] = $item['start_at'];
            }
        }

        // take locale time, but reset 'locale' in datetime object to have true comparison
        $now = new \DateTime($this->localeDate->date()->format('d.m.Y H:i:s'));

        // refine status
        foreach ($result as &$entry) {
            $customer = $entry['customer'];
            $events = $entry['events'];
            $qtyOrdered = array_sum(array_column($events, 'qty_ordered'));
            $qtyInvoiced = array_sum(array_column($events, 'qty_invoiced'));
            if ($qtyOrdered === $qtyInvoiced) {
                $entry['customer']['status'] = (string)QueueStatus::STATUS_PAID;
            }
            $isLate = (new \DateTime($customer['nearest_time'])) < $now;
            if ($customer['status'] == (string)QueueStatus::STATUS_IDLE && $isLate) {
                $entry['customer']['status'] = (string)QueueStatus::STATUS_LATE;
            }
        }

        return $result;
    }
}
