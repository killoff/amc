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
            if ($this->getRequest()->getParam('user_date')) {
                $userDate = \DateTime::createFromFormat('d/m/Y', $this->getRequest()->getParam('user_date'));
                $consultation->setUserDate($userDate->format('Y-m-d H:i:s'));
            } else {
                $consultation->setUserDate(null);
            }
            $createdAt = new \DateTime('now');
            $consultation->setCreatedAt($createdAt->format('Y-m-d H:i:s'));
            $consultation->setUserId($this->_authSession->getUser()->getId());
            $order = $this->_orderRepository->get($this->getRequest()->getParam('order_id'));
            $consultation->setCustomerId($order->getCustomerId());
            $consultation->save();

            $editMode
                ? $this->messageManager->addSuccess(__('You have updated the consultation.'))
                : $this->messageManager->addSuccess(__('You have created the consultation.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            if ($returnToEdit && $consultationId) {
                $resultRedirect->setPath(
                    'consultation/*/edit',
                    ['consultation_id' => $consultationId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getEntityId()]);
            }
            return $resultRedirect;

        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('An error occurred while saving the consultation.'));
            $returnToEdit = true;
        } catch (\Exception $e) {
            $this->loggerInterface->critical($e);
            $this->messageManager->addError($e->getMessage());
            $returnToEdit = true;
        }

    }
}
