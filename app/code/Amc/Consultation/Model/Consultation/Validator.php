<?php

namespace Amc\Consultation\Model\Consultation;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;
use Zend_Validate_Exception;

class Validator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var NotEmpty
     */
    private $notEmpty;

    /**
     * @param NotEmptyFactory $notEmptyFactory
     */
    public function __construct(NotEmptyFactory $notEmptyFactory)
    {
        $this->notEmpty = $notEmptyFactory->create(['options' => NotEmpty::ALL]);
    }

    /**
     * @param \Amc\Consultation\Model\Consultation $value
     * @return bool
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        $this->validateRequiredFields($value);

        return !$this->hasMessages();
    }

    /**
     * @param \Amc\Consultation\Model\Consultation $value
     * @return void
     * @throws Zend_Validate_Exception
     * @throws \Exception
     */
    protected function validateRequiredFields($value)
    {
        if ($value->getId()) {
            return;
        }

        $messages = [];
        $requiredFields = [
            'product_id'    => $value->getProductId(),
            'customer_id'   => $value->getCustomerId(),
            'user_id'       => $value->getUserId(),
            'order_id'      => $value->getOrderId(),
            'order_item_id' => $value->getOrderItemId(),
            'created_at'    => $value->getCreatedAt()
        ];
        foreach ($requiredFields as $requiredField => $requiredValue) {
            if (!$this->notEmpty->isValid(trim($requiredValue))) {
                $messages[$requiredField] = __(InputException::REQUIRED_FIELD, ['fieldName' => $requiredField]);
            }
        }
        $this->_addMessages($messages);
    }
}
