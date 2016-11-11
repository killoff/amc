<?php
namespace Amc\Timetable\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\UrlInterface;

class Queue extends Template
{
    /** @var UrlInterface */
    private $urlBuilder;

    public function __construct(
        Template\Context $context,
        UrlInterface $urlBuilder,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function getComponentOptionsJson()
    {
        $options =  [
            'component' => 'Amc_Timetable/js/queue',
            'template' => 'Amc_Timetable/queue',
            'source_url' => $this->urlBuilder->getUrl('timetable/index/queueJson'),
//            'add_to_cart_url' => $this->urlBuilder->getUrl('smc_checkout/cart/add'),
//            'cart_url' => $this->urlBuilder->getUrl('checkout'),
        ];

        return \Zend_Json::encode($options);
    }
}
