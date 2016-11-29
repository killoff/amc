<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Framework\DB\Transaction;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class Pay extends Action
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var InvoiceService */
    private $invoiceService;

    /** @var Transaction */
    private $transaction;

    /** @var JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepositoryInterface,
        InvoiceService $invoiceService,
        Transaction $transaction,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepositoryInterface;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $result = [];
        $itemsQty = $this->getRequest()->getParam('qtys', []);
        try {
            foreach ($itemsQty as $orderId => $orderItemsQty) {
                // skip invoices with 0 qty
                if (array_sum($orderItemsQty) == 0) {
                    continue;
                }
                $order = $this->orderRepository->get($orderId);
                if($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order, $orderItemsQty);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $this->transaction
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();
                        // $this->invoiceSender->send($invoice);
                        // $order->addStatusHistoryComment(
                        //      __('Notified customer about invoice #%1.', $invoice->getId())
                        // )
                        // ->setIsCustomerNotified(true)
                        // ->save();
                    // in order to load items from db
                    $invoice->unsetData(InvoiceInterface::ITEMS);
                    /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
                    foreach ($invoice->getItemsCollection() as $item) {
                        $result[] = ['order_item_id' => $item->getOrderItemId(), 'qty' => $item->getQty()];
                    }
                }
            }
        } catch (\Exception $e) {
            $resultJson->setHttpResponseCode(400);
            $result = ['error' => $e->getMessage()];
        }
        return $resultJson->setData($result);
    }
}
