<?php

namespace Amc\User\Block\User\Edit\Tab\Products;

use Magento\Backend\Block\Template;

class Form extends Template
{
    /**
     * @var string
     */
    protected $_template = 'user/products/grid/form.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        return $this->_jsonEncoder->encode(
            $this->getUser()->getAssignedProducts()
        );
    }

    /**
     * @return array|null
     */
    public function getUser()
    {
        return $this->_coreRegistry->registry('permissions_user');
    }
}
