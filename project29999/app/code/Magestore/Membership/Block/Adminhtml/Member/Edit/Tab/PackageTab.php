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

namespace Magestore\Membership\Block\Adminhtml\Member\Edit\Tab;

use Magestore\Membership\Model\Status;
use Magestore\Membership\Model\MemberPackageStatus;
use Magestore\Membership\Setup\InstallSchema;

/**
 * class PackageTab
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class PackageTab extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * PackageTab constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Membership\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Membership\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }


    /**
     * grid construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('member_packages_grid');
        $this->setDefaultSort('package_id');
        $this->setDefaultDir('ASC');
        // edit membership member, sort packages by member_package's end_time
        if ($this->getRequest()->getParam('member_id')) {
            $this->setDefaultSort('end_time')->setDefaultDir('DESC');
        }
        $this->setUseAjax(true);
    }

    /**
     * Add column filter to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_packages') {
            $packageIds = $this->_getSelectedPackages();
            if (empty($packageIds)) {
                $packageIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('package_id', ['in' => $packageIds]);
            } else {
                if ($packageIds) {
                    $this->getCollection()->addFieldToFilter('package_id', ['nin' => $packageIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packageCollection */
        $packageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');

        if ($memberId = $this->getRequest()->getParam('member_id')) {
            $packageCollection->getSelect()->joinLeft(
                ['mp' => InstallSchema::TABLE_MEMBERSHIP_MEMBER_PACKAGE],
                'main_table.package_id=mp.package_id and mp.member_id=' . $memberId,
                [
                    'end_time',
                    'bought_item_total',
                    'saved_total',
                    'status'
                ]
            );
        }

        $this->setCollection($packageCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_packages',
            [
                'header' => '',
                'align' => 'center',
                'type' => 'checkbox',
                'name' => 'in_packages',
                'index' => 'package_id',
                'values' => $this->_getSelectedPackages()
            ]
        );

        $this->addColumn(
            'package_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'package_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'type' => 'text',
                'index' => 'package_name',
            ]
        );

        $this->addColumn(
            'package_price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'index' => 'package_price',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
            ]
        );

        // in case of adding new membership member, hide these fields
        if ($this->getRequest()->getParam('member_id')) {
            $this->addColumn(
                'mp_status',
                [
                    'header' => __('Membership Status'),
                    'type' => 'options',
                    'index' => 'status',
                    'options' => MemberPackageStatus::getStatus(),

                ]
            );

            $this->addColumn(
                'end_time',
                [
                    'header' => __('End Time'),
                    'type' => 'datetime',
                    'index' => 'end_time'
                ]
            );

            $this->addColumn(
                'bought_item_number',
                [
                    'header' => __('Purchased Items'),
                    'type' => 'number',
                    'index' => 'bought_item_total'
                ]
            );

            $this->addColumn(
                'saved_total',
                [
                    'header' => __('Saved Total'),
                    'type' => 'price',
                    'index' => 'saved_total',
                    'currency_code' => (string)$this->_scopeConfig->getValue(
                        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                ]
            );
        }
        // end hiding some fields in grid when add a new membership member

        $this->addColumn(
            'package_status',
            [
                'header' => __('Package Status'),
                'index' => 'package_status',
                'type' => 'options',
                'options' => Status::getStatus()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get selected packages
     *
     * @return mixed
     */
    protected function _getSelectedPackages()
    {
        $packageIds = $this->getPackages();
        if (!is_array($packageIds)) {
            $packageIds = $this->getSelectedPackages();
        }
        return $packageIds;
    }

    /**
     * Get all products of the package
     * @return array
     */
    public function getSelectedPackages()
    {
         return $this->_getMemberPackagesIds();
//        return [];
    }

    /**
     * get packages that the member has joined
     * @return array
     */
    protected function _getMemberPackagesIds()
    {
        if ($memberId = $this->getRequest()->getParam('member_id')) {
            /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');

            return $collection->addFieldToFilter('member_id', ['eq' => $memberId])->getColumnValues('package_id');
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->_getData(
            'grid_url'
        ) ? $this->_getData(
            'grid_url'
        ) : $this->getUrl(
            '*/*/packagesGrid',
            ['_current' => true]
        );
    }
}