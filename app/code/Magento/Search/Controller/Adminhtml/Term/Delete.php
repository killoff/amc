<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

class Delete extends \Magento\Search\Controller\Adminhtml\Term
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirectResult = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('Magento\Search\Model\Query');
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the search.'));
                return $redirectResult->setPath('search/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $redirectResult->setPath('search/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a search term to delete.'));
        return $redirectResult->setPath('search/*/');
    }
}
