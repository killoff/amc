<?php

namespace Amc\Timetable\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * @return void
     */
    public function execute()
    {
        var_dump($this->getRequest()->getParams());
        echo __CLASS__; die;
    }
}
