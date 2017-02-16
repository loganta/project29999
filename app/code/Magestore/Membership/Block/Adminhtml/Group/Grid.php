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

namespace Magestore\Membership\Block\Adminhtml\Group;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Grid
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
        $this->setId('group_grid'); // continue editing
        $this->setDefaultSort('group_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Group\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Group\Collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'group_id',
            [
                'header' => __('ID'),
                'index' => 'group_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'group_name',
            [
                'header' => __('Name'),
                'index' => 'group_name',
                'type' => 'text',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'description',
            [
                'header' => __('Description'),
                'index' => 'description',
                'type' => 'text',
                'header_css_class' => 'col-description',
                'column_css_class' => 'col-description',
            ]
        );

        $this->addColumn(
            'group_status',
            [
                'header' => __('Status'),
                'index' => 'group_status',
                'type' => 'options',
                'options' => \Magestore\Membership\Model\Status::getStatus(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => ['base' => '*/*/edit'],
                        'field' => 'group_id',
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
        $this->setMassactionIdField('group_id');

        /**
         * $_POST || $_GET
         */
        $this->getMassactionBlock()->setFormFieldName('group');

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
        return $this->getUrl('*/*/edit', ['_current' => true, 'group_id' => $row->getId()]);
    }
}