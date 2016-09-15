<?php

namespace Amc\UserSchedule\Model\ResourceModel\Schedule\Item;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
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
        $this->_init('Amc\UserSchedule\Model\Schedule\Item', 'Amc\UserSchedule\Model\ResourceModel\Schedule\Item');
    }

    public function addProductFilter($productId)
    {
        return $this->addProductsFilter([$productId]);
    }

    public function addProductsFilter(array $productIds)
    {
        if (0 === count($productIds)) {
            return $this;
        } elseif (1 === count($productIds)) {
            $condition = $this->getConnection()->quoteInto('main_table.user_id = up.user_id AND up.product_id = ?', $productIds[0]);
        } else {
            $condition = $this->getConnection()->quoteInto('main_table.user_id = up.user_id AND up.product_id IN(?)', $productIds);
        }
        // join product ids to be able to know which user has which products assigned
        $columns = [ 'product_ids' => new \Zend_Db_Expr('GROUP_CONCAT(up.product_id)') ];
        $this->getSelect()->group('main_table.entity_id');

        $this->getSelect()->joinInner(
            ['up' => $this->getTable('amc_user_products')],
            $condition,
            $columns
        );
        return $this;
    }

    public function startFrom(\DateTime $startFrom, $strict = false)
    {
        $field = (bool)$strict ? 'start_at' : 'end_at';
        return $this->addFieldToFilter($field, ['gt' => $startFrom->format('Y-m-d H:i:s')]);
    }

    public function endTo(\DateTime $endTo, $strict = false)
    {
        $field = (bool)$strict ? 'end_at' : 'start_at';
        return $this->addFieldToFilter($field, ['lt' => $endTo->format('Y-m-d H:i:s')]);
    }

    public function groupByUsers()
    {
        $this->getSelect()->group('main_table.user_id');
        return $this;
    }
}
