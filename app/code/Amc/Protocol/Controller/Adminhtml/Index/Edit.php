<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

class Edit extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Amc_Protocol');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Add New Protocol'));
        $this->_view->renderLayout();
    }
}
