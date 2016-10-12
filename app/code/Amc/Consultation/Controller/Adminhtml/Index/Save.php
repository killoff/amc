<?php
namespace Amc\Consultation\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Amc\Consultation\Model\Consultation\Builder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    /** @var Builder */
    private $consultationBuilder;

    /** @var LoggerInterface */
    private $loggerInterface;

    public function __construct(
        Builder $consultationBuilder,
        LoggerInterface $loggerInterface,
        Action\Context $context
    ) {
        $this->consultationBuilder = $consultationBuilder;
        $this->loggerInterface = $loggerInterface;
        parent::__construct($context);
    }

    /**
     * Consultation save action
     */
    public function execute()
    {
        try {
            $orderItemId = $this->getRequest()->getParam('order_item_id');
            $consultation = $this->consultationBuilder->createConsultation($orderItemId);
            $consultation->setUserId($this->_auth->getUser()->getId());

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
//            print_r($consultation->getData());
//            exit;
            $consultation->save();
            $this->messageManager->addSuccessMessage(__('You have created the consultation.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->getRequest()->getParam('back_url')) {
                $resultRedirect->setPath($this->getRequest()->getParam('back_url'));
            } else {
                $resultRedirect->setPath(
                    'sales/order/view',
                    ['order_id' => $consultation->getOrderId()]
                );
            }
            return $resultRedirect;

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
            $this->loggerInterface->error($e->getMessage());
        }
    }
}
