<?php

namespace Amc\Timetable\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;
use Magento\Framework\Message\Error;

class Validate extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * AJAX ticket validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $resultJson = $this->resultJsonFactory->create();
//        if ($response->getError()) {
//            $response->setError(true);
//            $response->setMessages($response->getMessages());
//        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
