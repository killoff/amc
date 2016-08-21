<?php

namespace Amc\Consultation\Controller\Adminhtml\Index;

use Magento\Framework\Exception\NoSuchEntityException;

class Create extends \Amc\Consultation\Controller\Adminhtml\Index
{
    /**
     * Consultation edit action
     */
    public function execute()
    {
        try {
            $orderId = $this->_request->getParam('order_id');
            $order = $this->initializeOrder($this->_request->getParam('order_id'));
            $this->_coreRegistry->register('current_order', $order);
            $this->_coreRegistry->register('current_customer', $this->_customerRepository->getById($order->getCustomerId()));

            $currentUser = $this->_authSession->getUser();

            // product already selected
            $productId = $this->getRequest()->getParam('product_id');
            if ($productId && $this->_userProductLink->isProductAssignedToUser($currentUser->getId(), $productId)) {
                $this->_coreRegistry->register('current_product', $this->_productFactory->create()->load($productId));
                return $this->resultPageFactory->create();
            }


            $orderProductIds = $order->getItemsCollection()->getColumnValues('product_id');
//            foreach ($order->getItems() as $item) {
//                $orderProductIds[] = $item->getProductId();
//            }
//
            if (false && $this->isCurrentUserAnAdmin()) {
                if (count($orderProductIds) == 1) {
                    return $this->_redirect('consultation/index/create');
                } else {
                    $this->_coreRegistry->register('consultation_product_ids', $orderProductIds);
                    return $this->resultPageFactory->create();
                }
            }

            $userProductIds = $this->_userProductLink->getUserProducts($this->_authSession->getUser());
            $intersection = array_intersect($userProductIds, $orderProductIds);
            if (empty($intersection)) {
                $this->messageManager->addWarningMessage(__('You are not allowed to create a consultation for this order.'));
                return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
                // redirect back to order
            }
            if (count($intersection) == 1) {
                return $this->_redirect('consultation/index/create');
            } else {
                $this->_coreRegistry->register('consultation_product_ids', $intersection);
                return $this->resultPageFactory->create();
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('An error occurred while editing the customer.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/*/index');
            return $resultRedirect;
        }
    }

    private function initializeOrder($orderId)
    {
        $order = $this->_orderRepository->get($orderId);if ( ! $order->getId()) {
            throw new NoSuchEntityException(__('Requested order doesn\'t exist'));
        }
        return $order;
    }

    private function redirectToCreate($orderId, $productId)
    {
        return $this->_redirect('consultation/index/edit', ['order_id' => $orderId, 'product_id' => $productId]);
    }


    private function isCurrentUserAnAdmin()
    {
        return $this->_authorization->isAllowed('Magento_Backend::all');
    }
}
