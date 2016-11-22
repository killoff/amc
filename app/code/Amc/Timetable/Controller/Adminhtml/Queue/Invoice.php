<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class Invoice extends Action
{
    /** @var OrderCollectionFactory */
    private $orderCollectionFactory;

    /** @var InvoiceService */
    private $invoiceService;

    /** @var JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        InvoiceService $invoiceService,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = [];
        $customerId = $this->getRequest()->getParam('customer_id');
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('state', [ 'in' => [Order::STATE_NEW, Order::STATE_PROCESSING] ])
            ->setOrder('created_at', 'ASC');
        /** @var Order $order */
        foreach ($orderCollection->getItems() as $order) {
            if (!$order->canInvoice()) {
                continue;
            }

            $invoice = $this->invoiceService->prepareInvoice($order);

            if (!$invoice->getTotalQty()) {
                continue;
            }
            $invoiceItems = [];
            /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
            foreach ($invoice->getItemsCollection() as $item) {
                $invoiceItems[] = [
                    'product_id' => $item->getProductId(),
                    'order_item_id' => $item->getOrderItemId(),
                    'name' => $item->getName(),
                    'sku' => $item->getProductId(),
                    'price' => $item->getPriceInclTax(),
                    'row_total' => $item->getRowTotalInclTax(),
                    'qty' => $item->getQty(),
                ];
            }

            $result[] = [
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'items' => $invoiceItems,
                'total' => $invoice->getGrandTotal(),
                'discount_amount' => $invoice->getDiscountAmount()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( array_values($result) );
    }
}
