<?php
namespace Amc\Timetable\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\OrderEvent\CollectionFactory as OrderEventCollectionFactory;
use Amc\User\Helper\Data as UserHelper;
use Magento\Framework\Controller\Result\JsonFactory;

class OrderEventsJson extends Action
{
    /** @var OrderEventCollectionFactory */
    private $orderEventCollectionFactory;

    /** @var JsonFactory */
    private $jsonFactory;

    /** @var \Amc\User\Helper\Data */
    private $userHelper;

    public function __construct(
        Action\Context $context,
        OrderEventCollectionFactory $orderEventCollectionFactory,
        UserHelper $userHelper,
        JsonFactory $resultJsonFactory
    ) {
        $this->orderEventCollectionFactory = $orderEventCollectionFactory;
        $this->userHelper = $userHelper;
        $this->jsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            /** @var \Amc\Timetable\Model\ResourceModel\OrderEvent\Collection $collection */
            $collection = $this->orderEventCollectionFactory->create();
            $collection->joinOrderItemsInformation();
            $collection->whereOrderId($orderId);
            $collection->joinUsersInformation();
            foreach ($collection as $item) {
                $data = $item->getData();
                $data['sales_item_id'] = $data['order_item_id'];
                $data['user_name'] = $this->userHelper->shortenUserName($data['user_firstname'], $data['user_lastname'], $data['user_fathername']);
                $data['product_unique_id'] = 'p' . $data['order_item_id'];
                $data['user_unique_id'] = $data['product_unique_id'].'_'.$data['user_id'];
                $data['start'] = $data['start_at'];
                $data['end'] = $data['end_at'];
                $data['cancelled'] = $data['qty_canceled'] > 0;
                unset(
                    $data['username'],
                    $data['user_firstname'],
                    $data['user_lastname'],
                    $data['user_fathername'],
                    $data['start_at'],
                    $data['end_at']
                );
                $result[] = $data;
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }

        return $this->jsonFactory->create()
            ->setData($result);
    }
}
