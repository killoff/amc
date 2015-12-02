<?php
namespace Amc\Protocol\Block\Adminhtml\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->getCurrentObject();
        $isEditMode = (null !== $model && $model->getId());

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $legend = $isEditMode ? __('Protocol %1', $model->getName()) : __('New Protocol');
        $fieldSet = $form->addFieldset(
            'base_fieldset',
            ['legend' => $legend, 'class' => 'fieldset-wide']
        );

        $fieldSet->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Protocol Name'),
                'title' => __('Protocol Name'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'text',
            'editor',
            [
                'name' => 'text',
                'label' => __('Metadata'),
                'title' => __('Metadata'),
                'required' => true,
                'state' => 'html',
                'style' => 'height:36em;',
            ]
        );

//        $fieldSet->addType('protocol', 'Amc\Protocol\Block\Adminhtml\Renderer');
//
//        $fieldSet->addField(
//            'dialog',
//            'protocol',
//            [
//                'name' => 'dialog',
//                'label' => __(''),
//                'title' => __(''),
//                'required' => false,
//            ]
//        );
        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getCurrentObject()
    {
        return $this->_coreRegistry->registry('current_protocol');
    }
}
