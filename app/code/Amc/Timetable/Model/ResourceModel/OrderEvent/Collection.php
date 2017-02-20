<?php

namespace Amc\Timetable\Model\ResourceModel\OrderEvent;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amc\Timetable\Model\ResourceModel\QueueStatus;
use Magento\Sales\Model\Order;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'event_id';

    protected $_orderItemsTableJoined = false;

    protected $_isUserTableJoined = false;

    protected $_isCustomerTableJoined = false;

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
        $this->joinOrderItemsInformation();
        $condition = $this->getConnection()->quoteInto('order_item.product_id IN(?)', $productIds);
        $this->getSelect()->where($condition);
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

    public function joinOrderItemsInformation()
    {
        // new \Zend_Db_Expr('order_item.qty_ordered - order_item.qty_invoiced')
        if (false === $this->_orderItemsTableJoined) {
            $this->getSelect()->joinInner(
                ['order_item' => $this->getTable('sales_order_item')],
                'main_table.order_item_id = order_item.item_id',
                ['product_id', 'order_id', 'product_name' => 'name', 'qty_ordered', 'qty_invoiced']
            );
            $this->_orderItemsTableJoined = true;
        }
        return $this;
    }

    public function excludeCancelledOrderItems()
    {
        $this->joinOrderItemsInformation();
        $this->getSelect()->joinInner(
            ['order' => $this->getTable('sales_order')],
            'order_item.order_id = order.entity_id',
            []
        );
        $this->getSelect()->where('order.status<>?', Order::STATE_CANCELED);
        return $this;
    }

    public function joinUsersInformation()
    {
        if (!$this->_isUserTableJoined) {
            $this->getSelect()->join(
                ['user_table' => $this->getTable('admin_user')],
                'user_table.user_id=main_table.user_id',
                ['username', 'user_firstname' => 'firstname', 'user_lastname' => 'lastname', 'user_fathername', 'user_position']
            );
            $this->_isUserTableJoined = true;
        }
    }

    public function joinCustomersInformation()
    {
        if (!$this->_isCustomerTableJoined) {
            $this->getSelect()->join(
                ['customers_grid' => $this->getTable('customer_grid_flat')],
                'customers_grid.entity_id=main_table.customer_id',
                ['customer_name' => 'name']
            );
            $this->_isCustomerTableJoined = true;
        }
    }

    public function joinQueueStatus($context)
    {
        $statusIdle = QueueStatus::STATUS_IDLE;
        $this->getSelect()->joinLeft(
            ['queue_status' => $this->getTable('amc_timetable_queue_status')],
            'queue_status.customer_id=main_table.customer_id AND queue_status.context=' . (int)$context,
            ['timetable_status' => new \Zend_Db_Expr("IFNULL(queue_status.status, {$statusIdle})")]
        );
    }
}
