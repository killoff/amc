<?php

namespace Amc\User\Plugin\Model\User;

use Magento\User\Model\User;

class UpdateProductsRelation
{
    /**
     * @var \Amc\User\Model\UserProductLink
     */
    protected $relationManager;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @param \Amc\User\Model\UserProductLink $relationManager
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        \Amc\User\Model\UserProductLink $relationManager,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->relationManager = $relationManager;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @param User $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundSave(
        User $subject,
        \Closure $proceed
    ) {
        $return = $proceed();

        $productIds = [];

        if ((null !== $subject->getData('user_products')) && is_string($subject->getData('user_products'))) {
            $productIds = $this->jsonDecoder->decode($subject->getData('user_products'));
        }

        $this->relationManager->addRelation($subject, $productIds);

        return $return;
    }
}
