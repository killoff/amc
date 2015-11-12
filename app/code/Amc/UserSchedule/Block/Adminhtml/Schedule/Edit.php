<?php

namespace Amc\UserSchedule\Block\Adminhtml\Schedule;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Amc\User\Model\ResourceModel\User\CollectionFactory;

class Edit extends Container
{
    /**
     * @var \Amc\User\Model\ResourceModel\User\Collection
     */
    protected $collection;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save'),
                'class' => 'save save-schedule'
            ],
            -100
        );
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
        return $this->collection->toFormOptionArray();
    }
}
