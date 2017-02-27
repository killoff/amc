<?php

namespace Amc\Timetable\Model;

/**
 *  Return array of timetable aggregated data for quote/order:
 *
 * [
 *      products [
 *          [id => 1, sales_item_id => 11, name => 'Product 1'],
 *          [id => 2, sales_item_id => 12, name => 'Product 2'],
 *      ],
 *      users [
 *          [id => 18, name => 'Peter',  product_id => 1],
 *          [id => 19, name => 'John',   product_id => 1],
 *          [id => 20, name => 'Oliver', product_id => 2],
 *      ],
 *      events [
 *          [user_id => 18, room_id => 1, start_at => '2016-01-01 12:30:00', end_at => '2016-01-01 13:00:00', type => schedule],
 *          [user_id => 19, room_id => 5, start_at => '2016-01-01 12:30:00', end_at => '2016-01-01 13:00:00', type => schedule],
 *          [user_id => 19, room_id => 8, start_at => '2016-01-01 12:30:00', end_at => '2016-01-01 13:00:00', type => order],
 *          [user_id => 19, room_id => 1, start_at => '2016-01-01 12:30:00', end_at => '2016-01-01 13:00:00', type => order],
 *          [user_id => 20, room_id => 1, start_at => '2016-01-01 12:30:00', end_at => '2016-01-01 13:00:00', type => schedule],
 *      ]
 * ]
 */

class Aggregated
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

    /** @var \Magento\Catalog\Model\ProductFactory */
    protected $productFactory;

    public function __construct(
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amc\User\Model\UserProductLink $userProductLink,
        \Amc\User\Helper\Data $userHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->userProductLink = $userProductLink;
        $this->userHelper = $userHelper;
        $this->productFactory = $productFactory;
    }

    /**
     *  Return array of timetable aggregated data for quote, see description above
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getForQuote(\Magento\Quote\Model\Quote $quote, $dateFrom, $dateTo)
    {
        $items = $this->prepareItems($quote->getAllVisibleItems());
        return $this->getAggregated($items, $dateFrom, $dateTo);
    }

    /**
     *  Return array of timetable aggregated data for order, see description above
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getForOrder(\Magento\Sales\Model\Order $order, $dateFrom, $dateTo)
    {
        $items = $this->prepareItems($order->getAllVisibleItems());
        return $this->getAggregated($items, $dateFrom, $dateTo);
    }

    /**
     * @param array $items
     * @param $dateFrom
     * @param $dateTo
     * @return array
     */
    private function getAggregated(array $items, $dateFrom, $dateTo)
    {
        $result = [
            'products' => [],
            'users' => [],
            'events' => []
        ];

        if (0 === count($items)) {
            return $result;
        }

        /**
         * Collect products that represent quote/order items on timetable
         */
        foreach ($items as $salesItem) {
            $result['products'][] = [
                'id' => $salesItem['product_id'],
                'sales_item_id' => $salesItem['id'],
                'name' => $salesItem['name'],
                'duration' => $salesItem['duration']
            ];
        }
        $allProductIds = array_column($result['products'], 'id');

        /**
         * Collect events of type 'schedule' = datetime intervals during witch users can execute product
         * These events will be time slots for new appointments
         * Results are limited to interval $dateFrom .. $dateTo, see above
         */
        /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection
            ->addProductsFilter($allProductIds)
            ->startFrom(new \DateTime($dateFrom))
            ->endTo(new \DateTime($dateTo));

        if (0 === $scheduleCollection->count()) {
            return $result;
        }

        $userToProductsMapping = [];
        foreach ($scheduleCollection->getItems() as $schedule) {
            $result['events'][] = [
                'user_id' => $schedule->getData('user_id'),
                'room_id' => $schedule->getData('room_id'),
                'start_at' => $schedule->getData('start_at'),
                'end_at' => $schedule->getData('end_at'),
                'type' => 'schedule',
                'order_id' => null, // fill with null for convention
                'order_item_id' => null // fill with null for convention
            ];
            // collect mapping user_id => product_ids for next procedure
            $userToProductsMapping[$schedule->getData('user_id')] = explode(',', $schedule->getData('product_ids'));
        }

        $userIds = array_keys($userToProductsMapping);

        /**
         * For all products in list select users that assigned to them, considering users schedule above
         * So we select only users, that can execute these products (many-to-many) within specified period
         * $dateFrom .. $dateTo
         */
        /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
        $userCollection = $this->userCollectionFactory->create();
        $userCollection->setOrder('lastname');
        $userCollection->addFieldToFilter('user_id', ['in' => $userIds]);
        foreach ($userCollection->getItems() as $user) {
            $result['users'][] = [
                'id' => $user->getId(),
                'name' => $this->userHelper->getUserShortName($user),
                'product_ids' => $userToProductsMapping[$user->getId()]
            ];
        }

        /**
         * Collect events of other orders: type 'order' = datetime intervals during witch users already assigned for execution
         * These events will be marked as taken/occupied by other appointments (orders)
         * Results are limited to interval $dateFrom .. $dateTo
         */
        /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $orderEventCollection */
        $orderEventCollection = $this->orderEventCollectionFactory->create();
        $orderEventCollection
            ->whereUserIdIn($userIds)
            ->joinOrderItemsInformation()
            ->excludeCancelledOrderItems()
            ->whereStartIsBefore(new \DateTime($dateTo))
            ->whereEndIsAfter(new \DateTime($dateFrom));
        foreach ($orderEventCollection->getItems() as $orderEvent) {
            $result['events'][] = [
                'user_id' => $orderEvent->getData('user_id'),
                'room_id' => $orderEvent->getData('room_id'),
                'start_at' => $orderEvent->getData('start_at'),
                'end_at' => $orderEvent->getData('end_at'),
                'type' => 'order',
                'order_id' => $orderEvent->getData('order_id'),
                'order_item_id' => $orderEvent->getData('order_item_id')
            ];
        }

        return $result;
    }

    private function prepareItems($items)
    {
        $items = array_map(function($item) {
            $product = $this->productFactory->create()->load($item->getProductId());
            return [
                'id'         => $item->getId(),
                'product_id' => $item->getProductId(),
                'name'       => $item->getName(),
                'duration'   => $product->getData('duration')
            ];
        }, $items);

        return $items;
    }
}
