<?php

namespace Amc\UserSchedule\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Amc\User\Helper\Data as UserHelper;
class Schedule extends Template
{
    /**
     * @var UserCollectionFactory
     */
    protected $userCollectionFactory;

    /**
     * @var UserHelper
     */
    protected $userHelper;

    public function __construct(
        Template\Context $context,
        UserCollectionFactory $collectionFactory,
        UserHelper $userHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->userCollectionFactory = $collectionFactory;
        $this->userHelper = $userHelper;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getUsersListLabel()
    {
        return __('Doctors');
    }

    /**
     * @return array
     */
    public function getUsersData()
    {
        $userCollection = $this->userCollectionFactory->create();
        $userCollection->setOrder('lastname', 'ASC');
        $userCollection->addOrder('firstname', 'ASC');
        $result = [];
        foreach ($userCollection as $user) {
            $result[] = [
                'id' => $user->getId(),
                'name' => $this->userHelper->getUserShortName($user)
            ];
        }
        return $result;
    }
}
