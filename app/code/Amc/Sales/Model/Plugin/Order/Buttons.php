<?php

namespace Amc\Sales\Model\Plugin\Order;

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;

class Buttons
{
    /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
            return [$context, $buttonList];
        }
//        $buttonList->remove('order_invoice');
//        $buttonList->remove('order_creditmemo');
        $buttonList->remove('void_payment');
        $buttonList->remove('order_hold');
        $buttonList->remove('order_unhold');
        $buttonList->remove('accept_payment');
        $buttonList->remove('deny_payment');
        $buttonList->remove('get_review_payment_update');
        $buttonList->remove('order_ship');
        $buttonList->remove('send_notification');
//        $buttonList->add('order_review',
//            [
//                'label' => __('Review'),
//                'onclick' => 'setLocation(\'' . $context->getUrl('sales/*/review') . '\')',
//                'class' => 'review'
//            ]
//        );
//
//        $buttonList->remove('order_hold');
//        $buttonList->remove('send_notification');
//
        return [$context, $buttonList];
    }
}
