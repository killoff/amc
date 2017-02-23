<?php
namespace Amc\Sales\Controller\Adminhtml\Order\Item;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class Cancel extends Action
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @todo: Exceptions handling
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->orderItemRepository->get($itemId);
        $orderItem->cancel();
        $this->orderItemRepository->save($orderItem);
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('sales/order/view', ['order_id' => $orderItem->getOrderId()]);
        return $redirect;
    }
}
