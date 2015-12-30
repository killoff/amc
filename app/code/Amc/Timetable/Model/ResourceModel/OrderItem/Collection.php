<?php

namespace Amc\Timetable\Model\ResourceModel\OrderItem;

use Magento\Framework\Model\ModelResource\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Timetable\Model\OrderItem', 'Amc\Timetable\Model\ResourceModel\OrderItem');
    }
}
