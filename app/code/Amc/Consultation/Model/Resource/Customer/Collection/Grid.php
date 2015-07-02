<?php

namespace Amc\Consultation\Model\Resource\Customer\Collection;

use Magento\Customer\Controller\RegistryConstants as RegistryConstants;

class Grid extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Resource\ConfigFactory
     */
    protected $_catalogConfFactory;

    /**
     * @var \Magento\Catalog\Model\Entity\AttributeFactory
     */
    protected $_catalogAttrFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var bool
     */
    protected $_isProductNameJoined = false;

    /**
     * @var bool
     */
    protected $_isUserTableJoined = false;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registry
     * @param null $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    )
    {
        $this->_registryManager = $registry;
        $this->_storeManager = $storeManager;
        $this->_catalogConfFactory = $catalogConfFactory;
        $this->_catalogAttrFactory = $catalogAttrFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Amc\Consultation\Model\Consultation', 'Amc\Consultation\Model\Resource\Consultation');
    }

    /**
     * Initialize db select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addCustomerIdFilter(
            $this->_registryManager->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );
        $this->_joinProductNameTable();
        $this->_joinUserTable();
        return $this;
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()->where(
            'main_table.customer_id = ?',
            $customerId
        );
        return $this;
    }

    /**
     * Joins product name attribute value to use it in WHERE and ORDER clauses
     *
     * @return void
     */
    protected function _joinProductNameTable()
    {
        if (!$this->_isProductNameJoined) {
            $entityTypeId = $this->_catalogConfFactory->create()->getEntityTypeId();
            /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $attribute = $this->_catalogAttrFactory->create()->loadByCode($entityTypeId, 'name');

            $storeId = $this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();

            $this->getSelect()->join(
                ['product_name_table' => $attribute->getBackendTable()],
                'product_name_table.entity_id=main_table.product_id' .
                ' AND product_name_table.store_id=' .
                $storeId .
                ' AND product_name_table.attribute_id=' .
                $attribute->getId(),
                []
            );

            $this->_isProductNameJoined = true;
        }
    }

    /**
     * @return void
     */
    protected function _joinUserTable()
    {
        if (!$this->_isUserTableJoined) {
            $this->getSelect()->join(
                ['user_table' => $this->getTable('admin_user')],
                'user_table.user_id=main_table.user_id',
                ['username']
            );
            $this->_isUserTableJoined = true;
        }
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
     * Adds filter on product name
     *
     * @param string $productName
     * @return $this
     */
    public function addProductNameFilter($productName)
    {
        $this->_joinProductNameTable();
        $this->getSelect()->where('INSTR(product_name_table.value, ?)', $productName);

        return $this;
    }

    /**
     * Sets ordering by product name
     *
     * @param string $dir
     * @return $this
     */
    public function setOrderByProductName($dir)
    {
        $this->_joinProductNameTable();
        $this->getSelect()->order('product_name_table.value ' . $dir);
        return $this;
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
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $productCollection */
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
