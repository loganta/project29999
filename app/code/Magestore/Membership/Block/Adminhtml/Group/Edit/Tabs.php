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

namespace Magestore\Membership\Block\Adminhtml\Group\Edit;

/**
 * Class Tabs
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
        $this->setId('group_tab');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Group Information'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addTab('main_section', 'Magestore\Membership\Block\Adminhtml\Group\Edit\Tab\GroupTab');

        $this->addTab(
            'products_tab',
            [
                'label' => 'Product Information',
                'title' => 'Product Information',
                'url' => $this->getUrl('*/*/products', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return $this;
    }
}