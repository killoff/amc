<?php

namespace Amc\Timetable\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InvoiceItem extends AbstractDb
{
    protected function _construct()
    {
    }

    /**
     * Pick either order.item created_at or amc_timetable_order_event start_at as for 'execution_date' for item
     * Value from amc_timetable_order_event has higher priority
     *
     * @param $orderItemId
     * @return string
     */
    public function calculateItemExecutionDate($orderItemId)
    {
        return $this->getConnection()->fetchOne(
            'select IFNULL(toe.start_at, soi.created_at) as created_at
             from sales_order_item soi
                 left join amc_timetable_order_event toe on soi.item_id=toe.order_item_id
             where soi.item_id = :order_item_id',
            [':order_item_id' => $orderItemId]
        );
    }
}
