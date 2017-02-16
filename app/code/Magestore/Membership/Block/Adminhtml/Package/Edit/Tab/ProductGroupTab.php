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
 * class ProductGroupTab
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class ProductGroupTab extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

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
        $this->setId('package_product_group_grid');
        $this->setDefaultSort('product_group_id');
        $this->setDefaultDir('ASC');
        // $this->setSaveParametersInSession(true);
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
        if ($column->getId() == 'in_groups') {
            $groupIds = $this->_getSelectedGroups();
            if (empty($groupIds)) {
                $groupIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('group_id', ['in' => $groupIds]);
            } else {
                if ($groupIds) {
                    $this->getCollection()->addFieldToFilter('group_id', ['nin' => $groupIds]);
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
        /** @var \Magestore\Membership\Model\ResourceModel\Group\Collection $groupCollection */
        $groupCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Group\Collection');

        if ($packageId = $this->getRequest()->getParam('package_id')) {
            /** @var \Magestore\Membership\Model\ResourceModel\PackageGroup\Collection */
//            $packageGroupCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PackageGroup\Collection');
//            $groupIds = $packageGroupCollection->addFieldToFilter('package_id', $packageId)->getColumnValues('group_id');

            /** @var \Magestore\Membership\Model\ResourceModel\GroupProduct\Collection $collection */
//            $groupCollection->addFieldToFilter('group_id', ['in' => $groupIds]);
            $this->setDefaultFilter(array('in_groups' => 1));
        }

        $this->setCollection($groupCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'in_groups',
            [
                'header' => '',
                'align' => 'center',
                'type' => 'checkbox',
                'name' => 'in_groups',
                'index' => 'group_id',
                'values' => $this->_getSelectedGroups()
            ]
        );

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
                        'url' => ['base' => '*/group/edit'],
                        'field' => 'group_id',
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
     * Get selected groups
     *
     * @return mixed
     */
    protected function _getSelectedGroups()
    {
        $groups = $this->getGroups();
        if (!is_array($groups)) {
            $groups = $this->getSelectedGroups();
        }
        return $groups;
    }

    /**
     * Get all products of the package
     * @return array
     */
    public function getSelectedGroups()
    {
        return $this->_getPackageGroupIds();
    }

    /**
     * @return array
     */
    protected function _getPackageGroupIds()
    {
        if ($packageId = $this->getRequest()->getParam('package_id')) {
            /** @var \Magestore\Membership\Model\ResourceModel\PackageGroup\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PackageGroup\Collection');

            return $collection->addFieldToFilter('package_id', ['eq' => $packageId])->getColumnValues('group_id');
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
            '*/*/groupsGrid',
            ['_current' => true]
        );
    }
}