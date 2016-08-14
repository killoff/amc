<?php

namespace Amc\Timetable\Model\Plugin\Order\Create;

class Search
{

    /**
     * Get buttons html
     *
     * @return string
     */
    public function afterGetButtonsHtml($subject, $result)
    {
        $addButtonData = [
            'label' => __('Add Selected Product(s) to Order'),
            'onclick' => 'console.log(order.gridProducts.toObject());order.productGridAddSelected()',
            'class' => 'action-add action-secondary',
        ];
        return $subject->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            $addButtonData
        )->toHtml();
    }
}
