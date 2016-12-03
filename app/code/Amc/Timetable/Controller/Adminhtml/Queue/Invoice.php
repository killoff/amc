<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice as OrderInvoice;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Amc\Timetable\Model\ResourceModel\InvoiceItem;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Framework\DB\Transaction as DbTransaction;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Invoice extends Action
{
    /** @var OrderCollectionFactory */
    private $orderCollectionFactory;

    /** @var InvoiceService */
    private $invoiceService;

    /** @var InvoiceItem */
    private $invoiceItem;

    /** @var DbTransaction */
    private $dbTransaction;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /** @var DateTime */
    private $dateTime;

    /** @var string */
    private $todayDate;

    /** @var JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        InvoiceService $invoiceService,
        InvoiceItem $invoiceItem,
        DbTransaction $dbTransaction,
        PriceCurrencyInterface $priceCurrency,
        DateTime $dateTime,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->invoiceItem = $invoiceItem;
        $this->dbTransaction = $dbTransaction;
        $this->priceCurrency = $priceCurrency;
        $this->dateTime = $dateTime;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->todayDate = $this->dateTime->gmtDate();
    }

    /**
     * Represent 3 actions:
     *   - generate future invoices: context: invoices
     *   - update invoices items qty: context: update_qty
     *   - create invoices: context: pay
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        try {
            $customerId = $this->getRequest()->getParam('customer_id');
            $context = $this->getRequest()->getParam('context', 'invoices');
            $itemsQty = $this->getRequest()->getParam('qtys', []);
            /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('state', ['in' => [Order::STATE_NEW, Order::STATE_PROCESSING]])
                ->setOrder('created_at', 'ASC');

            $invoices = [];
            /** @var Order $order */
            foreach ($orderCollection->getItems() as $order) {
                if (!$order->canInvoice()) {
                    continue;
                }

                $orderItemsQty = isset($itemsQty[$order->getId()]) ? $itemsQty[$order->getId()] : [];
                $invoice = $this->invoiceService->prepareInvoice($order, $orderItemsQty);

                // do not skip 0-total invoices in case of context = update_qty
                if (!$invoice->getTotalQty() && 'update_qty' !== $context ) {
                    continue;
                }

                // create invoices
                if ($context === 'pay') {
                    $invoice->register();
                    $transactionSave = $this->dbTransaction
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();
                    // in order to load items from db
                    $invoice->unsetData(InvoiceInterface::ITEMS);
                }

                $invoiceItems = [];
                foreach ($invoice->getItemsCollection() as $item) {
                    $invoiceItems[] = $this->extractInvoiceItemData($item);
                }

                $invoiceData = $this->extractInvoiceData($order, $invoice);
                $invoiceData['items'] = $invoiceItems;
                $invoiceData['total_amount'] = $invoice->getGrandTotal();
                $invoices[] = $invoiceData;
            }

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

    /**
     * @param \Magento\Sales\Model\Order\Invoice\Item $item
     * @return array
     */
    private function extractInvoiceItemData($item)
    {
        $itemDate = $this->invoiceItem->calculateItemExecutionDate($item->getOrderItemId());
        return [
            'product_id' => $item->getProductId(),
            'order_item_id' => $item->getOrderItemId(),
            'name' => $item->getName(),
            'sku' => $item->getProductId(),
            'price' => $this->formatPrice($item->getPriceInclTax()),
            'row_total' => $this->formatPrice($item->getRowTotalInclTax()),
            'discount' => $this->formatPrice($item->getDiscountAmount()),
            'qty' => (int)$item->getQty(),
            'date' => $itemDate,
            'date_text' => $this->formatDate($itemDate),
        ];
    }

    /**
     * @param Order $order
     * @param OrderInvoice $invoice
     * @return array
     */
    private function extractInvoiceData($order, $invoice)
    {
        return [
            'order_id' => $order->getId(),
            'increment_id' => $order->getIncrementId(),
            'created_at' => $order->getCreatedAt(),
            'order_url' => $this->getUrl('sales/order/view', ['order_id' => $order->getId()]),
            'total_amount' => $invoice->getGrandTotal(),
            'discount_amount' => $invoice->getDiscountAmount(),
            'total' => $this->formatPrice($invoice->getGrandTotal()),
            'discount' => $this->formatPrice($invoice->getDiscountAmount()),
            'paid' => $invoice->getId() ? '1' : '0',
            'invoice_increment_id' => $invoice->getId() ? $invoice->getIncrementId() : '',
            'invoice_url' => $invoice->getId() ? $this->getUrl('sales/invoice/view', ['invoice_id' => $invoice->getId()]) : '',
        ];
    }

    private function formatPrice($value)
    {
        return $this->priceCurrency->format($value, false);
    }

    private function formatDate($date)
    {
        $todayDate = (new \DateTime($this->todayDate))->format('d.m.Y');
        $dateTime = new \DateTime($date);
        if ($todayDate === $dateTime->format('d.m.Y')) {
            return $dateTime->format('H:i');
        } else {
            return $dateTime->format('d.m.Y, H:i');
        }
    }
}
