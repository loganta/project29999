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

namespace Magestore\Membership\Block\Adminhtml\Member;

use Magento\Framework\ObjectManagerInterface;

/**
 * class Grid
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('member_grid');
        $this->setDefaultSort('member_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
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
            'member_status',
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
                'is_system' => true,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportExcel', __('Excel'));
        $this->addExportType('*/*/exportXml', __('Xml'));

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('member_id');

        /**
         * $_POST || $_GET
         */
        $this->getMassactionBlock()->setFormFieldName('member');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('membershipadmin/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]
        );

        $this->getMassactionBlock()->addItem(
            'enable',
            [
                'label' => __('Enable'),
                'url' => $this->getUrl('membershipadmin/*/massEnable', ['_current' => true])
            ]
        );

        $this->getMassactionBlock()->addItem(
            'disable',
            [
                'label' => __('Disable'),
                'url' => $this->getUrl('membershipadmin/*/massDisable', ['_current' => true])
            ]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['member_id' => $row->getId()]);
    }
}