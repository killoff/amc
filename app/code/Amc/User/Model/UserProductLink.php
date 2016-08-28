<?php

namespace Amc\User\Model;

class UserProductLink
{
    /**
     * @var string
     */
    protected $relationTableName = 'amc_user_products';

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
     * @return $this
     */
    public function addRelation(\Magento\User\Model\User $user)
    {
        $user->setIsChangedProductList(false);
        $id = $user->getId();

        $products = $user->getPostedProducts();

        if ($products === null) {
            return $this;
        }

        $oldProducts = $this->getProductsIncome($user);

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $connection = $this->resource->getConnection();

        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => array_keys($delete), 'user_id=?' => $id];
            $connection->delete($this->getRelationTableName(), $cond);
        }

        /**
         * Add products to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $income) {
                $data[] = [
                    'user_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'income' => $income,
                ];
            }
            $connection->insertMultiple($this->getRelationTableName(), $data);
        }

        if (!empty($update)) {
            foreach ($update as $productId => $income) {
                $where = ['user_id = ?' => (int)$id, 'product_id = ?' => (int)$productId];
                $bind = ['income' => $income];
                $connection->update($this->getRelationTableName(), $bind, $where);
            }
        }
    }

    /**
     * @param \Magento\User\Model\User $user
     * @return array
     */
    public function getProductsIncome(\Magento\User\Model\User $user)
    {
        $select = $this->resource->getConnection()->select()->from(
            $this->getRelationTableName(),
            ['product_id', 'income']
        )->where(
            'user_id = :user_id'
        );
        $bind = ['user_id' => (int)$user->getId()];

        return $this->resource->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getUserProducts($userId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->getRelationTableName(), ['product_id'])
            ->where('user_id = :user_id');

        return $connection->fetchCol($select, ['user_id' => $userId]);
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getProductUsers($productId)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->getRelationTableName(), ['user_id'])
            ->where('product_id = :product_id');

        return $connection->fetchCol($select, ['product_id' => $productId]);
    }

    public function isProductAssignedToUser($userId, $productId)
    {
        return in_array($productId, $this->getUserProducts($userId));
    }

    /**
     * @return string
     */
    public function getRelationTableName()
    {
        return $this->resource->getConnection()->getTableName($this->relationTableName);
    }
}
