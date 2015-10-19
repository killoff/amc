<?php

namespace Amc\User\Block\User\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('User Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('User Info'),
                'title' => __('User Info'),
                'content' => $this->getLayout()->createBlock('Magento\User\Block\User\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'address_section',
            [
                'label' => __('Address Info'),
                'title' => __('Address Info'),
                'content' => $this->getLayout()->createBlock('Amc\User\Block\User\Edit\Tab\Address')->toHtml(),
            ]
        );

        $this->addTab(
            'products_section',
            [
                'label' => __('Allowed Products'),
                'title' => __('Allowed Products'),
                'content' => $this->getLayout()->createBlock(
                    'Amc\User\Block\User\Edit\Tab\Products',
                    'user.products.grid'
                )->toHtml()
                . $this->getLayout()->createBlock(
                        'Amc\User\Block\User\Edit\Tab\Products\Form',
                        'user.products.grid.form'
                    )->toHtml()
            ]
        );

        $this->addTab(
            'roles_section',
            [
                'label' => __('User Role'),
                'title' => __('User Role'),
                'content' => $this->getLayout()->createBlock(
                    'Magento\User\Block\User\Edit\Tab\Roles',
                    'user.roles.grid'
                )->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
