<?php

namespace Amc\Clinic\Block\Adminhtml\Room;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'room_id';
        $this->_controller = 'adminhtml_room';
        $this->_blockGroup = 'Amc_Clinic';

        parent::_construct();
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Consultation Form');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('clinic/room');
    }
}
