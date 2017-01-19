<?php
namespace Amc\Timetable\Block\Adminhtml\Order\View;

use \Amc\Timetable\Block\Adminhtml\Order\TimetableInterface;
use \Magento\Backend\Block\Template;

class Timetable extends Template implements TimetableInterface
{
    /** @var \Magento\Framework\Registry  */
    private $coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    // todo: very old orders will be fucked up - only 30 days schedule ahead
    public function getWidgetOptionsJson()
    {
        // get resources from now for 30 days
        $start = new \DateTime($this->getInitialDate());
        $end = (clone $start)->add(new \DateInterval('P30D'));

        $options = [
            'resources' => [
                'url' => $this->getUrl('timetable/order/resourcesJson'),
                'data' => [
                    'order_id' => $this->getOrderId(),
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ]
            ],
            'events' => [
                'url' => $this->getUrl('timetable/order/eventsJson'),
                'data' => [
                    'order_id' => $this->getOrderId()
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
        return $this->getData('initial_date') ? $this->getData('initial_date') : $this->getOrder()->getCreatedAt();
    }

    private function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    private function getOrderId()
    {
        return $this->getOrder()->getId();
    }
}
