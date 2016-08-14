<?php
namespace Amc\Protocol\Model\ResourceModel\Protocol;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model and model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Protocol\Model\Protocol', 'Amc\Protocol\Model\ResourceModel\Protocol');
    }

    /**
     * Returns options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('protocol_id', 'name');
    }
}
