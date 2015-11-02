<?php

namespace Amc\Clinic\Model\ResourceModel\Room;

class Collection extends \Magento\Framework\Model\ModelResource\Db\Collection\AbstractCollection
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
        $this->_init('Amc\Clinic\Model\Room', 'Amc\Clinic\Model\ResourceModel\Room');
    }
}
