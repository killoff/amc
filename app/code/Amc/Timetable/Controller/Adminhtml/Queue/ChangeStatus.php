<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\QueueStatus;
use Magento\Framework\Controller\Result\JsonFactory;

class ChangeStatus extends \Magento\Backend\App\Action
{
    /** @var QueueStatus */
    private $queueStatus;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        QueueStatus $queueStatus,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->queueStatus = $queueStatus;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $context = $this->getRequest()->getParam('context');
        $status = $this->getRequest()->getParam('status');
        $changedBy = $this->_auth->getUser()->getId();
        $this->queueStatus->updateStatus($customerId, $context, $status, $changedBy);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( ['result' => 'ok'] );
    }
}

