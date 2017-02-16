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

namespace Magestore\Membership\Block\Adminhtml\Group\Edit\Tab;

use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class ProductTab
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class ProductTab extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

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
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magestore\Membership\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_productFactory = $productFactory;
        $this->_objectManager = $objectManager;
        $this->_status = $status;
        $this->_type = $type;
        $this->_setsFactory = $setsFactory;
        $this->_visibility = $visibility;
        $this->_helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * grid construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        // $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
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
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
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
        $membershipAttributeSetId = $this->_helper->getMembershipAttributeSetId();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->_productFactory->create()->getCollection();
        $collection->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('attribute_set_id', ['neq' => $membershipAttributeSetId])
            ->addFieldToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE])
            ->addAttributeToSelect('*');

        if ($this->getRequest()->getParam('group_id')) {
//            ($groupId = $this->getRequest()->getParam('group_id');
            /** @var \Magestore\Membership\Model\ResourceModel\GroupProduct\Collection $groupProductCollection */
//            $groupProductCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\GroupProduct\Collection');
//            $productIds = $groupProductCollection->addFieldToFilter('group_id', $groupId)->getColumnValues('product_id');
//
//            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $this->setDefaultFilter(array('in_products' => 1));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'header' => '',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'product_entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx'
            ]
        );

        $sets = $this->_setsFactory->create()->setEntityTypeFilter(
            $this->_productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'product_attribute_set_name',
            [
                'header' => __('Attribute Set'),
                'type' => 'options',
                'index' => 'attribute_set_id',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );


        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray()
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->_visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'index' => 'price',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getProducts();
        if (!is_array($products)) {
            $products = $this->getSelectedProducts();
        }
        return $products;
    }

    /**
     * Get all products of the package
     * @return array
     */
    public function getSelectedProducts()
    {
        return $this->_getGroupProductIds();
    }

    /**
     * @return array
     */
    protected function _getGroupProductIds()
    {
        if ($groupId = $this->getRequest()->getParam('group_id')) {
            /** @var \Magestore\Membership\Model\ResourceModel\GroupProduct\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\GroupProduct\Collection');

            return $collection->addFieldToFilter('group_id', ['eq' => $groupId])->getColumnValues('product_id');
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
            '*/*/productsGrid',
            ['_current' => true]
        );
    }
}