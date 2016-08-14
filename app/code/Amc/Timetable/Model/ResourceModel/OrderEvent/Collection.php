<?php

namespace Amc\Timetable\Model\ResourceModel\OrderEvent;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'event_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amc\Timetable\Model\OrderEvent', 'Amc\Timetable\Model\ResourceModel\OrderEvent');
    }

    public function whereUserIdIn(array $userIds)
    {
        if (empty($userIds)) {
            return $this;
        }
        $this->addFieldToFilter('user_id', ['in' => $userIds]);
        return $this;
    }

    public function whereProductIdIn(array $productIds)
    {
        if (empty($userIds)) {
            return $this;
        }
        $condition = $this->getConnection()->quoteInto('main_table.order_item_id = oi.item_id AND oi.product_id IN(?)', $productIds);
        $this->getSelect()->joinInner(
            ['oi' => $this->getTable('sales_order_item')],
            $condition,
            []
        );
        return $this;
    }

    public function whereStartIsBefore(\DateTime $date)
    {
        $this->addFieldToFilter('start_at', ['lt' => $date->format('Y-m-d H:i:s')]);
        return $this;
    }

    public function whereEndIsAfter(\DateTime $date)
    {
        $this->addFieldToFilter('end_at', ['gt' => $date->format('Y-m-d H:i:s')]);
        return $this;
    }
}
