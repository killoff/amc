<?php
namespace Amc\Consultation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Amc\Consultation\Model\ConsultationFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    /** @var ConsultationFactory */
    private $consultationFactory;

    /** @var LoggerInterface */
    private $loggerInterface;

    public function __construct(
        ConsultationFactory $consultationFactory,
        LoggerInterface $loggerInterface,
        Action\Context $context
    ) {
        $this->consultationFactory = $consultationFactory;
        $this->loggerInterface = $loggerInterface;
        parent::__construct($context);
    }

    /**
     * Consultation save action
     */
    public function execute()
    {
        try {
            $consultation = $this->consultationFactory->create();
            $consultation->setProductId($this->getRequest()->getParam('product_id'));
            $consultation->setCustomerId($this->getRequest()->getParam('customer_id'));
            $consultation->setUserId($this->_auth->getUser()->getId());
            $consultation->setOrderId($this->getRequest()->getParam('order_id'));
            $consultation->setOrderItemId($this->getRequest()->getParam('order_item_id'));
            $createdAt = new \DateTime('now');
            $consultation->setCreatedAt($createdAt->format('Y-m-d H:i:s'));

            $data = $this->getRequest()->getParam('data');
            $jsonData = \Zend_Json::encode($data);
            $consultation->setJsonData($jsonData);

            if (isset($data['user_date'])) {
//                $userDate = \DateTime::createFromFormat('m/d/Y H:i:s', $data['user_date']);
//                   $consultation->setUserDate($userDate->format('Y-m-d H:i:s'));
            } else {
                $consultation->setUserDate(null);
            }
            print_r($consultation->getData());
            exit;
            $consultation->save();
            $this->messageManager->addSuccessMessage(__('You have created the consultation.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->getRequest()->getParam('back_url')) {
                $resultRedirect->setPath($this->getRequest()->getParam('back_url'));
            } else {
                $resultRedirect->setPath(
                    'consultation/index/edit',
                    ['consultation_id' => $consultation->getId(), '_current' => true]
                );
            }
            return $resultRedirect;

        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('An error occurred while saving the consultation.'));
            $returnToEdit = true;
        } catch (\Exception $e) {

            $this->loggerInterface->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $returnToEdit = true;
        }

    }
}
