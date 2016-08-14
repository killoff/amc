<?php
namespace Amc\Timetable\Block\Adminhtml\Order\Tab;

class Timetable extends \Amc\Timetable\Block\Adminhtml\Order\Timetable
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    protected function getOrderItems()
    {
        return $this->getOrder()->getAllVisibleItems();
    }

    public function getCustomerId()
    {
        return $this->getOrder()->getCustomerId();
    }

    public function getInitialDate()
    {
        return $this->getData('initial_date') ? $this->getData('initial_date') : $this->getOrder()->getCreatedAt();
    }

    public function getSaveTimetableUrl()
    {
        return $this->getUrl('timetable/order/save', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Timetable');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Timetable');
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
