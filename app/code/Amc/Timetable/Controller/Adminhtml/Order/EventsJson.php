<?php
namespace Amc\Timetable\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class EventsJson extends Action
{
    /** @var \Amc\Timetable\Model\Aggregated */
    private $timetableAggregated;

    /** @var \Amc\Timetable\Model\Adapter\Fullcalendar */
    private $fullcalendarAdapter;

    /** @var \Magento\Quote\Model\OrderFactory */
    private $orderFactory;

    /** @var \Magento\Quote\Model\QuoteFactory */
    private $quoteFactory;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Amc\Timetable\Model\Aggregated $timetableAggregated,
        \Amc\Timetable\Model\Adapter\Fullcalendar $fullcalendarAdapter,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->timetableAggregated = $timetableAggregated;
        $this->fullcalendarAdapter = $fullcalendarAdapter;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $start = $this->_request->getParam('start');
            $end = $this->_request->getParam('end');
            if ($this->_request->getParam('quote_id')) {
                $quoteId = $this->_request->getParam('quote_id');
                $quote = $this->quoteFactory->create()->load($quoteId);
                $aggregated = $this->timetableAggregated->getForQuote($quote, $start, $end);
                $events = $this->fullcalendarAdapter->getSchedulerEvents($aggregated);
            } elseif ($this->_request->getParam('order_id')) {
                $orderId = $this->_request->getParam('order_id');
                $order = $this->orderFactory->create()->load($orderId);
                $aggregated = $this->timetableAggregated->getForOrder($order, $start, $end);
                $events = $this->fullcalendarAdapter->getSchedulerEvents($aggregated);
            } else {
                $events = ['error' => 'order_id/quote_id not provided'];
            }
        } catch (\Exception $e) {
            $events = ['error' => $e->getMessage()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($events);
    }
}
