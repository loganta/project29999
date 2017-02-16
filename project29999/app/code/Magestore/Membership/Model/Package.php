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

namespace Magestore\Membership\Model;

use Magestore\Membership\Setup\InstallSchema as Schema;

/**
 * class Package
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Package extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @string
     */
    const URL_REWRITE_TYPE = 'ms-package';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Package constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_urlBuilder = $urlInterface;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Membership\Model\ResourceModel\Package');
    }

    /**
     * get package's productIds
     * @return array
     */
    public function getProductIds()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\PackageProduct\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PackageProduct\Collection');
        return $collection->addFieldToFilter('package_id', $this->getId())->getColumnValues('product_id');
    }

    /**
     * get productIds from available groups of package
     * @return array
     */
    public function getProductIdsFromGroups()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Group\Collection $groups */
        $groups = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Group\Collection');
        $groups->addFieldToFilter('group_id', ['in' => $this->getGroupIds()])
            ->addFieldToFilter('group_status', Status::STATUS_ENABLED);

        /** @var \Magestore\Membership\Model\ResourceModel\GroupProduct\Collection $groupProducts */
        $groupProducts = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\GroupProduct\Collection');
        $groupProducts->addFieldToFilter('group_id', ['in' => $groups->getColumnValues('group_id')]);
        return $groupProducts->getColumnValues('product_id');
    }

    /**
     * Get all groupIds of package
     * @return array
     */
    public function getGroupIds()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\PackageGroup\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PackageGroup\Collection');
        $collection->addFieldToFilter('package_id', $this->getId());
        return $collection->getColumnValues('group_id');
    }

    /**
     * get all available productIds of package (also productIds from available groups)
     * @return array
     */
    public function getAllProductIds()
    {
        return array_unique(array_merge($this->getProductIds(), $this->getProductIdsFromGroups()));
    }

    public function getAllPackageProductIds()
    {
        $childrenIds = [];
        /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType */
        $configurableType = $this->_objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        foreach ($this->getAllProductIds() as $id) {
            if (in_array($id, $this->_getConfigurableProductIds())) {
                // product is configurable then add its children ids
                $childrenIds = array_merge($configurableType->getChildrenIds($id)[0]);
            }
        }
        return array_merge($this->getAllProductIds(), $childrenIds);
    }

    /**
     * get all ids of products which are configurable
     * @return array
     */
    protected function _getConfigurableProductIds()
    {
        return $this->_productFactory->create()->getCollection()->addAttributeToFilter('type_id', 'configurable')
            ->getColumnValues('entity_id');
    }

    public function delete()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        // delete product which simulates the membership package
        $product->setId($this->getData('product_id'))->delete();
        return parent::delete();
    }

    /**
     * Get time unit label (for display in front-end)
     * @return \Magento\Framework\Phrase|string
     */
    public function getUnitTimeLabel()
    {
        switch ($this->getTimeUnit()) {
            case DurationUnit::DAY:
                $label = __('day(s)');
                break;
            case DurationUnit::WEEK:
                $label = __('week(s)');
                break;
            case DurationUnit::MONTH:
                $label = __('month(s)');
                break;
            case DurationUnit::YEAR:
                $label = __('year(s)');
                break;
            default:
                $label = '';
                break;
        }
        return $label;
    }

    /**
     * [ true => enable, false => disabled ]
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getPackageStatus() == Status::STATUS_ENABLED;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $result = parent::afterSave();

        /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
        $urlRewrite = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');

        $urls = $urlRewrite->getCollection()
            ->addFieldToFilter('entity_type', self::URL_REWRITE_TYPE)
            ->addFieldToFilter('entity_id', $this->getId());

        try {
            if (count($urls)) {
                /** @var \Magento\UrlRewrite\Model\UrlRewrite $url */
                foreach ($urls as $url) {
                    $url->setRequestPath('membership/' . $this->getUrlKey() . '.html');
                    $url->save();
                }
            } else {
                if ($this->getUrlKey()) {
                    $stores = $this->_storeManager->getStores();
                    /** @var \Magento\Store\Api\Data\StoreInterface $store */
                    foreach ($stores as $store) {
                        $urlRewrite = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite');
                        $urlRewrite->setStoreId($store->getId())
                            ->setEntityType(self::URL_REWRITE_TYPE)
                            ->setEntityId($this->getId())
                            ->setRequestPath('membership/' . $this->getUrlKey() . '.html')
                            ->setTargetPath('membership/package/view/id/' . $this->getId())
                            ->setRedirectType(0)
                            ->setIsAutogenerated(1);
                        $urlRewrite->save();
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('There has been an error saving url rewrite!');
        }

        return $result;
    }

    /**
     * get package view detail url
     * @return string
     */
    public function getViewUrl()
    {
        if ($urlKey = $this->getUrlKey()) {
            return $this->_urlBuilder->getUrl('membership/' . $urlKey . '.html', []);
        }
        return $this->_urlBuilder->getUrl('membership/package/view', ['id' => $this->getId()]);
    }
}