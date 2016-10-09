<?php
namespace Amc\Consultation\Controller\Adminhtml\Index;

class Edit extends \Amc\Consultation\Controller\Adminhtml\Index\Create
{
    /**
     * Consultation save action
     */
    public function execute()
    {
        try {
            $consultationId = $this->getRequest()->getParam('consultation_id');
            $consultation = $this->consultationBuilder->loadConsultation($consultationId);
            $this->registry->register('current_consultation', $consultation);

            $currentUser = $this->authSession->getUser();
            $this->throwExceptionIfUserNotAllowed($currentUser->getId(), $consultation->getProduct()->getId());

            return $this->pageFactory->create();

        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Entity not found.'));
            if ($this->getRequest()->getParam('order_id')) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('An error occurred while creating the consultation.'));
            if ($this->getRequest()->getParam('order_id')) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
                return $resultRedirect;
            }
        }
    }
}
