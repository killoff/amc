<?php

namespace Amc\Timetable\Model\ResourceModel\Ticket;

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
        $this->_init('Amc\Timetable\Model\Ticket', 'Amc\Timetable\Model\ResourceModel\Ticket');
    }
}
