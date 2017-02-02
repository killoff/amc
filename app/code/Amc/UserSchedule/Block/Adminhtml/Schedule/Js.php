<?php

namespace Amc\UserSchedule\Block\Adminhtml\Schedule;

use Magento\Backend\Block\Template;

class Js extends Template
{
    /**
     * @var \Amc\Clinic\Model\ResourceModel\Room\Collection
     */
    protected $roomCollection;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Template\Context $context
     * @param \Amc\Clinic\Model\ResourceModel\Room\CollectionFactory $roomCollectionFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amc\Clinic\Model\ResourceModel\Room\CollectionFactory $roomCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->roomCollection = $roomCollectionFactory->create();
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
