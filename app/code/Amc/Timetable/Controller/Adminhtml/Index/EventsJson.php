<?php

namespace Amc\Timetable\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class EventsJson extends Action
{
    /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory */
    private $orderEventCollectionFactory;

    /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory */
    private $scheduleCollectionFactory;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;


    /**
     * @param Action\Context $context
     * @param \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory
     * @param \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory $orderEventCollectionFactory,
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        try {
            $response = [];
            /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $orderEventCollection */
            $orderEventCollection = $this->orderEventCollectionFactory->create();
            foreach ($orderEventCollection->getItems() as $event) {
                $response[] = [
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

            /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection $scheduleCollection */
            $scheduleCollection = $this->scheduleCollectionFactory->create();
            $scheduleCollection->startFrom(new \DateTime($this->_request->getParam('start')));
            $scheduleCollection->endTo(new \DateTime($this->_request->getParam('end')));
            foreach ($scheduleCollection->getItems() as $schedule) {
                $response[] = [
                    'resourceId' => sprintf('i%s', $schedule->getData('user_id')),
                    'id'         => $schedule->getId(),
                    'start'      => $schedule->getStartAt(),
                    'end'        => $schedule->getEndAt(),
                    'rendering'  => 'background',
                    'overlap'    => false,
                    'title'      => '', // 'room '.$schedule->getRoomId()
                    'type'       => 'schedule'
                ];
            }

        } catch (\Exception $e) {
            $response = [];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

}
