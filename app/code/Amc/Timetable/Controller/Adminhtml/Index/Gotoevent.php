<?php

namespace Amc\Timetable\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Gotoevent extends Action
{
    private $orderItemFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
    ) {
        parent::__construct($context);
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $orderItemId = $this->getRequest()->getParam('item');
        $orderId = $this->orderItemFactory->create()->load($orderItemId)->getOrderId();
        $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
