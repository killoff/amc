<?php

namespace Amc\Timetable\Model\Plugin\Order;

class Create
{
    /** @var \Magento\Framework\Json\DecoderInterface  */
    private $jsonDecoder;

    /** @var \Magento\Framework\DataObject\Factory */
    private $orderEventFactory;

    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Amc\Timetable\Model\OrderEventFactory $orderEventFactory
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->orderEventFactory = $orderEventFactory;
    }

    /**
     * Save timetable for created order
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Sales\Model\Order $order
     * @return mixed
     */
    public function afterCreateOrder(\Magento\Sales\Model\AdminOrder\Create $subject, \Magento\Sales\Model\Order $order)
    {
        $timetable = $subject->getData('timetable_json');
        try {
            $timetable = $this->jsonDecoder->decode($timetable);
        } catch (\Exception $e) {
            return $order;
        }
        if (! is_array($timetable)) {
            return $order;
        }
        $saved = [];
        foreach ($timetable as $event) {
            $uuid = isset($event['uuid']) ? $event['uuid'] : '';
            if (! $uuid) {
                continue;
            }
            $event['order_item_id'] = $this->getOrderItemIdByQuoteItemId($order, $event['sales_item_id']);
            if (! $event['order_item_id']) {
                continue;
            }
            $event['customer_id'] = $order->getCustomerId();
            if (!isset($event['deleted'])) {
                /** @var \Amc\Timetable\Model\OrderEvent $eventModel */
                $eventModel = $this->orderEventFactory->create();
                $eventModel->addData($event);
                $eventModel->save();
                $saved[$uuid] = $eventModel->getId();
            }
        }
        return $order;
    }

    private function getOrderItemIdByQuoteItemId(\Magento\Sales\Model\Order $order, $quoteItemId)
    {
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getQuoteItemId() == $quoteItemId) {
                return $item->getId();
            }
        }
        return null;
    }
}
