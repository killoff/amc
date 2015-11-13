<?php

namespace Amc\Timetable\Model\ResourceModel;

use Magento\Framework\Model\ModelResource\Db\AbstractDb;

class Ticket extends AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amc_timetable_ticket_entity', 'entity_id');
    }
}
