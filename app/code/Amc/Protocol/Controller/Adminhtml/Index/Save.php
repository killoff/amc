<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

class Save extends \Magento\Backend\App\Action
{
    private $protocol;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amc\Protocol\Model\ProtocolFactory $protocol
    ) {
        parent::__construct($context);
        $this->protocol = $protocol;
    }


    public function execute()
    {
        /** @var \Amc\Protocol\Model\Protocol $protocol */
        $protocol = $this->protocol->create();
        $protocol->setName($this->_request->getParam('name'));
        $protocol->save();
        $protocol->saveHypertext($this->_request->getParam('text'));
    }
}
