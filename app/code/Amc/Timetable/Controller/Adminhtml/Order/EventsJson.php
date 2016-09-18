<?php
namespace Amc\Timetable\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class EventsJson extends Action
{
    /** @var \Amc\Timetable\Model\Aggregated */
    private $timetableAggregated;

    /** @var \Amc\Timetable\Model\Adapter\Fullcalendar */
    private $fullcalendarAdapter;

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
            $aggregated = $this->getAggregated($this->_request->getParam('quote_id'), $this->_request->getParam('order_id'));
            $resources = $this->fullcalendarAdapter->getSchedulerEvents($aggregated);
        } catch (\Exception $e) {
            $resources = ['error' => $e->getMessage()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($resources);
    }

    /**
     * TODO: refactor due to multiple responsibility
     * @param $quoteId
     * @param $orderId
     * @return array
     * @throws \Exception
     */
    private function getAggregated($quoteId, $orderId)
    {
        if ($quoteId) {
            $quote = $this->quoteFactory->create()->load($quoteId);
            return $this->timetableAggregated->getForQuote($quote, '2016-01-01 12:00:00', '2017-01-01 12:00:00');
        } elseif ($orderId) {
            $order = $this->orderFactory->create()->load($orderId);
            return $this->timetableAggregated->getForOrder($order, '2016-01-01 12:00:00', '2017-01-01 12:00:00');
        } else {
            throw new \Exception('order_id/quote_id not provided');
        }
    }
}
