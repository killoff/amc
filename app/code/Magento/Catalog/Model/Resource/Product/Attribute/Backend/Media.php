<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Resource\Product\Attribute\Backend;

/**
 * Catalog product media gallery attribute backend resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Media extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    const GALLERY_TABLE = 'catalog_product_entity_media_gallery';

    const GALLERY_VALUE_TABLE = 'catalog_product_entity_media_gallery_value';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::GALLERY_TABLE, 'value_id');
    }

    /**
     * Load gallery images for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Media $object
     * @return array
     */
    public function loadGallery($product, $object)
    {
        $connection = $this->getConnection();

        $positionCheckSql = $connection->getCheckSql(
            'value.position IS NULL',
            'default_value.position',
            'value.position'
        );

        // Select gallery images for product
        $select = $connection->select()->from(
            ['main' => $this->getMainTable()],
            [
                'value_id',
                'file' => 'value'
            ]
        )->joinLeft(
            ['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            $connection->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$product->getStoreId()),
            [
                'label',
                'position',
                'disabled'
            ]
        )->joinLeft(
            // Joining default values
            ['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            'main.value_id = default_value.value_id AND default_value.store_id = 0',
            ['label_default' => 'label', 'position_default' => 'position', 'disabled_default' => 'disabled']
        )->where(
            'main.attribute_id = ?',
            $object->getAttribute()->getId()
        )->where(
            'main.entity_id = ?',
            $product->getId()
        )
        ->where($positionCheckSql . ' IS NOT NULL')
        ->order($positionCheckSql . ' ' . \Magento\Framework\DB\Select::SQL_ASC);

        $result = $connection->fetchAll($select);
        $this->_removeDuplicates($result);
        return $result;
    }

    /**
     * Remove duplicates
     *
     * @param array &$result
     * @return $this
     */
    protected function _removeDuplicates(&$result)
    {
        $fileToId = [];

        foreach (array_keys($result) as $index) {
            if (!isset($fileToId[$result[$index]['file']])) {
                $fileToId[$result[$index]['file']] = $result[$index]['value_id'];
            } elseif ($fileToId[$result[$index]['file']] != $result[$index]['value_id']) {
                $this->deleteGallery($result[$index]['value_id']);
                unset($result[$index]);
            }
        }

        $result = array_values($result);
        return $this;
    }

    /**
     * Insert gallery value to db and retrieve last id
     *
     * @param array $data
     * @return integer
     */
    public function insertGallery($data)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable(new \Magento\Framework\DataObject($data), $this->getMainTable());
        $connection->insert($this->getMainTable(), $data);

        return $connection->lastInsertId($this->getMainTable());
    }

    /**
     * Delete gallery value in db
     *
     * @param array|integer $valueId
     * @return $this
     */
    public function deleteGallery($valueId)
    {
        if (is_array($valueId) && count($valueId) > 0) {
            $condition = $this->getConnection()->quoteInto('value_id IN(?) ', $valueId);
        } elseif (!is_array($valueId)) {
            $condition = $this->getConnection()->quoteInto('value_id = ? ', $valueId);
        } else {
            return $this;
        }

        $this->getConnection()->delete($this->getMainTable(), $condition);
        return $this;
    }

    /**
     * Insert gallery value for store to db
     *
     * @param array $data
     * @return $this
     */
    public function insertGalleryValueInStore($data)
    {
        $data = $this->_prepareDataForTable(new \Magento\Framework\DataObject($data), $this->getTable(self::GALLERY_VALUE_TABLE));
        $this->getConnection()->insert($this->getTable(self::GALLERY_VALUE_TABLE), $data);

        return $this;
    }

    /**
     * Delete gallery value for store in db
     *
     * @param integer $valueId
     * @param integer $storeId
     * @return $this
     */
    public function deleteGalleryValueInStore($valueId, $storeId)
    {
        $connection = $this->getConnection();

        $conditions = implode(
            ' AND ',
            [
                $connection->quoteInto('value_id = ?', (int)$valueId),
                $connection->quoteInto('store_id = ?', (int)$storeId)
            ]
        );

        $connection->delete($this->getTable(self::GALLERY_VALUE_TABLE), $conditions);

        return $this;
    }

    /**
     * Duplicates gallery db values
     *
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Media $object
     * @param array $newFiles
     * @param int $originalProductId
     * @param int $newProductId
     * @return $this
     */
    public function duplicate($object, $newFiles, $originalProductId, $newProductId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['value_id', 'value']
        )->where(
            'attribute_id = ?',
            $object->getAttribute()->getId()
        )->where(
            'entity_id = ?',
            $originalProductId
        );

        $valueIdMap = [];
        // Duplicate main entries of gallery
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $data = [
                'attribute_id' => $object->getAttribute()->getId(),
                'entity_id' => $newProductId,
                'value' => isset($newFiles[$row['value_id']]) ? $newFiles[$row['value_id']] : $row['value'],
            ];

            $valueIdMap[$row['value_id']] = $this->insertGallery($data);
        }

        if (count($valueIdMap) == 0) {
            return $this;
        }

        // Duplicate per store gallery values
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::GALLERY_VALUE_TABLE)
        )->where(
            'value_id IN(?)',
            array_keys($valueIdMap)
        );

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $row['value_id'] = $valueIdMap[$row['value_id']];
            $this->insertGalleryValueInStore($row);
        }

        return $this;
    }
}
