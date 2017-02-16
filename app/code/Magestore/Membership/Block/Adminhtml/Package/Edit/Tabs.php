<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Membership
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Membership\Block\Adminhtml\Package\Edit;

/**
 * class Tabs
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('package_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Package Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addTab('main_section', 'Magestore\Membership\Block\Adminhtml\Package\Edit\Tab\PackageTab');

        $this->addTab(
            'product_groups_tab',
            [
                'label' => 'Manage Product Groups',
                'title' => 'Manage Product Groups',
                'url' => $this->getUrl('*/*/groups', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'products_tab',
            [
                'label' => 'Manage Products',
                'title' => 'Manage Products',
                'url' => $this->getUrl('*/*/products', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        if ($this->getRequest()->getParam('package_id')) {
            $this->addTab(
                'members_tab',
                [
                    'label' => 'Members',
                    'title' => 'Members',
                    'url' => $this->getUrl('*/*/members', ['_current' => true]),
                    'class' => 'ajax'
                ]
            );
        }

        return $this;
    }
}