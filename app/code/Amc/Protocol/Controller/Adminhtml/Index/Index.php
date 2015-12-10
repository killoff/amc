<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Amc_Protocol');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Protocols'));
        $this->_view->renderLayout();
    }
}
