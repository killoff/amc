<?php

namespace Amc\Consultation\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ConsultationButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->authorization = $context->getAuthorization();
        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        if ($customerId) { //TODO: && $this->authorization->isAllowed('Amc_Consultation::create')   ACL !
            $data = [
                'label' => __('Create Consultation'),
                'on_click' => 'setLocation(\'' . $this->getCreateConsultationUrl() . '\')',
                'class' => 'add',
                'sort_order' => 40,
            ];
        }
        return $data;
    }

    /**
     * Retrieve the Url for creating an consultation.
     *
     * @return string
     */
    public function getCreateConsultationUrl()
    {
//        return $this->getUrl('consultation/index/edit', ['customer_id' => $this->getCustomerId()]);
        return $this->getUrl('consultation/index/create', ['customer_id' => $this->getCustomerId()]);
    }
}
