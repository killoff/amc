<?php

namespace Amc\User\Controller\Adminhtml\User\Products;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->userFactory = $userFactory;
    }

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
//        $category = $this->_initCategory(true);
//        if (!$category) {
//            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
//            $resultRedirect = $this->resultRedirectFactory->create();
//            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
//        }
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        /** @var \Amc\User\Block\User\Edit\Tab\Products $block */
        $block = $this->layoutFactory->create()->createBlock(
            'Amc\User\Block\User\Edit\Tab\Products',
            'user.products.grid'
        );
        if ($this->_request->getParam('user_id')) {
            $user = $this->userFactory->create()->load($this->_request->getParam('user_id'));
            if ($user->getId()) {
                $block->setUser($user);
            }
        }

        return $resultRaw->setContents($block->toHtml());
    }
}
