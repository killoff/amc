<?php

namespace Amc\Sales\Model\Plugin\Order;

class ViewAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param \Magento\Sales\Controller\Adminhtml\Order\View $subject
     * @param $resultPage
     * @return mixed
     */
    public function afterExecute(\Magento\Sales\Controller\Adminhtml\Order\View $subject, $resultPage)
    {
        if ($resultPage instanceof \Magento\Backend\Model\View\Result\Page) {
            $order = $this->coreRegistry->registry('current_order');
            $resultPage->getConfig()->getTitle()->prepend(
                sprintf("#%s &mdash; %s", $order->getIncrementId(), $order->getStatusLabel())
            );
            return $resultPage;
        }
    }
}
