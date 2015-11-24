<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

class Load extends \Magento\Backend\App\Action
{
    public function execute()
    {
        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Amc\Protocol\Block\Adminhtml\Protocol',
                'amc.protocol.ui'
            )->setIndex(
                $this->getRequest()->getParam('index')
            )->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return true;
    }
}
