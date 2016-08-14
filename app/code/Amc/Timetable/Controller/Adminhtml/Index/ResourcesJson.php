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
            $response = [];
            $userCollection = $this->userCollectionFactory->create();
            foreach ($userCollection->getItems() as $user) {
                $response[] = [
                    'id'    => sprintf('i%s', $user->getId()),
                    'title' => $this->userHelper->getUserShortName($user),
                    'type'  => 'user',
                    'user_id' => $user->getId()
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
