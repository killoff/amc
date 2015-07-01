<?php

namespace Amc\Consultation\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\RegistryConstants;

class Consultation extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->_initCustomer();
        return $this->resultLayoutFactory->create();
    }
}
