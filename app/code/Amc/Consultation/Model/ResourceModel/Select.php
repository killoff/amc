<?php
namespace Amc\Consultation\Model\ResourceModel;

class Select extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ConfigFactory
     */
    protected $_catalogConfFactory;

    /**
     * @var \Magento\Catalog\Model\Entity\AttributeFactory
     */
    protected $_catalogAttrFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogConfFactory = $catalogConfFactory;
        $this->_catalogAttrFactory = $catalogAttrFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
    }

    public function joinProductName(\Magento\Framework\DB\Select $select)
    {
        $entityTypeId = $this->_catalogConfFactory->create()->getEntityTypeId();
        /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
        $attribute = $this->_catalogAttrFactory->create()->loadByCode($entityTypeId, 'name');

        $storeId = $this->_storeManager->getStore(\Magento\Store\Model\Store::ADMIN_CODE)->getId();

        $select->join(
            ['product_name_table' => $attribute->getBackendTable()],
            'product_name_table.entity_id=main_table.product_id' .
            ' AND product_name_table.store_id=' . $storeId .
            ' AND product_name_table.attribute_id=' . $attribute->getId(),
            ['product_name' => 'product_name_table.value']
        );

        return $this;
    }

    public function joinUserName(\Magento\Framework\DB\Select $select)
    {
        $select->join(
            ['user_table' => $this->getTable('admin_user')],
            'user_table.user_id=main_table.user_id',
            ['username']
        );
        return $this;
    }
}
