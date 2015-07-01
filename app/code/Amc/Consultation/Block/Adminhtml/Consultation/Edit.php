<?php

namespace Amc\Consultation\Block\Adminhtml\Consultation;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'consultation_id';
        $this->_controller = 'adminhtml_consultation';
        $this->_blockGroup = 'Amc_Consultation';

        parent::_construct();
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Consultation Form');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $this->_coreRegistry->registry('current_customer');
        if ($customer) {
            return $this->getUrl('customer/*/edit', ['id' => $customer->getId()]);
        }
        return $this->getUrl('customer/*/*');
    }
}
