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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Membership\Model\PaymentStatus;

/**
 * class SalesInvoiceSaveAfterObserver
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class SalesInvoiceSaveAfterObserver implements ObserverInterface
{

    /**
     * new memberId
     * @var int
     */
    protected $_newMemberId = null;

    /**
     * new member flag
     * @var bool
     */
    protected $_isNewMember = false;

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
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;


    /**
     * SalesInvoiceSaveAfterObserver constructor.
     * @param \Magestore\Membership\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magestore\Membership\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    )
    {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();

        if (!$customerId = $invoice->getOrder()->getCustomerId()) {
            // guest's purchase
            return $this;
        }

        if ($invoice->getState() != 2) {
            // invoice state is not 'Paid'
            return $this;
        }

        /** @var \Magestore\Membership\Model\Member $member */
        $member = $this->_helper->getMemberByCustomerId($customerId);
        if ($member->getId()) {
            $this->_packages = $member->getPackages();
        } else {
            $this->_isNewMember = true;
        }

        // get all packages' productIds as an associative array ['productId' => 'packageId']
        $packageProductIds = [];
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packageCollection */
        $packageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        /** @var \Magestore\Membership\Model\Package $item */
        foreach ($packageCollection as $item) {
            $packageProductIds[$item->getProductId()] = $item->getId();
        }

        // calculate data for activating membership member
        // if (this customer isn't a member yet) and (he ordered at least a membership package) then add him as a member
        /** @var \Magento\Sales\Model\Order\Item $invoiceItem */
        if (!$member->getId()) {
            foreach ($invoice->getAllItems() as $invoiceItem) {
                if (in_array($invoiceItem->getProductId(), array_keys($packageProductIds))) {
                    $this->_newMemberId = $this->_helper->saveMember($customerId);
                    break;
                }
            }
        }

        /** @var \Magento\Sales\Model\Order\Invoice\Item $invoiceItem */
        foreach ($invoice->getAllItems() as $invoiceItem) {
            $productId = $invoiceItem->getProductId();

            if (in_array($productId, array_keys($packageProductIds))) {
                // the product is a package
                $packageId = $packageProductIds[$productId];

                if ($this->_isNewMember) {
                    if ($this->_newMemberId) {
                        $this->_helper->addPackageToMember($this->_newMemberId, $packageId, $invoice->getOrderId(), $invoiceItem->getQty());
                        $this->_helper->addPaymentHistory(
                            $this->_newMemberId,
                            $packageId,
                            $invoice->getOrderId(),
                            $invoiceItem->getQty(),
                            PaymentStatus::STATUS_PAID
                        );
                    }
                } else {
                    // customer is already a member
                    $this->_helper->addPackageToMember($member->getId(), $packageId, $invoice->getOrderId(), $invoiceItem->getQty());
                    $this->_helper->addPaymentHistory(
                        $member->getId(),
                        $packageId,
                        $invoice->getOrderId(),
                        $invoiceItem->getQty(),
                        PaymentStatus::STATUS_PAID
                    );
                }

            } else {
                // the product is a regular product
                if (!$member->getId()) {
                    continue;
                }

                if (!$member->isEnable()) {
                    continue;
                }

                $packages = $this->_getPackages($productId);

                if (!count($packages)) {
                    continue;
                }

                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                $baseProductPrice = $product->getData('price');

                $purchaseOnPackageId = 0;
                /** @var \Magestore\Membership\Model\Package $package */
                foreach ($packages as $package) {
                    // convert to displayed currency
                    $membershipPrice = $this->_helper->getMembershipPrice($productId, $package);
                    if ($membershipPrice == $invoiceItem->getBasePrice()) {
                        $purchaseOnPackageId = $package->getId();
                        break;
                    }
                }

                if (!$purchaseOnPackageId) {
                    return $this;
                }

                /** @var \Magestore\Membership\Model\MemberPackage $memberPackage */
                $memberPackage = $this->_getMemberPackage($member->getId(), $purchaseOnPackageId);
                $savedAmount = ($baseProductPrice - $invoiceItem->getBasePrice()) * $invoiceItem->getQty();

                if (strtotime('now') <= strtotime($memberPackage->getData('end_time'))){
                    $data = [
                        'bought_item_total' => $memberPackage->getBoughtItemTotal() + $invoiceItem->getQty(),
                        'saved_total' => $memberPackage->getSavedTotal() + $savedAmount,
                        'status' => 1
                    ];

                    $memberPackage->addData($data);
                    //Gin
                    $memberPackage->setData('order_ids',$invoiceItem->getOrderItemId())  ;
                    //End
                    $memberPackage->save();
                }
            }
        }
        return $this;
    }

    /**
     * @param $memberId
     * @param \Magestore\Membership\Model\Package $package
     * @return \Magento\Framework\DataObject|null
     */
    protected function _getMemberPackage($memberId, $packageId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $packageMemberCollection */
        $packageMemberCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $packageMemberCollection->addFieldToFilter('member_id', $memberId)->addFieldToFilter('package_id', $packageId);
        if (count($packageMemberCollection)) {
            return $packageMemberCollection->getFirstItem();
        }
        return null;
    }

    /**
     * get all packages which member registered (by productId)
     * @param $productId
     * @return array
     */
    protected function _getPackages($productId)
    {
        $packages = [];
        if (!$this->_isNewMember && count($this->_packages)) {
            /** @var \Magestore\Membership\Model\Package $package */
            foreach ($this->_packages as $package) {
                if (in_array($productId, $package->getAllProductIds())) {
                    $packages[] = $package;
                }
            }
        }
        return $packages;
    }
}