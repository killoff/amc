<?php

namespace Amc\UserSchedule\Controller\Adminhtml\Schedule;

use Magento\Backend\App\Action;
use Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory as ScheduleCollectionFactory;
use Magento\User\Model\UserFactory;
use Amc\User\Helper\Data as UserHelper;
use Magento\Framework\Controller\Result\JsonFactory;

class EventsJson extends Action
{
    /** @var ScheduleCollectionFactory */
    protected $scheduleCollectionFactory;

    /** @var UserFactory */
    protected $userFactory;

    /** @var UserHelper */
    protected $userHelper;

    /** @var JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        UserFactory $userFactory,
        UserHelper $userHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->userFactory = $userFactory;
        $this->userHelper = $userHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $response = [];
        try {
            $start = new \DateTime($this->_request->getParam('start'));
            $end = new \DateTime($this->_request->getParam('end'));

            /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\Collection $collection */
            $collection = $this->scheduleCollectionFactory->create();
            $collection->startFrom($start, true);
            $collection->endTo($end, true);
            foreach ($collection as $event) {
                $user = $this->userFactory->create()->load($event->getUserId());
                $response[] = [
                    'id' => $event->getId(),
                    'userId' => $event->getUserId(),
                    'resourceId' => $event->getRoomId(),
                    'start' => $event->getStartAt(),
                    'end' => $event->getEndAt(),
                    // todo: cache/precalculate user load or join user names to collection
                    'title' => $this->userHelper->getUserShortName($user)
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
