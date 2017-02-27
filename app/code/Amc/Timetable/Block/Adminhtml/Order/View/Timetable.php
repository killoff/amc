<?php
namespace Amc\Timetable\Block\Adminhtml\Order\View;

use \Amc\Timetable\Block\Adminhtml\Order\TimetableInterface;
use \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection as OrderEventCollection;
use \Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory as OrderEventCollectionFactory;
use \Magento\Backend\Block\Template;

class Timetable extends Template implements TimetableInterface
{
    /** @var OrderEventCollectionFactory */
    private $orderEventCollectionFactory;

    /** @var \Magento\Framework\Registry  */
    private $coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        OrderEventCollectionFactory $orderEventCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getWidgetOptionsJson()
    {
        // todo:
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
            'init_registry_url' => $this->getUrl('timetable/order/orderEventsJson', ['order_id' => $this->getOrderId()]),
            'resourceLabelText' => __('Executant'),
            'registry_json_field_name' => 'order[timetable_json]',
        ];
        return \Zend_Json::encode($options);
    }

    /**
     * Return datetime for order calendar - either first appointment or order creation date if there is no appointments
     * @return string yyyy-mm-dd hh:i:s
     */
    private function getInitialDate()
    {
        /** @var OrderEventCollection $orderEventsCollection */
        $orderEventsCollection = $this->orderEventCollectionFactory->create();
        $orderEventsCollection->whereOrderId($this->getOrderId());
        $orderEventsCollection->setOrder('main_table.start_at', 'ASC');
        $nearestEvent = $orderEventsCollection->getFirstItem();
        if ($nearestEvent->getId()) {
            return $nearestEvent->getData('start_at');
        } else {
            return $this->getData('initial_date') ? $this->getData('initial_date') : $this->getOrder()->getCreatedAt();
        }
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
