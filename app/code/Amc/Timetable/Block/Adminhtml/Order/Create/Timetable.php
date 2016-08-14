<?php
namespace Amc\Timetable\Block\Adminhtml\Order\Create;

class Timetable extends \Amc\Timetable\Block\Adminhtml\Order\Timetable
{
    protected function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    protected function getOrderItems()
    {
        return $this->_layout->getBlock('items_grid')->getItems();
    }

    public function getInitialDate()
    {
        return date('Y-m-d');
    }

    public function getCustomerId()
    {
        return $this->_layout->getBlock('items_grid')->getCustomerId();
    }
}
