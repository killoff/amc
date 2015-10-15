<?php
namespace Amc\Protocol\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class Protocol extends \Magento\Backend\Block\Template
{
    protected $_template = 'protocol.phtml';

    /** @var \Amc\Protocol\Model\ResourceModel\Protocol */
    private $protocol;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amc\Protocol\Model\ResourceModel\Protocol $protocol,
        array $data = []
    ) {
        $this->protocol = $protocol;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $items = $this->protocol->getProtocolItems(1);
        $metadata = array();
        $children = array();
        if (is_array($items)) {
            foreach ($items as $row) {
                $metadata[$row['row_id']] = $row;
                $children[$row['parent_id']][] = $row['row_id'];
            }
        }
        $this->setItemsMetadata($metadata);
        $this->setItemsChildren($children);
    }

    public function getItemsMetadataJson()
    {
//        foreach ($this->getDataSetDefault('items_metadata', array()) as $i => $row) {
//
//            var metadata = {"1":{"row_id":"1","parent_id":"0","title":"\u041b\u043e\u043a\u0442\u0435\u0432\u044b\u0435 \u0441\u0443\u0441\u0442\u0430\u0432\u044b","text":"","action":"","product_id":"29"},"2"
//        }
        $metadata = $this->getItemsMetadata();
        return json_encode($metadata);
    }

    public function getItemsChildrenJson()
    {
        $a = '_    ЗАКЛЮЧЕНИЕ: Ультразвуковых признаков патолог.';
        $children = $this->getItemsChildren();
        return json_encode($children);
    }
}
