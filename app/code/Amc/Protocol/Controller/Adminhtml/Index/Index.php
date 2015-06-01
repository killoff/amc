<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
//        $hypertext = $this->_objectManager->create('Amc\Protocol\Model\Hypertext');
//        $rows = $hypertext->parseHypertext(file_get_contents('/vagrant/dev/fixtures/protocol.txt'));
//        print_r($rows);

        $this->_view->loadLayout();
        $this->_setActiveMenu('Amc_Protocol');
        $this->_view->renderLayout();
    }
}
