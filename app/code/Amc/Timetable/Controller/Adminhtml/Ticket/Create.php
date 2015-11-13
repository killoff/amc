<?php

namespace Amc\Timetable\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action;

class Create extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return ResultInterface
     */
    protected function execute()
    {
        return $this->resultPageFactory->create();
    }
}
