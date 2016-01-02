<?php

namespace Amc\UserSchedule\Model\ResourceModel\Schedule\Item;

use Magento\Framework\Model\ModelResource\Db\Collection\AbstractCollection;

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
        $this->getSelect()->joinInner(
            ['up' => $this->getTable('amc_user_products')],
            $condition,
            []
        );
        return $this;
    }

    public function groupByUsers()
    {
        $this->getSelect()->group('main_table.user_id');
        return $this;
    }
}
