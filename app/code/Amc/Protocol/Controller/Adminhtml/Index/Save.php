<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;

class Save extends \Magento\Backend\App\Action
{
    private $hypertext;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amc\Protocol\Model\HypertextFactory $hypertext
    ) {
        parent::__construct($context);
        $this->hypertext = $hypertext;
    }


    public function execute()
    {
        $protocol = $this->hypertext->create();
        $protocol->setName($this->_request->getParam('name'));
        $protocol->save();
        $protocol->saveProtocol($this->_request->getParam('text'));
    }
}
