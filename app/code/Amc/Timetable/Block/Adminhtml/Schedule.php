<?php
namespace Amc\Timetable\Block\Adminhtml;

class Schedule extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('timetable_index');
    }

    public function getInitialDate()
    {
        return date('Y-m-d');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            []
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'created_at',
            'date',
            [
                'name' => 'created_at',
                'label' => __('Date'),
                'id' => 'created_at',
                'title' => __('Date'),
                'date_format' => 'dd.mm.yyyy',
//                'disabled' => $isEditMode
//                'value' => $this->_localeDate->date()->format(\IntlDateFormatter::SHORT)
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
