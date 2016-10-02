<?php
namespace Amc\Protocol\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Textarea;
use Magento\Framework\Escaper;

class Renderer extends Textarea
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        $data = []
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

//    public function getElementHtml()
//    {
//        $html = parent::getElementHtml();
//        return $html . '<textarea id="protocol-form" class="protocol-output" style="display:none"></textarea>';
//    }

    public function getAfterElementHtml()
    {
        return sprintf('<a href="%s" data-protocol-id="%s" data-selector="%s">%s</a>',
            $this->backendUrl->getUrl('protocol/index/load'),
            $this->getData('protocol_id'),
            'protocol-opener',
            __('open protocol')
        );
    }
}
