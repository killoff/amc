<?php
namespace Amc\Consultation\Model\Consultation;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Amc\Consultation\Model\ConsultationFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Builder
{
    private $customerRepositoryInterface;
    private $productFactory;
    private $orderRepositoryInterface;
    private $orderItemRepositoryInterface;
    private $consultationFactory;

    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        ProductFactory $productFactory,
        OrderRepositoryInterface $orderRepositoryInterface,
        OrderItemRepositoryInterface $orderItemRepositoryInterface,
        ConsultationFactory $consultationFactory
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->productFactory = $productFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->orderItemRepositoryInterface = $orderItemRepositoryInterface;
        $this->consultationFactory = $consultationFactory;
    }

    public function createConsultation($orderItemId)
    {
        $consultation = $this->consultationFactory->create();
        $orderItem = $this->orderItemRepositoryInterface->get($orderItemId);
        $order = $this->orderRepositoryInterface->get($orderItem->getOrderId());
        $customer = $this->customerRepositoryInterface->getById($order->getCustomerId());
        $product = $this->productFactory->create()->load($orderItem->getProductId());
        $consultation->setOrder($order);
        $consultation->setOrderItem($orderItem);
        $consultation->setCustomer($customer);
        $consultation->setProduct($product);
        return $consultation;
    }

    public function loadConsultation($consultationId)
    {
        /** @var \Amc\Consultation\Model\Consultation $consultation */
        $consultation = $this->consultationFactory->create();
        $consultation->load($consultationId);
        if ( ! $consultation->getId()) {
            throw new NoSuchEntityException(__('Consultation not found with id: ' . $consultationId));
        }
        $orderItem = $this->orderItemRepositoryInterface->get($consultation->getOrderItemId());
        $order = $this->orderRepositoryInterface->get($consultation->getOrderId());
        $customer = $this->customerRepositoryInterface->getById($consultation->getCustomerId());
        $product = $this->productFactory->create()->load($consultation->getProductId());
        $consultation->setOrder($order);
        $consultation->setOrderItem($orderItem);
        $consultation->setCustomer($customer);
        $consultation->setProduct($product);
        return $consultation;
    }
}
