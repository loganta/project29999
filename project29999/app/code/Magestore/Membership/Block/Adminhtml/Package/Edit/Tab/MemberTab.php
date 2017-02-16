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

namespace Magestore\Membership\Block\Adminhtml\Package\Edit\Tab;

/**
 * class MemberTab
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MemberTab extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * grid construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('package_member_grid');
        $this->setDefaultSort('member_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        if ($packageId = $this->getRequest()->getParam('package_id')) {
            /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection */
            $memberPackageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
            $memberIds = $memberPackageCollection->addFieldToFilter('package_id', $packageId)->getColumnValues('member_id');

            /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
            $collection->addFieldToFilter('member_id', ['in' => $memberIds]);
            $this->setCollection($collection);
        }

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'member_id',
            [
                'header' => __('ID'),
                'index' => 'member_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'type' => 'text',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'type' => 'text',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email',
            ]
        );

        $this->addColumn(
            'joined_time',
            [
                'header' => __('Joined Date'),
                'index' => 'joined_time',
                'type' => 'datetime',
                'header_css_class' => 'col-joined-time',
                'column_css_class' => 'col-joined-time',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'member_status',
                'type' => 'options',
                'options' => \Magestore\Membership\Model\Status::getStatus(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getCustomerId',
                'actions' => [
                    [
                        'caption' => __('View customer'),
                        'url' => ['base' => 'customer/index/edit'],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/members', ['_current' => true]);
    }
}