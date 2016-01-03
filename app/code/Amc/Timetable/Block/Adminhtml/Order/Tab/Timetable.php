<?php
namespace Amc\Timetable\Block\Adminhtml\Order\Tab;

class Timetable extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'order/tab/timetable.phtml';

    /** @var \Magento\Framework\Registry */
    protected $coreRegistry = null;

    /** @var \Magento\Sales\Helper\Admin */
    private $adminHelper;

    /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory */
    private $scheduleCollectionFactory;

    /** @var \Magento\User\Model\ResourceModel\User\CollectionFactory */
    private $userCollectionFactory;

    /** @var \Magento\User\Model\ResourceModel\User\Collection */
    private $userCollection;

    /** @var \Magento\Framework\Json\EncoderInterface */
    private $jsonEncoder;

    /** @var \Amc\User\Helper\Data */
    private $userHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amc\User\Helper\Data $userHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->adminHelper = $adminHelper;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->userHelper = $userHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Return array of resources and events for timetable calendar.
     *      Resources = Products and relevant users
     *      Events = Available slots in users' schedule linked to resources
     *
     * @return array ['resources' => resources_json, 'events' => events_json]
     */
    public function getResourcesAndEvents()
    {
        $resources = [];
        $events = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($this->getOrder()->getAllVisibleItems() as $item) {
            $scheduleCollection = $this->prepareUserScheduleCollection();
            $scheduleCollection->addProductFilter($item->getProductId());
            $productUserIds = [];
            foreach ($scheduleCollection->getItems() as $schedule) {
                $productUserIds[$schedule->getData('user_id')] = 1;
                $events[] = [
                    'resourceId' => sprintf('i%s_u%s', $item->getId(), $schedule->getData('user_id')),
                    'id'         => sprintf('u%s_s%s', $schedule->getData('user_id'), $schedule->getId()),
                    'start'      => $schedule->getStartAt(),
                    'end'        => $schedule->getEndAt(),
                    'rendering'  => 'background',
                    'overlap'    => true,
                    'title'      => 'room '.$schedule->getRoomId()
                ];
            }
            $resource = [
                'id'    => 'i' . $item->getId(),
                'title' => $item->getName(),
                'type'  => 'item'
            ];
            foreach (array_keys($productUserIds) as $userId) {
                $resource['children'][] = [
                    'id'    => sprintf('i%s_u%s', $item->getId(), $userId),
                    'title' => $this->userHelper->getUserShortName($this->getUserInfo($userId)),
                    'type'  => 'user'
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
    private function getUserInfo($userId)
    {
        if (null === $this->userCollection) {
            $productIds = [];
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($this->getOrder()->getItems() as $item) {
                $productIds[] = $item->getProductId();
            }
            $scheduleCollection = $this->prepareUserScheduleCollection()
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
     * Return collection of users' schedule between order date and 1 month (P1M) further
     *
     * @return \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection
     */
    private function prepareUserScheduleCollection()
    {
        $rangeStart = new \DateTime($this->getOrder()->getCreatedAt());
        $rangeEnd = new \DateTime($this->getOrder()->getCreatedAt());
        $rangeEnd->add(new \DateInterval('P1M'));
        /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection
            ->addFieldToFilter('end_at', ['gt' => $rangeStart->format('Y-m-d H:i:s')])
            ->addFieldToFilter('start_at', ['lt' => $rangeEnd->format('Y-m-d H:i:s')]);
        return $scheduleCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Timetable');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Timetable');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
