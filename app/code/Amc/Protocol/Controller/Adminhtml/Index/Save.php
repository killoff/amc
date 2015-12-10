<?php
namespace Amc\Protocol\Controller\Adminhtml\Index;

use Symfony\Component\Config\Definition\Exception\Exception;

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
        try {
            /** @var \Amc\Protocol\Model\Protocol $protocol */
            $protocol = $this->protocol->create();
            $protocol->setName($this->_request->getParam('name'));
            $protocol->save();
            $protocol->saveHypertext($this->_request->getParam('text'));
            $protocol->saveProducts(explode(',', $this->_request->getParam('product_ids')));
            $protocol->saveHypertextRows($this->_request->getParam('text'));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
