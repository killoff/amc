<?php
namespace Amc\Consultation\Controller\Adminhtml\Index;

use Amc\Consultation\Model\Consultation\Builder;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Create extends Action
{
    protected $consultationBuilder;
    protected $authSession;
    protected $registry;
    protected $pageFactory;
    protected $logger;

    public function __construct(
        Builder $consultationBuilder,
        Session $authSession,
        Registry $registry,
        LoggerInterface $loggerInterface,
        PageFactory $pageFactory,
        Action\Context $context
    ) {
        $this->consultationBuilder = $consultationBuilder;
        $this->authSession = $authSession;
        $this->registry = $registry;
        $this->pageFactory = $pageFactory;
        $this->logger = $loggerInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $orderItemId = $this->_request->getParam('order_item_id');
        if ( ! $orderItemId) {
            throw new NoSuchEntityException(__('Order item ID is required to create a consultation.'));
        }
        try {
            $consultation = $this->consultationBuilder->createConsultation($orderItemId);
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

    protected function throwExceptionIfUserNotAllowed($userId, $productId)
    {
        return false;
        if ( ! $this->_userProductLink->isProductAssignedToUser($userId, $productId)) {
            throw new \Exception(__('You are not allowed to create consultation for this product.'));
        }
    }
}
