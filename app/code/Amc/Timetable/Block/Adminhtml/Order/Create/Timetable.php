<?php
namespace Amc\Timetable\Block\Adminhtml\Order\Create;

use \Amc\Timetable\Block\Adminhtml\Order\TimetableInterface;
use \Magento\Backend\Block\Template;

class Timetable extends Template implements TimetableInterface
{
    /** @var \Magento\Backend\Model\Session\Quote */
    private $sessionQuote;

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
        // get resources from now for 30 days
        $start = new \DateTime($this->sessionQuote->getQuote()->getCreatedAt());
        $end = (clone $start)->add(new \DateInterval('P30D'));
        $options = [
            'resources' => [
                'url' => $this->getUrl('timetable/order/resourcesJson'),
                'data' => [
                    'quote_id' => $this->getQuoteId(),
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ]
            ],
            'events' => [
                'url' => $this->getUrl('timetable/order/eventsJson'),
                'data' => [
                    'quote_id' => $this->getQuoteId(),
                    'day' => $start->format('Y-m-d'),
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
