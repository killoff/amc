<?php
namespace Amc\Protocol\Block\Adminhtml;

class Index extends \Magento\Backend\Block\Widget\Form\Container
{

    protected $_mode = false;

    public function getFormHtml()
    {
        return $this->_layout->createBlock('Amc\Protocol\Block\Adminhtml\Index\Form')->toHtml();
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Save Protocol'));
    }

    /**
     * Return action url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
