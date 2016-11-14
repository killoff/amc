<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\CustomerDue;
use Magento\Framework\Controller\Result\JsonFactory;

class Invoice extends \Magento\Backend\App\Action
{
    /** @var CustomerDue */
    private $customerDue;

    /** @var JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        CustomerDue $customerDue,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->customerDue = $customerDue;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $orderItems = $this->customerDue->getOrderItemsDue($customerId);
        $groupByOrders = $this->groupByOrders($orderItems);
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( array_values($groupByOrders) );
    }

    private function groupByOrders(array $orderItems)
    {
        $result = [];
        foreach($orderItems as $item) {
            $orderId = $item['order_id'];
            if (!isset($result[$orderId])) {
                $result[$orderId] = [
                    'order' => [
                        'id' => $orderId,
                        'increment_id' => $item['increment_id'],
                        'created_at' => $item['created_at']
                    ],
                    'items' => []
                ];
            }
            $result[$orderId]['items'][] = $item;
        }
        return $result;
    }
}
