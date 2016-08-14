<?php

namespace Amc\Clinic\Model\ResourceModel\Room;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
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

    /**
     * @return array
     */
    public function toColumnOptionArray()
    {
        $res = [];
        foreach ($this as $item) {
            $res[$item->getId()] = $item->getFullLabel();
        }
        return $res;
    }

    /**
     * @return array
     */
    public function toFormOptionArray($withEmpty = false)
    {
        $res = $withEmpty ? [['value' => '', 'label' => __('Please Select')]] : [];
        foreach ($this as $item) {
            $res[] = [
                'value' => $item->getId(),
                'label' => $item->getFullLabel()
            ];
        }
        return $res;
    }
}
