<?php
namespace Amc\Timetable\Block\Adminhtml\Order;

abstract class Timetable extends \Magento\Backend\Block\Template
{
    /**
     * {@inheritdoc}
     */

    /** @var \Magento\Framework\Registry */
    protected $coreRegistry = null;

    /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory */
    protected $scheduleCollectionFactory;

    /** @var \Magento\User\Model\ResourceModel\User\CollectionFactory */
    protected $userCollectionFactory;

    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    protected $orderEventCollectionFactory;

    /** @var \Magento\User\Model\ResourceModel\User\Collection */
    protected $userCollection;

    /** @var \Magento\Framework\Json\EncoderInterface */
    protected $jsonEncoder;

    /** @var \Amc\User\Helper\Data */
    protected $userHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
     * @param \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amc\User\Helper\Data $userHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->userHelper = $userHelper;
    }

    abstract protected function getOrder();

    abstract protected function getOrderItems();

    abstract public function getInitialDate();

    /**
     * Return array of resources and events for timetable calendar.
     *      Resources = Products and relevant users
     *      Events = Available slots in users' schedule linked to resources
     *
     * @return array ['resources' => resources_json, 'events' => events_json]
     * @todo: refactor occupiedSchedule
     * @todo: remove dependency on product => no product load, what will happen if product deleted?
     */
    public function getResourcesAndEvents()
    {
//        $order = $this->getOrder();
        $resources = [];
        $events = [];
        /** @var \Magento\Sales\Model\Order\Item $item */

        $occupiedSchedule = $this->getOccupiedSchedule();

        foreach ($this->getOrderItems() as $item) {

            // add user schedule as background events
            $scheduleCollection = $this->createUserScheduleCollection()
                ->addProductFilter($item->getProductId());
            $productUserIds = [];
            foreach ($scheduleCollection->getItems() as $schedule) {
                $productUserIds[] = $schedule->getData('user_id');

                // events that represent employee's schedule = working time slots
                $events[] = [
                    'resourceId' => sprintf('i%s_u%s', $item->getId(), $schedule->getData('user_id')),
                    'id'         => sprintf('u%s_s%s', $schedule->getData('user_id'), $schedule->getId()),
                    'start'      => $schedule->getStartAt(),
                    'end'        => $schedule->getEndAt(),
                    'rendering'  => 'background',
                    'overlap'    => true,
                    'title'      => 'room '.$schedule->getRoomId(),
                    'type'       => 'schedule',
                    'room_id'    => $schedule->getRoomId(),
                    'user_id'    => $schedule->getData('user_id'),
                    'sales_item_id' => $item->getId(),
                ];

                // events that represent real orders
                $roomOccupiedSchedule = $occupiedSchedule->getItemsByColumnValue('room_id', $schedule->getRoomId());
                foreach ($roomOccupiedSchedule as $occupied) {
                    $scheduleStart = new \DateTime($schedule->getStartAt());
                    $scheduleEnd = new \DateTime($schedule->getEndAt());
                    $occupiedStart = new \DateTime($occupied->getStartAt());
                    $occupiedEnd = new \DateTime($occupied->getEndAt());
                    // if schedule intersects occupied schedule - add this event as taken, i.e. red background
                    if ($occupiedStart->getTimestamp() < $scheduleEnd->getTimestamp() && $occupiedEnd->getTimestamp() > $scheduleStart->getTimestamp()) {
                        $events[] = [
                            'resourceId' => sprintf('i%s_u%s', $item->getId(), $schedule->getData('user_id')),
                            'id'         => $occupied->getUuid(),
                            'start'      => $occupied->getStartAt(),
                            'end'        => $occupied->getEndAt(),
                            // todo: refactor
                            'rendering'  => $occupied->getOrderItemId() === $item->getId() ? '' : 'background',
                            'color'      => $occupied->getOrderItemId() === $item->getId() ? '#00c853' : '#ff8a80',
                            'belongs_to_current_order' => $occupied->getOrderItemId() === $item->getId() ? true : false,
                            'overlap'    => true,
                            'title'      => '', // 'room '.$occupied->getRoomId(),
                            'type'       => 'schedule',
                            'room_id'    => $occupied->getRoomId(),
                            'user_id'    => $occupied->getData('user_id'),
                            'sales_item_id' => $occupied->getOrderItemId(),
                        ];
                    }
                }
            }

            $product = $item->getProduct();
            if ($product) {
                $duration = $product->load($item->getProductId())->getData('duration');
            } else {
                $duration = '15';
            }
            $resource = [
                'id'    => 'i' . $item->getId(),
                'title' => $item->getName(),
                'type'  => 'item',
                'duration' => $duration,
                'sales_item_id' => $item->getId()
            ];
            foreach (array_unique($productUserIds) as $userId) {
                $resource['children'][] = [
                    'id'    => sprintf('i%s_u%s', $item->getId(), $userId),
                    'title' => $this->userHelper->getUserShortName($this->getUserInfo($userId)),
                    'type'  => 'user',
                    'sales_item_id' => $item->getId(),
                    'user_id' => $userId
                ];
            }
            $resources[] = $resource;
        }

        return [
            'resources' => $this->jsonEncoder->encode($resources),
            'events' => $this->jsonEncoder->encode($events)
        ];
    }

    /**
     * Return user info that represented in order timetable
     * User collection is cached and based on products in order
     *
     * @param int $userId
     * @return \Magento\User\Model\User
     */
    protected function getUserInfo($userId)
    {
        if (null === $this->userCollection) {
            $productIds = [];
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($this->getOrderItems() as $item) {
                $productIds[] = $item->getProductId();
            }
            $scheduleCollection = $this->createUserScheduleCollection()
                ->addProductsFilter($productIds)
                ->groupByUsers();
            $userIds = $scheduleCollection->getColumnValues('user_id');

            /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
            $this->userCollection = $this->userCollectionFactory->create();
            $this->userCollection->addFieldToFilter('user_id', ['in' => $userIds]);
        }
        return $this->userCollection->getItemById($userId);
    }

    /**
     * Return collection of users' schedule
     *
     * @return \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection
     */
    protected function createUserScheduleCollection()
    {
        $range = $this->getTimetableRange();
        /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection
            ->startFrom($range['start'])
            ->endTo($range['end']);

        return $scheduleCollection;
    }

    protected function getTimetableRange()
    {
        // let's get schedule between 1 days ago and 1 month later
        $rangeStart = new \DateTime($this->getInitialDate());
        $rangeStart->sub(new \DateInterval('P1D')); // 1 day ago
        $rangeEnd = new \DateTime($this->getInitialDate());
        $rangeEnd->add(new \DateInterval('P1M'));  //  1 month later
        return ['start' => $rangeStart, 'end' => $rangeEnd];
    }

    public function getProductIds()
    {
        // todo: not optimal way to read items
        $productIds = [];
        foreach ($this->getOrderItems() as $item) {
            $productIds[] = $item->getProductId();
        }
        return $productIds;
    }

    protected function getOccupiedSchedule()
    {
        $range = $this->getTimetableRange();
        /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $orderEventCollection */
        $orderEventCollection = $this->orderEventCollectionFactory->create();
        $orderEventCollection
            ->whereStartIsBefore($range['end'])
            ->whereEndIsAfter($range['start']);
        return $orderEventCollection;
    }
}
