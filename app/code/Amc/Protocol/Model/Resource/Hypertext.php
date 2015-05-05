<?php
namespace Amc\Protocol\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;

class Hypertext extends AbstractDb
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

    public function saveProtocol($protocol, $rows)
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
//        print_r($data);
        return $this->_getWriteAdapter()->insertMultiple($this->getTable('protocol_rows'), $data);
    }

    /**
     * Return array of Protocol items for product
     *
     * @param $protocolId
     * @return array
     */
    public function getProtocolItems($protocolId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('protocol_rows'))
            ->where('protocol_id=?', $protocolId);
        return $adapter->fetchAll($select);
    }
}
