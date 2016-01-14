<?php
namespace Amc\Timetable\Block\Adminhtml\Index;

class Timetable extends \Magento\Backend\Block\Template
{
    /** @var \Magento\User\Model\ResourceModel\User\CollectionFactory */
    private $userCollectionFactory;

    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    private $orderEventCollectionFactory;

    /** @var \Magento\Framework\Json\EncoderInterface */
    private $jsonEncoder;

    /** @var \Amc\User\Helper\Data */
    private $userHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
     * @param \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amc\User\Helper\Data $userHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->userHelper = $userHelper;
    }

    public function getInitialDate()
    {
        return date('Y-m-d');
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

        /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $orderEventCollection */
        $orderEventCollection = $this->orderEventCollectionFactory->create();
        foreach ($orderEventCollection->getItems() as $event) {
            $events[] = [
                'resourceId' => sprintf('i%s', $event->getData('user_id')),
                'order_item_id' => $event->getData('order_item_id'),
                'id'         => $event->getData('uuid'),
                'uuid'       => $event->getData('uuid'),
                'start'      => $event->getStartAt(),
                'end'        => $event->getEndAt(),
                'overlap'    => false,
                'title'      => '', // 'room '.$schedule->getRoomId()
                'type'       => 'order'
            ];
        }

        $userCollection = $this->userCollectionFactory->create();
        foreach ($userCollection->getItems() as $user) {
            $resources[] = [
                'id'    => sprintf('i%s', $user->getId()),
                'title' => $this->userHelper->getUserShortName($user),
                'type'  => 'user',
                'user_id' => $user->getId()
            ];
        }

        return [
            'resources' => $this->jsonEncoder->encode($resources),
            'events' => $this->jsonEncoder->encode($events)
        ];
    }
}
