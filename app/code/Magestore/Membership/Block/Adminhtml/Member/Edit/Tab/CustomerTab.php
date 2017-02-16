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

/**
 * class CustomerTab
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class CustomerTab extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Magestore\Membership\Model\MemberFactory
     */
    protected $_memberFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * CustomerTab constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magestore\Membership\Model\MemberFactory $memberFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magestore\Membership\Model\MemberFactory $memberFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_groupFactory = $groupFactory;
        $this->_memberFactory = $memberFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * grid construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ms_customer_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('customer_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->_customerFactory->create()->getCollection()->addNameToSelect();

        // this array also contains current customerId in case of editing a membership member
        $joinedCustomers = $this->_memberFactory->create()->getCollection()->getColumnValues('customer_id');

        if ($memberId = $this->getRequest()->getParam('member_id')) {
            // case of editing membership member
            $currentCustomerId = $this->_objectManager->create('Magestore\Membership\Model\Member')->load($memberId)->getCustomerId();
            $joinedCustomers = $this->_removeElementByValue($joinedCustomers, $currentCustomerId);
            if (count($joinedCustomers)) {
                $collection->addFieldToFilter('entity_id', ['nin' => $joinedCustomers]);
            }
        } else {
            // case of adding new membership member
            // filter out customers that have already been membership member
            if (count($joinedCustomers)) {
                $collection->addFieldToFilter('entity_id', ['nin' => $joinedCustomers]);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer_select',
            [
                'header' => __(''),
                'align' => 'center',
                'type' => 'radio',
                'renderer' => 'Magestore\Membership\Block\Adminhtml\Member\Edit\Tab\RadioButton'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Customer Id'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Customer Name'),
                'type' => 'text',
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );


        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'type' => 'text',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );


        $this->addColumn(
            'group_id',
            [
                'header' => __('Group'),
                'sortable' => false,
                'index' => 'group_id',
                'type' => 'options',
                'options' => $this->_groupFactory->create()->getCollection()->toOptionHash(),
                'header_css_class' => 'col-group',
                'column_css_class' => 'col-group'
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header' => __('Website'),
                    'sortable' => false,
                    'index' => 'website_id',
                    'type' => 'options',
                    'options' => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites'
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customers', ['_current' => true, 'member_id' => $this->getRequest()->getParam('member_id')]);
    }

    /**
     * remove elements from an array by its value
     * @param $array
     * @param $value
     * @return mixed
     */
    protected function _removeElementByValue($array, $value)
    {
        foreach ($array as $key => $val) {
            if ($val == $value) {
                unset($array[$key]);
            }
        }
        return $array;
    }
}