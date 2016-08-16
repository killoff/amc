<?php
namespace Amc\Consultation\Block\Adminhtml\Order\View\Tab;

/**
 * Order Invoices grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Consultation extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Consultations');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Consultations');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
