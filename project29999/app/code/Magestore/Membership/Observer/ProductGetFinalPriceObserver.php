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

use Magento\Framework\Event\ObserverInterface;

/**
 * class ProductGetFinalPriceObserver
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class ProductGetFinalPriceObserver extends AbstractMembershipPriceObserver implements ObserverInterface
{

    /**
     * @param \Magento\Framework\Event\Observer Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if (!$customerId = $this->_customerSession->getCustomerId()) {
            return $this;
        }

        if (!$member = $this->_getMember($customerId)) {
            return $this;
        }

        if ($member->isEnable()) {
            $this->_packages = $member->getPackages();
            $finalPrices = [];

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getEvent()->getProduct();

            $packages = $this->_getPackages($product->getEntityId());

            if (!count($packages)) {
                return $this;
            }

            /** @var \Magestore\Membership\Model\Package $package */
            foreach ($packages as $package) {
                $finalPrices[] = $this->_helper->getMembershipPrice($product->getId(), $package);
            }

            $product->setFinalPrice($this->_getBestPrice($finalPrices));
        }
    }
}