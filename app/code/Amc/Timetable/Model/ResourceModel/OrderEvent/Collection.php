<?php

namespace Amc\Timetable\Model\ResourceModel\OrderEvent;

use Magento\Framework\Model\ModelResource\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'event_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Timetable\Model\OrderEvent', 'Amc\Timetable\Model\ResourceModel\OrderEvent');
    }
}
