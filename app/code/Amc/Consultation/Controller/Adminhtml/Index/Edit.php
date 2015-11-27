<?php

namespace Amc\Consultation\Controller\Adminhtml\Index;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Amc\Consultation\Controller\Adminhtml\Index
{
    /**
     * Consultation edit action
     */
    public function execute()
    {
        try {
            $this->_initProduct();
            $this->_initConsultation();
            $this->_initCustomer();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('An error occurred while editing the customer.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/*/index');
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
