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

namespace Magestore\Membership\Block\Adminhtml\PaymentHistory;

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
        $this->setId('payment_history_grid');
        $this->setDefaultSort('payment_history_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\PaymentHistory\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PaymentHistory\Collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'payment_history_id',
            [
                'header' => __('ID'),
                'index' => 'payment_history_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'member_id',
            [
                'header' => __('Member ID'),
                'index' => 'member_id',
                'type' => 'number'
            ]
        );

        $this->addColumn(
            'package_name',
            [
                'header' => __('Package Name'),
                'index' => 'package_name',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'renderer' => 'Magestore\Membership\Block\Adminhtml\PaymentHistory\Renderer\Price',
                'align' => 'right',
            ]
        );


        $this->addColumn(
            'duration',
            [
                'header' => __('Duration'),
                'index' => 'duration',
                'type' => 'number',
                'align' => 'center'
            ]
        );


        $this->addColumn(
            'time_unit',
            [
                'header' => __('Time Unit'),
                'index' => 'time_unit',
                'type' => 'options',
                'options' => \Magestore\Membership\Model\DurationUnit::getTimeUnits(),
            ]
        );

        $this->addColumn(
            'start_time',
            [
                'header' => __('Start Time'),
                'index' => 'start_time',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'end_time',
            [
                'header' => __('End Time'),
                'index' => 'end_time',
                'type' => 'datetime',
            ]
        );


        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getMemberId',
                'actions' => [
                    [
                        'caption' => __('View Member'),
                        'url' => ['base' => '*/member/edit'],
                        'field' => 'member_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
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
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
//    public function getRowUrl($row)
//    {
//        return $this->getUrl('*/member/edit', ['_current' => true, 'member_id' => $row->getData('member_id')]);
//    }
}