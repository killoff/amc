<?php

namespace Amc\Clinic\Block\Adminhtml\Room\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_room');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->getCurrentRoom();
        $isEditMode = (null !== $model && $model->getId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $legend = __('New Room');
        if ($isEditMode) {
            $legend = __('Room %1', $model->getName());
            if ($model->hasCode()) {
                $legend .= '#' . $model->getCode();
            }
        }
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => $legend, 'class' => 'fieldset-wide']
        );

        if ($model) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'room_id', 'value' => $model->getId()]);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Room Number'),
                'title' => __('Room Number'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'color',
            'text',
            [
                'name' => 'color',
                'label' => __('Color'),
                'title' => __('Color'),
                'note' => __('You can use any of the CSS color formats such #f00, #ff0000, rgb(255,0,0), or red.'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'wysiwyg' => false,
                'required' => false
            ]
        );

        if (null !== $model) {
            $form->setValues($model->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getCurrentRoom()
    {
        return $this->_coreRegistry->registry('current_room');
    }
}
