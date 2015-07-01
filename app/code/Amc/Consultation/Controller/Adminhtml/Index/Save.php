<?php

namespace Amc\Consultation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends \Amc\Consultation\Controller\Adminhtml\Index
{
    /**
     * Consultation save action
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $consultationId = $this->getRequest()->getParam('consultation_id');
        $returnToEdit = false;
        $editMode = false;

        try {
            $consultation = $this->consultationFactory->create();

            if ($consultationId) {
                $consultation->load($consultationId);
                $editMode = true;
            }

            $consultation->addData($this->getRequest()->getParams());
            $consultation->setUserId($this->_authSession->getUser()->getId());
            $consultation->save();

            $editMode
                ? $this->messageManager->addSuccess(__('You have updated the consultation.'))
                : $this->messageManager->addSuccess(__('You have created the consultation.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('An error occurred while saving the consultation.'));
            $returnToEdit = true;
        } catch (\Exception $e) {
            $this->loggerInterface->critical($e);
            $this->messageManager->addError($e->getMessage());
            $returnToEdit = true;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($consultationId) {
                $resultRedirect->setPath(
                    'consultation/*/edit',
                    ['consultation_id' => $consultationId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'consultation/*/edit',
                    ['customer_id' => $customerId, '_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
        }
        return $resultRedirect;
    }
}
