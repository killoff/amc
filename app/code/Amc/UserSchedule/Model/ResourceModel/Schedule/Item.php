<?php

namespace Amc\UserSchedule\Model\ResourceModel\Schedule;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amc_user_schedule', 'entity_id');
    }
}
