<?php
namespace Amc\Protocol\Block\Adminhtml;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected function _construct()
    {
        $this->_objectId = 'protocol_id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Amc_Protocol';
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
//    public function getSaveUrl()
//    {
//        return $this->getUrl('*/*/save');
//    }
}
