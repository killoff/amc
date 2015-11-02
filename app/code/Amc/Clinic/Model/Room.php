<?php

namespace Amc\Clinic\Model;

use Magento\Framework\Model\AbstractModel;

class Room extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'room';

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
        $this->_init('Amc\Clinic\Model\ResourceModel\Room');
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

    public function getCustomAttributes()
    {
        return [];
    }
}
