<?php
namespace Amc\Timetable\Block\Adminhtml\Order\Create;

class Timetable extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        $this->sessionQuote = $sessionQuote;
        parent::__construct($context, $data);
    }

    protected function getOrderItems()
    {
        return $this->_layout->getBlock('items_grid')->getItems();
    }

    public function getInitialDate()
    {
        return date('Y-m-d');
    }

    public function getCustomerId()
    {
        return $this->sessionQuote->getCustomerId();
    }

    public function getQuote()
    {
        return $this->sessionQuote->getQuote();
    }
    public function getQuoteId()
    {
        return $this->sessionQuote->getQuote()->getId();
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

}
