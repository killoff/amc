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

    public function joinUsers()
    {
        $this->getSelect()->joinInner(
            ['u' => $this->getTable('admin_user')],
            'main_table.user_id = u.user_id',
            ['u.firstname', 'u.lastname', 'u.user_fathername', 'u.user_position']
        );
    }

    public function addProductFilter($productId)
    {
        $this->getSelect()->joinInner(
            ['up' => $this->getTable('amc_admin_user_products')],
            $this->getConnection()->quoteInto('main_table.user_id = up.user_id AND up.product_id = ?', $productId),
            []
        );
    }
}
