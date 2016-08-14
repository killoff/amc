<?php

namespace Amc\Consultation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Amc\Consultation\Controller\Adminhtml\Index
{
    /**
     * Consultation delete action
     */
    public function execute()
    {
        $this->messageManager->addError('Not Implemented');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            'consultation/*/edit',
            ['consultation_id' => $this->getRequest()->getParam('consultation_id'), '_current' => true]
        );

        return $resultRedirect;
    }
}
