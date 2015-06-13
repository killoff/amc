<?php
namespace Amc\Protocol\Model\Resource\Protocol;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Define resource model and model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Protocol\Model\Protocol', 'Amc\Protocol\Model\Resource\Protocol');
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
