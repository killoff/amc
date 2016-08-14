<?php

namespace Amc\Consultation\Model\ResourceModel\Customer\Collection;

class Grid extends \Amc\Consultation\Model\ResourceModel\Consultation\Collection
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory
     * @param \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $registry,
            $storeManager,
            $catalogConfFactory,
            $catalogAttrFactory,
            $connection,
            $resource
        );
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  \Magento\Framework\Data\Collection\Db
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == 'product_name') {
            return $this->setOrderByProductName($direction);
        }
        return parent::setOrder($field, $direction);
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @see self::_getConditionSql for $condition
     * @return \Magento\Framework\Data\Collection\Db
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'product_name':
                $value = (string)$condition['like'];
                $value = trim(trim($value, "'"), "%");
                return $this->addProductNameFilter($value);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->_assignProductNames();

        return $this;
    }

    /**
     * @return void
     */
    protected function _assignProductNames()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect(['name']);

        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                if (!$product->isInStock()) {
                    $this->removeItemByKey($item->getId());
                } else {
                    $item->setProductName($product->getName());
                }
            } else {
                $item->isDeleted(true);
            }
        }
    }
}
