<?php

namespace Amc\Timetable\Model;

use Magento\Framework\Model\AbstractModel;

class OrderEvent extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amc_timetable_order_event';

    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Timetable\Model\ResourceModel\OrderEvent');
    }
}
