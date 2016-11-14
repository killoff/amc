<?php

namespace Amc\Timetable\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerDue extends AbstractDb
{
    protected function _construct()
    {
    }

    public function getOrderItemsDue($customerId)
    {
        $customerId = (int)$customerId;
        return $this->getConnection()->fetchAll(
            "select i.item_id, i.order_id, i.sku, i.name,
                    i.qty_canceled, i.qty_invoiced, i.qty_ordered, i.qty_refunded, i.qty_shipped,
                    i.price, i.row_total,
                    o.increment_id, o.created_at
            from sales_order_item i INNER JOIN sales_order o ON i.order_id=o.entity_id
            where i.qty_ordered-i.qty_invoiced > 0
              and o.customer_id={$customerId}
            order by i.created_at desc
            ");
    }
}
