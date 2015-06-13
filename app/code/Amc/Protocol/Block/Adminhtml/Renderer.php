<?php
namespace Amc\Protocol\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;
use Magento\TestFramework\Event\Magento;

class Renderer extends Select
{
    protected $protocolFactory;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param \Amc\Protocol\Model\ProtocolFactory $protocolFactory
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Amc\Protocol\Model\ProtocolFactory $protocolFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        $data = []
    )
    {
        $this->protocolFactory = $protocolFactory;
        $this->backendUrl = $backendUrl;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        return $html . '<textarea id="protocol-form" class="protocol-output" style="display:none"></textarea>';
    }


    public function getAfterElementHtml()
    {
        return sprintf('<a href="%s" data-selector="%s" data-dropdawn-id="%s">%s</a>',
            $this->backendUrl->getUrl('protocol/index/load'),
            'protocol-opener',
            $this->getHtmlId(),
            __('open protocol')
        );
    }

    public function getValues()
    {
        return $this->protocolFactory->create()->getResourceCollection()->toOptionArray();
    }
}
