<?php

namespace Amc\UserSchedule\Model\ResourceModel\Schedule\Item;

use Magento\Framework\Model\ModelResource\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\UserSchedule\Model\Schedule\Item', 'Amc\UserSchedule\Model\ResourceModel\Schedule\Item');
    }
}
