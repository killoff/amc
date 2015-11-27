<?php

namespace Amc\Consultation\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Index
 */
abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Amc\Consultation\Model\ConsultationFactory
     */
    protected $consultationFactory;

    /**
     * Authentication
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerInterface;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Amc\Consultation\Model\Consultation
     */
    protected $_consultation;


    protected $_productFactory;


    /**
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Amc\Consultation\Model\ConsultationFactory $consultationFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amc\Consultation\Model\ConsultationFactory $consultationFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $loggerInterface,
        Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->consultationFactory = $consultationFactory;
        $this->loggerInterface = $loggerInterface;
        $this->_authSession = $authSession;
        $this->_coreRegistry = $registry;
        $this->_customerRepository = $customerRepository;
        $this->_productFactory = $productFactory;
    }

    /**
     * @return $this|\Amc\Consultation\Model\Consultation
     */
    protected function _initConsultation()
    {
        if (null === $this->_consultation) {
            $id = $this->getRequest()->getParam('consultation_id');
            if ($id) {
                $model = $this->consultationFactory->create()->load($id);
                if ($model->getId()) {
                    $this->_consultation = $model;
                    $this->_coreRegistry->register('current_consultation', $model);
                }
            }
        }
        return $this->_consultation;
    }

    protected function _initProduct()
    {
        $id = $this->getRequest()->getParam('product_id');
        if ($id) {
            $model = $this->_productFactory->create()->load($id);
            if ($model->getId()) {
                $this->_coreRegistry->register('current_product', $model);
            }
        }
    }

    protected function _initCustomer()
    {
        $customerId = 0;
        $consultation = $this->_initConsultation();
        if (null !== $consultation) {
            $customerId = $consultation->getCustomerId();
        } elseif ($this->getRequest()->getParam('customer_id')) {
            $customerId = $this->getRequest()->getParam('customer_id');
        }
        $customer = $this->_customerRepository->getById($customerId);
        $this->_coreRegistry->register('current_customer', $customer);
    }

    /**
     * Customer access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true; //$this->_authorization->isAllowed('Amc_Consultation::manage');
    }
}
