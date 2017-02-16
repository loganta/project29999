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

namespace Magestore\Membership\Observer;

/**
 * class AbstractMembershipPriceObserver
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
abstract class AbstractMembershipPriceObserver
{

    /**
     * All available packages which the customer bought
     * @var array
     */
    protected $_packages = [];

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * AbstractMembershipPriceObserver constructor.
     * @param \Magestore\Membership\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     */
    public function __construct(
        \Magestore\Membership\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
    )
    {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_configurableType = $configurable;
    }


    /**
     * @param $customerId
     * @return \Magestore\Membership\Model\Member|null
     */
    protected function _getMember($customerId)
    {
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        $collection->addFieldToFilter('customer_id', $customerId);
        if (count($collection)) {
            /** @var \Magestore\Membership\Model\Member $member */
            $member = $collection->getFirstItem();
            return $member;
        }
        return null;
    }

    /**
     * get all packages which member registered for a product
     * @param $productId
     * @return array
     */
    protected function _getPackages($productId)
    {
        $packages = [];
        if (count($this->_packages)) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            /** @var \Magestore\Membership\Model\Package $package */
            foreach ($this->_packages as $package) {
                if ($package->isEnabled()) {
                    if ($product->isVisibleInSiteVisibility()) {
                        if (in_array($productId, $package->getAllProductIds())) {
                            $packages[] = $package;
                        }
                    } else {
                        $parentId = $this->_configurableType->getParentIdsByChild($productId);
                        if (isset($parentId[0])){
                            $parentId = $productId[0];
                        }
                        if (in_array($parentId, $package->getAllProductIds())) {
                            $packages[] = $package;
                        }
                    }
                }
            }
        }
        return $packages;
    }


    /**
     * get the best price from a list of price
     * @param $prices
     * @return mixed
     */
    protected function _getBestPrice($prices)
    {
        if (is_array($prices)) {
            sort($prices, SORT_NUMERIC);
            return max(0, $prices[0]);
        }
    }
}