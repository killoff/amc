<?php

namespace Amc\UserSchedule\Block\Adminhtml\Schedule\Edit;

use Magento\Backend\Block\Template;

class Js extends Template
{
    /**
     * @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection
     */
    protected $eventCollection;

    /**
     * @var \Amc\Clinic\Model\ResourceModel\Room\Collection
     */
    protected $roomCollection;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Template\Context $context
     * @param \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $eventCollectionFactory
     * @param \Amc\Clinic\Model\ResourceModel\Room\CollectionFactory $roomCollectionFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $eventCollectionFactory,
        \Amc\Clinic\Model\ResourceModel\Room\CollectionFactory $roomCollectionFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->eventCollection = $eventCollectionFactory->create();
        $this->roomCollection = $roomCollectionFactory->create();
        $this->userFactory = $userFactory;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getCurrentDate()
    {
        return $this->_localeDate->date()->format('Y-m-d');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getResourceLabel()
    {
        return __('Rooms');
    }

    /**
     * @return string
     */
    public function getSaveActionUrl()
    {
        return $this->getUrl('user_schedule/schedule/save');
    }

    /**
     * @return string
     */
    public function getEventsCollectionJson()
    {
        $data = [];
        foreach ($this->eventCollection as $event) {
            $data[] = [
                'id' => $event->getId(),
                'userId' => $event->getUserId(),
                'resourceId' => $event->getRoomId(),
                'start' => $event->getStartAt(),
                'end' => $event->getEndAt(),
                // todo: cache/precalculate user load
                'title' => $this->userFactory->create()->load($event->getUserId())->getName()
            ];
        }
        return $this->jsonEncoder->encode($data);
    }

    /**
     * @return string
     */
    public function getRoomsCollectionJson()
    {
        $data = [];
        foreach ($this->roomCollection as $room) {
             $roomItem = [
                'id' => $room->getId(),
                'title' => $room->getFullLabel()
            ];

            if ($room->getColor()) {
                $roomItem['eventColor'] = $room->getColor();
            }

            $data[] = $roomItem;
        }
        return $this->jsonEncoder->encode($data);
    }
}
