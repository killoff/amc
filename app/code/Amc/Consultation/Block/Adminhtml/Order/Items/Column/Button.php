<?php
namespace Amc\Consultation\Block\Adminhtml\Order\Items\Column;

class Button extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Amc\User\Model\UserProductLink
     */
    private $userProductRelation;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Amc\User\Model\UserProductLink $userProductRelation,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
        $this->authSession = $authSession;
        $this->userProductRelation = $userProductRelation;
    }

    public function getCreateUrl()
    {
        return $this->getUrl('consultation/index/create',
            [
                'order_id' => $this->getItem()->getOrderId(),
                'product_id' => $this->getItem()->getProductId()
            ]
        );
    }

    protected function _toHtml()
    {
        if ($this->isAllowed()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    private function isAllowed()
    {
        return $this->userProductRelation->isProductAssignedToUser(
            $this->authSession->getUser()->getId(),
            $this->getItem()->getProductId()
        );
    }
}
