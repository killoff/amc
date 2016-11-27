<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Amc\Timetable\Model\ResourceModel\InvoiceItem;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

class Invoice extends Action
{
    /** @var OrderCollectionFactory */
    private $orderCollectionFactory;

    /** @var InvoiceService */
    private $invoiceService;

    /** @var InvoiceItem */
    private $invoiceItem;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /** @var JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        InvoiceService $invoiceService,
        InvoiceItem $invoiceItem,
        PriceCurrencyInterface $priceCurrency,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->invoiceItem = $invoiceItem;
        $this->priceCurrency = $priceCurrency;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        try {
            $customerId = $this->getRequest()->getParam('customer_id');
            $itemsQty = $this->getRequest()->getParam('qtys', []);
            /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('state', ['in' => [Order::STATE_NEW, Order::STATE_PROCESSING]])
                ->setOrder('created_at', 'ASC');
            $invoices = $this->prepareInvoicesForOrders($orderCollection->getItems(), $itemsQty);

            // calculate total for all invoices
            $total = array_sum(array_column($invoices, 'total_amount'));
            $discount = array_sum(array_column($invoices, 'discount_amount'));
            $totals = [
                'total' => $this->formatPrice($total),
                'discount' => $this->formatPrice($discount)
            ];

            $result = [
                'invoices' => $invoices,
                'totals' => $totals,
            ];

        } catch (\Exception $e) {
            $resultJson->setHttpResponseCode(400);
            $result = ['error' => $e->getMessage()];
        }
        return $resultJson->setData($result);
    }

    private function prepareInvoicesForOrders(array $orders, array $itemsQty)
    {
        $result = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            if (!$order->canInvoice()) {
                continue;
            }

            $orderItemsQty = isset($itemsQty[$order->getId()]) ? $itemsQty[$order->getId()] : [];
            $invoice = $this->invoiceService->prepareInvoice($order, $orderItemsQty);

            // only skip 0-total invoices if no items qty passed
            if (empty($itemsQty) && !$invoice->getTotalQty()) {
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
                    'price' => $this->formatPrice($item->getPriceInclTax()),
                    'row_total' => $this->formatPrice($item->getRowTotalInclTax()),
                    'discount' => $this->formatPrice($item->getDiscountAmount()),
                    'qty' => (int)$item->getQty(),
                    'date' => $this->invoiceItem->calculateItemExecutionDate($item->getOrderItemId()),
                ];
            }

            $result[] = [
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'order_url' => $this->getUrl('sales/order/view', ['order_id' => $order->getId()]),
                'items' => $invoiceItems,
                'total_amount' => $invoice->getGrandTotal(),
                'discount_amount' => $invoice->getDiscountAmount(),
                'total' => $this->formatPrice($invoice->getGrandTotal()),
                'discount' => $this->formatPrice($invoice->getDiscountAmount())
            ];
        }
        return $result;

    }

    private function formatPrice($value)
    {
        return $this->priceCurrency->format($value, false);
    }
}
