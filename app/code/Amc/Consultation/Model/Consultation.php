<?php

namespace Amc\Consultation\Model;

use Magento\Framework\Model\AbstractModel;

class Consultation extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'consultation';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Amc\Consultation\Model\ResourceModel\Consultation');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getId()) {
            $identities = [self::CACHE_TAG . '_' . $this->getId()];
        }
        return $identities;
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    public function getUserId()
    {
        return $this->getData('user_id');
    }

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    public function getOrderItemId()
    {
        return $this->getData('order_item_id');
    }

    public function getUserDate()
    {
        return $this->getData('user_date');
    }

    public function getJsonData()
    {
        return $this->getData('json_data');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }
}
