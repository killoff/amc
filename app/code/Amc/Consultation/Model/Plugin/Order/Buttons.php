<?php

namespace Amc\Consultation\Model\Plugin\Order;

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
        $buttonList->add('order_create_consultation',
            [
                'label' => __('Consultation'),
                'onclick' => 'setLocation(\'' . $context->getUrl('consultation/index/create') . '\')',
                'class' => 'review'
            ]
        );

        return [$context, $buttonList];
    }
}
