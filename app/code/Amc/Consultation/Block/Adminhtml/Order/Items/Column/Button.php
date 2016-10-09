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

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Amc\User\Model\UserProductLink $userProductRelation,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
        $this->authSession = $authSession;
        $this->userProductRelation = $userProductRelation;
        $this->productFactory = $productFactory;
    }

    public function getCreateUrl()
    {
        return $this->getUrl('consultation/index/create',
            [
                'order_id' => $this->getItem()->getOrderId(),
                'order_item_id' => $this->getItem()->getId()
            ]
        );
    }

    public function isAllowed()
    {
        return $this->userProductRelation->isProductAssignedToUser(
            $this->authSession->getUser()->getId(),
            $this->getItem()->getProductId()
        );
    }

    public function isLayoutAssigned()
    {
        $product = $this->productFactory->create();
        $product->load($this->getItem()->getProductId());
        return (bool)$product->getData('consultation_layout');
    }
}
