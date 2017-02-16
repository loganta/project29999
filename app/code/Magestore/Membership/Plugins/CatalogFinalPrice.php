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

namespace Magestore\Membership\Plugins;

/**
 * class CatalogFinalPrice
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class CatalogFinalPrice
{

    /**
     * All available packages which the customer bought
     * @var array
     */
    protected $_packages = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    protected $_objectManager = null;

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_configurableType;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * CatalogFinalPrice constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Membership\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Membership\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    )
    {
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->_configurableType = $configurable;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * @param \Magento\Catalog\Pricing\Price\FinalPrice $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetValue(
        \Magento\Catalog\Pricing\Price\FinalPrice $subject,
        \Closure $proceed
    )
    {

        if (!$customerId = $this->_customerSession->getCustomerId()) {
            return $proceed();
        }

        if (!$member = $this->_getMember($customerId)) {
            return $proceed();
        }

        if ($member->isEnable()) {
            $this->_packages = $member->getPackages();
            $productId = $subject->getProduct()->getId();
            $packages = $this->_getPackages($productId);

            if (!count($packages)) {
                return $proceed();
            }

            $finalPrices = [];

            /** @var \Magestore\Membership\Model\Package $package */
            foreach ($packages as $package) {
                $finalPrices[] = $this->_helper->getMembershipPrice($productId, $package);
            }

            return $this->_priceHelper->currency($this->_getBestPrice($finalPrices), false, false);
        }
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
                        $parentId = $this->_configurableType->getParentIdsByChild($productId)[0];
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
            return floatval(max(0, $prices[0]));
        }
    }
}