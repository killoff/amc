<?php
namespace Amc\Protocol\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Protocol extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('protocol', 'protocol_id');
    }

    public function saveProducts($protocol, array $productIds = [])
    {
        if (empty($productIds)) {
            return false;
        }
        $data = [];
        $protocolId = $protocol->getId();
        foreach ($productIds as $productId) {
            $data[] = [
                'product_id' => $productId,
                'protocol_id' => $protocolId
            ];
        }
        $this->getConnection()->delete($this->getTable('protocol_products'), 'protocol_id=' . (int)$protocolId);
        return $this->getConnection()->insertMultiple($this->getTable('protocol_products'), $data);
    }

    public function saveHypertext($protocol, $text)
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getTable('protocol_hypertext'),
            ['protocol_id' => $protocol->getId(), 'text' => $text],
            ['text']
        );
    }

    public function saveHypertextRows($protocol, $rows)
    {
        if (empty($rows)) {
            return 0;
        }
        $data = [];
        foreach ($rows as $index => $row) {
            $data[] = [
                'row_id' => $index,
                'parent_id' => $row['parent'],
                'protocol_id' => $protocol->getId(),
                'title' => $row['title'],
                'text' => $row['text'],
                'action' => $row['action'],
            ];
        }
        return $this->getConnection()->insertMultiple($this->getTable('protocol_rows'), $data);
    }

    /**
     * Return array of Protocol items for product
     *
     * @param $protocolId
     * @return array
     */
    public function getProtocolItems($protocolId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getTable('protocol_rows'))
            ->where('protocol_id=?', $protocolId);
        return $adapter->fetchAll($select);
    }
}
