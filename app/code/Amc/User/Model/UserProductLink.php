<?php

namespace Amc\User\Model;

class UserProductLink
{
    /**
     * @var string
     */
    protected $relationTableName = 'amc_admin_user_products';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\User\Model\User $user
     * @param array $products
     */
    public function addRelation(\Magento\User\Model\User $user, array $products)
    {
        $oldProducts = $this->getUserProducts($user);

        $insert = array_diff($products, $oldProducts);
        $delete = array_diff($oldProducts, $products);

        $connection = $this->resource->getConnection();

        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => $delete, 'user_id=?' => $user->getId()];
            $connection->delete($this->getRelationTableName(), $cond);
        }

        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId) {
                $data[] = [
                    'user_id' => $user->getId(),
                    'product_id' => (int)$productId
                ];
            }
            $connection->insertMultiple($this->getRelationTableName(), $data);
        }
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return array
     */
    public function getUserProducts(\Magento\User\Model\User $user)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->getRelationTableName(), ['product_id'])
            ->where('user_id = :user_id');

        return $connection->fetchCol($select, ['user_id' => $user->getId()]);
    }

    /**
     * @return string
     */
    public function getRelationTableName()
    {
        return $this->resource->getConnection()->getTableName($this->relationTableName);
    }
}
