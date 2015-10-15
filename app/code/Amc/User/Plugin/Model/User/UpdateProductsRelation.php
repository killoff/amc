<?php

namespace Amc\User\Plugin\Model\User;

use Magento\User\Controller\Adminhtml\User\Save;

class UpdateProductsRelation
{
    /**
     * @param Save $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(
        Save $subject,
        \Closure $proceed
    ) {
        $qwerty = $subject->getRequest()->getParams();
        var_dump($qwerty); yyy();

        return $proceed();
    }
}
