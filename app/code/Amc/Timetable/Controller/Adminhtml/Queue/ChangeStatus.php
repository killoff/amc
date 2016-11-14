<?php
namespace Amc\Timetable\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Amc\Timetable\Model\ResourceModel\CustomerStatus;
use Magento\Framework\Controller\Result\JsonFactory;

class ChangeStatus extends \Magento\Backend\App\Action
{
    /** @var CustomerStatus */
    private $customerStatus;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $resultJsonFactory;

    public function __construct(
        CustomerStatus $customerStatus,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->customerStatus = $customerStatus;
        $this->resultJsonFactory = $resultJsonFactory;
    }


    /**
     * Chooser Source action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $status = $this->getRequest()->getParam('status');
        $this->customerStatus->updateStatus($customerId, $status);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData( ['result' => 'ok'] );
    }
}

