<?php

namespace Amc\Timetable\Controller\Adminhtml\Order\View;

use Magento\Backend\App\Action;

class ResourcesJson extends Action
{
    /** @var \Amc\Timetable\Model\OrderTimetable */
    private $orderTimetable;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Amc\Timetable\Model\OrderTimetable $orderTimetable,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->orderTimetable = $orderTimetable;
        $this->orderFactory = $orderFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $order = $this->orderFactory->create()->load($this->_request->getParam('order_id'));
            $aggregated = $this->orderTimetable->getAggregated($order, '2016-01-01 12:00:00', '2017-01-01 12:00:00');
            $response = [];
            foreach ($aggregated['products'] as $product) {
                $resource = [
                    'id'            => sprintf('i%s', $product['sales_item_id']),
                    'product_id'    => $product['id'],
                    'sales_item_id' => $product['sales_item_id'],
                    'title'         => $product['name'],
                    'type'          => 'item',
                    'duration'      => '15', // @todo
                ];
                $productUsers = array_filter($aggregated['users'], function($user) use($product) {
                    return $user['product_id'] == $product['id'];
                });
                foreach ($productUsers as $user) {
                    $resource['children'][] = [
                        'id'            => sprintf('i%s_u%s', $product['sales_item_id'], $user['id']),
                        'product_id'    => $product['id'],
                        'sales_item_id' => $product['sales_item_id'],
                        'user_id'       => $user['id'],
                        'title'         => $user['name'],
                        'type'          => 'user',
                    ];
                }

                $response[] = $resource;
            }
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
