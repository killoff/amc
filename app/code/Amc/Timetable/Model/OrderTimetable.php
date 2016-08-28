<?php

namespace Amc\Timetable\Model;


class OrderTimetable
{
    /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory */
    protected $scheduleCollectionFactory;

    /** @var \Amc\User\Model\UserProductLink */
    private $userProductLink;

    /** @var \Amc\User\Helper\Data */
    private $userHelper;

    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    protected $orderEventCollectionFactory;

    /** @var \Magento\User\Model\ResourceModel\User\CollectionFactory */
    protected $userCollectionFactory;

    public function __construct(
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amc\User\Model\UserProductLink $userProductLink,
        \Amc\User\Helper\Data $userHelper
    )
    {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->userProductLink = $userProductLink;
        $this->userHelper = $userHelper;
    }

    /**
     *             $a = [
    [
    'product' => ['id' => '12', 'name' => 'Консультация ревматолога'],
    'users'   => [
    [ 'id' => '123', 'name' => 'Иванов'],
    [ 'id' => '123', 'name' => 'Петров'],
    [ 'id' => '123', 'name' => 'Сидоров'],
    ],
    'events' => [
    ['start' => '2016-01-01 12:00:00','end' => '2016-01-01 12:00:00', 'type' => 'schedule', 'user_id' => '123', 'room_id' => 5],
    ['start' => '2016-01-01 12:00:00','end' => '2016-01-01 12:00:00', 'type' => 'self', 'user_id' => '123', 'room_id' => 5],
    ['start' => '2016-01-01 12:00:00','end' => '2016-01-01 12:00:00', 'type' => 'occupied', 'user_id' => '123', 'room_id' => 5],
    ]
    ],
    [
    'product' => ['id' => '..', 'name' => '..'],
    'users'   => [
    ['name' => '..', ],
    ['name' => '..', ],
    ['name' => '..', ],
    ],
    'events' =>
    ['start' => '..','end' => '..', 'type' => '..'],
    ['start' => '..','end' => '..', 'type' => '..'],
    ['start' => '..','end' => '..', 'type' => '..'],
    ]
    ],
    ];

     *
     * @param $order
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getAggregated($order, $dateFrom, $dateTo)
    {
        $result = [
            'products' => [],
            'users' => [],
            'events' => []
        ];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $productId = $orderItem->getProductId();
            $result['products'][] = [
                'id' => $productId,
                'sales_item_id' => $orderItem->getId(),
                'name' => $orderItem->getName()
            ];

            /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection $scheduleCollection */
            $scheduleCollection = $this->scheduleCollectionFactory->create();
            $scheduleCollection
                ->addProductFilter($productId)
                ->startFrom(new \DateTime($dateFrom))
                ->endTo(new \DateTime($dateTo));
            $userIds = array_unique($scheduleCollection->getColumnValues('user_id'));
            /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
            $userCollection = $this->userCollectionFactory->create();
            $userCollection->addFieldToFilter('user_id', ['in' => $userIds]);
            foreach ($userCollection->getItems() as $user) {
                $result['users'][] = [
                    'id' => $user->getId(),
                    'name' => $this->userHelper->getUserShortName($user),
                    'product_id' => $productId
                ];
            }

            foreach ($scheduleCollection->getItems() as $schedule) {
                $result['events'][] = [
                    'user_id' => $schedule->getData('user_id'),
                    'room_id' => $schedule->getData('room_id'),
                    'start_at' => $schedule->getData('start_at'),
                    'end_at' => $schedule->getData('end_at'),
                    'type' => 'schedule',
                ];
            }
        }

        /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $orderEventCollection */
        $orderEventCollection = $this->orderEventCollectionFactory->create();
        $orderEventCollection
            ->whereUserIdIn(array_column($result['users'], 'id'))
            ->whereStartIsBefore(new \DateTime($dateTo))
            ->whereEndIsAfter(new \DateTime($dateFrom));
        foreach ($orderEventCollection->getItems() as $orderEvent) {
            $result['events'][] = [
                'user_id' => $orderEvent->getData('user_id'),
                'room_id' => $orderEvent->getData('room_id'),
                'start_at' => $orderEvent->getData('start_at'),
                'end_at' => $orderEvent->getData('end_at'),
                'type' => 'order',
            ];
        }

        return $result;
    }
}
