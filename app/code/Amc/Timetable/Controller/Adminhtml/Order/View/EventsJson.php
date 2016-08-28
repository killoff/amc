<?php

namespace Amc\Timetable\Controller\Adminhtml\Order\View;

use Magento\Backend\App\Action;

class EventsJson extends Action
{
    /** @var \Amc\Timetable\Model\OrderTimetable */
    private $orderTimetable;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Amc\Timetable\Model\OrderTimetable $orderTimetable,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->orderTimetable = $orderTimetable;
        $this->orderFactory = $orderFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $response = [];
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($this->_request->getParam('order_id'));
            $aggregated = $this->orderTimetable->getAggregated($order, '2016-01-01 12:00:00', '2017-01-01 12:00:00');
            foreach ($order->getAllVisibleItems() as $item) {
                foreach ($aggregated['events'] as $event) {
                    $response[] = [
                        'resourceId' => sprintf('i%s_u%s', $item->getId(), $event['user_id']),
                        'id'         => $event['id'], // todo
                        'start'      => $event['start_at'],
                        'end'        => $event['end_at'],
                        'rendering'  => 'background', //$event['end_at'] === $item->getId() ? '' : 'background',
                        'color'      => $event['type'] === 'schedule' ? '#00c853' : '#ff8a80',
                        'belongs_to_current_order' => false,// $occupied->getOrderItemId() === $item->getId() ? true : false,
                        'overlap'    => true,
                        'title'      => '', // 'room '.$occupied->getRoomId(),
                        'type'       => 'schedule',
                        'room_id'    => $event['room_id'],
                        'user_id'    => $event['user_id'],
                        'sales_item_id' => $item->getId(),
                    ];
                }
            }
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
