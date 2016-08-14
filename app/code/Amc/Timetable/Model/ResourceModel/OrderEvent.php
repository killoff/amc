<?php

namespace Amc\Timetable\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderEvent extends AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amc_timetable_order_event', 'event_id');
    }
}
