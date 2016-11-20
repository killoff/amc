<?php

namespace Amc\Timetable\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class ResourcesJson extends Action
{
    /** @var \Magento\User\Model\ResourceModel\User\CollectionFactory */
    private $userCollectionFactory;

    /** @var \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory */
    private $scheduleCollectionFactory;

    /** @var \Amc\User\Helper\Data */
    private $userHelper;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amc\UserSchedule\Model\ResourceModel\Schedule\Item\CollectionFactory $scheduleCollectionFactory,
        \Amc\User\Helper\Data $userHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->userCollectionFactory = $userCollectionFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->userHelper = $userHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        try {
            $currentDate = $this->getRequest()->getParam('date');
            if (!$currentDate) {
                throw new \InvalidArgumentException('Date must be specified');
            }
            $from = (new \DateTime($currentDate))->setTime(0,0,0)->format('Y-m-d H:i:s');
            $to = (new \DateTime($currentDate))->setTime(23,59,59)->format('Y-m-d H:i:s');
            $response = [];
            /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
            $userCollection = $this->userCollectionFactory->create();
            $userCollection->getSelect()->join(
                ['schedule' => $userCollection->getTable('amc_user_schedule')],
                'schedule.user_id=main_table.user_id'
            );
            $userCollection->getSelect()->where('start_at > ?', $from);
            $userCollection->getSelect()->where('start_at < ?', $to);
            $userCollection->setOrder('lastname', 'ASC');
            $userCollection->addOrder('firstname', 'ASC');
            foreach ($userCollection->getItems() as $user) {
                $response[] = [
                    'id'    => sprintf('i%s', $user->getId()),
                    'title' => $this->userHelper->getUserShortName($user),
                    'type'  => 'user',
                    'user_id' => $user->getId(),
                    'position' => $user->getData('user_position')
                ];
            }
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
