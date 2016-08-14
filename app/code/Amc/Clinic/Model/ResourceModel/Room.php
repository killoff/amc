<?php

namespace Amc\Clinic\Model\ResourceModel;

class Room extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amc_clinic_room_entity', 'entity_id');
    }
}
