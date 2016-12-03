<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory as OrderEventCollectionFactory;
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
        $events = $collection->getData();
        array_walk($events, function(&$e) {
            $e['user_fullname'] = $this->userHelper->shortenUserName(
                $e['user_firstname'], $e['user_lastname'], $e['user_fathername']
            );
        });
        $groupedByCustomer = $this->groupByCustomer($events);

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
                        'status' => $item['timetable_status'],
                        'nearest_time' => false
                    ],
                    'events' => []
                ];
            }
            unset($item['customer_name']);
            $result[$customerId]['events'][] = $item;

            if (false === $result[$customerId]['customer']['nearest_time']
                || (new \DateTime($item['start_at'])) < (new \DateTime($result[$customerId]['customer']['nearest_time']))
            ) {
                $result[$customerId]['customer']['nearest_time'] = $item['start_at'];
                if ($result[$customerId]['customer']['status'] == '0'
                    && (new \DateTime($result[$customerId]['customer']['nearest_time'])) < $this->now
                ) {
                    $result[$customerId]['customer']['status'] = '2'; // late
                }
            }

        }
        return $result;
    }
}
