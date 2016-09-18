<?php
namespace Amc\Timetable\Block\Adminhtml\Order\Create;

use \Amc\Timetable\Block\Adminhtml\Order\TimetableInterface;
use \Magento\Backend\Block\Template;

class Timetable extends Template implements TimetableInterface
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        $this->sessionQuote = $sessionQuote;
        parent::__construct($context, $data);
    }

    public function getWidgetOptionsJson()
    {
        $options = [
            'resources' => [
                'url' => $this->getUrl('timetable/order/resourcesJson'),
                'data' => [
                    'quote_id' => $this->getQuoteId()
                ]
            ],
            'events' => [
                'url' => $this->getUrl('timetable/order/eventsJson'),
                'data' => [
                    'quote_id' => $this->getQuoteId()
                ]
            ],
            'defaultDate' => $this->getInitialDate(),
            'resourceLabelText' => __('Executant'),
            'registry_json_field_name' => 'order[timetable_json]',
        ];
        return \Zend_Json::encode($options);
    }

    private function getInitialDate()
    {
        return date('Y-m-d');
    }

    private function getQuoteId()
    {
        return $this->sessionQuote->getQuote()->getId();
    }
}
