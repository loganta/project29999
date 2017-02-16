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

namespace Magestore\Membership\Block\Adminhtml\Package;

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
        $this->setId('package_grid');
        $this->setDefaultSort('package_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'package_id',
            [
                'header' => __('ID'),
                'index' => 'package_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'package_name',
            [
                'header' => __('Name'),
                'index' => 'package_name',
                'type' => 'text',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'url_key',
            [
                'header' => __('Url Key'),
                'index' => 'url_key',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'package_price',
            [
                'header' => __('Package Price'),
                'align' => 'right',
                'index' => 'package_price',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'type' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );

        $this->addColumn(
            'discount_value',
            [
                'header' => __('Discount Value'),
                'align' => 'right',
                'index' => 'package_product_price',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'discount_type',
            [
                'header' => __('Discount Type'),
                'index' => 'discount_type',
                'type' => 'options',
                'options' => \Magestore\Membership\Model\DiscountType::getDiscountType(),
                'header_css_class' => 'col-discount-type',
                'column_css_class' => 'col-discount-type',
            ]
        );

        $this->addColumn(
            'duration',
            [
                'header' => __('Duration'),
                'index' => 'duration',
                'type' => 'number',
                'header_css_class' => 'col-duration',
                'column_css_class' => 'col-duration',
            ]
        );

        $this->addColumn(
            'time_unit',
            [
                'header' => __('Time Unit'),
                'index' => 'time_unit',
                'type' => 'options',
                'options' => \Magestore\Membership\Model\DurationUnit::getTimeUnits(),
                'header_css_class' => 'col-time-unit',
                'column_css_class' => 'col-time-unit',
            ]
        );

        $this->addColumn(
            'is_featured',
            [
                'header' => __('Featured Package'),
                'index' => 'is_featured',
                'type' => 'options',
                'options' => \Magestore\Membership\Model\FeaturedOptions::getOptions(),
            ]
        );

        $this->addColumn(
            'sort_order',
            [
                'header' => __('Sort Order'),
                'index' => 'sort_order',
                'type' => 'number',
                'header_css_class' => 'col-sort-order',
                'column_css_class' => 'col-sort-order',
            ]
        );

        $this->addColumn(
            'package_status',
            [
                'header' => __('Status'),
                'index' => 'package_status',
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
                        'field' => 'package_id',
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
        $this->setMassactionIdField('package_id');

        /**
         * $_POST || $_GET
         */
        $this->getMassactionBlock()->setFormFieldName('packages');

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

        $this->getMassactionBlock()->addItem(
            'promoted',
            [
                'label' => __('Featured'),
                'url' => $this->getUrl('membershipadmin/*/massFeatured', ['_current' => true])
            ]
        );

        $this->getMassactionBlock()->addItem(
            'not-promoted',
            [
                'label' => __('Not Featured'),
                'url' => $this->getUrl('membershipadmin/*/massNotFeatured', ['_current' => true])
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
        return $this->getUrl('*/*/edit', ['_current' => true, 'package_id' => $row->getId()]);
    }
}