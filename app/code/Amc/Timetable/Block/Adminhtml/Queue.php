<?php
namespace Amc\Timetable\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\UrlInterface;
use Amc\Timetable\Model\ResourceModel\CustomerStatus;

class Queue extends Template
{
    /** @var UrlInterface */
    private $urlBuilder;

    /** @var CustomerStatus */
    private $customerStatus;

    public function __construct(
        Template\Context $context,
        UrlInterface $urlBuilder,
        CustomerStatus $customerStatus,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->customerStatus = $customerStatus;
    }

    public function getComponentOptionsJson()
    {
        $options = [
            'component' => 'Amc_Timetable/js/queue',
            'template' => 'Amc_Timetable/queue',
            'source_url' => $this->urlBuilder->getUrl('timetable/queue/jsonFeed'),
            'invoice_url' => $this->urlBuilder->getUrl('timetable/queue/invoice'),
            'change_status_url' => $this->urlBuilder->getUrl('timetable/queue/changeStatus'),
            'statuses' => $this->getStatuses(),
//            'add_to_cart_url' => $this->urlBuilder->getUrl('smc_checkout/cart/add'),
//            'cart_url' => $this->urlBuilder->getUrl('checkout'),
        ];

        return \Zend_Json::encode($options);
    }

    private function getStatuses()
    {
        $result = [];
        foreach ($this->customerStatus->getAllStatuses() as $status => $label) {
            $result[] = ['status' => $status, 'label' => $label];
        }
        return $result;
    }

    public function getTodayDate()
    {
        return (new \DateTime())->setTime(0, 0, 0)->format('D, j.m');
    }


}
