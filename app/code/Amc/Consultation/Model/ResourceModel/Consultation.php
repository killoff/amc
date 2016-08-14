<?php

namespace Amc\Consultation\Model\ResourceModel;

class Consultation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Amc\Consultation\Model\Consultation\Validator
     */
    protected $validator;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Amc\Consultation\Model\Consultation\Validator $validator
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amc\Consultation\Model\Consultation\Validator $validator,
        $resourcePrefix = null
    ) {
        $this->validator = $validator;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amc_consultation_entity', 'entity_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
