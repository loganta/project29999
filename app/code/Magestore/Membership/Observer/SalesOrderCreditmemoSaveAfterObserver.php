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
use Magestore\Membership\Model\PaymentStatus;

/**
 * class SalesOrderCreditmemoSaveAfterObserver
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{

    /**
     * associative array ['productId' => 'packageId']
     * @var array
     */
    protected $_packageProduct = [];

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magestore\Membership\Model\PaymentHistory
     */
    protected $_paymentHistory;

    /**
     * SalesOrderCreditmemoSaveAfterObserver constructor.
     * @param \Magestore\Membership\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magestore\Membership\Model\PaymentHistory $paymentHistory
     */
    public function __construct(
        \Magestore\Membership\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magestore\Membership\Model\PaymentHistory $paymentHistory
    )
    {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_paymentHistory = $paymentHistory;
        $this->_setPackageProduct();
    }


    /**
     * Add new member or apply discount for member
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        if (!$customerId = $creditmemo->getOrder()->getCustomerId()) {
            return $this;
        }

        if (!$memberId = $this->_getMember($customerId)) {
            return $this;
        }

        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($creditmemo->getAllItems() as $item) {



            if (in_array($item->getProductId(), array_keys($this->_packageProduct))) {
                // product refunded is a membership package
                $packageId = $this->_packageProduct[$item->getProductId()];
//                $packageProduct = $this->_objectManager->create('Magestore\Membership\Model\PackageProduct')->load($packageProductId);
//                $packageId = $packageProduct->getPackageId();
                /** @var \Magestore\Membership\Model\Package $package */
                $package = $this->_objectManager->create('Magestore\Membership\Model\Package')->load($packageId);

                $memberPackage = $this->_helper->getMemberPackage($memberId, $packageId);

                if ($memberPackage->getBoughtItemTotal()) {
                    // used membership package
                    $this->_messageManager->addError(__('Cannot refund a membership product if there is any other relevant purchase made with a discounted price under the same membership level!'));
                    throw new \Exception(__('Cannot refund a membership product if there is any other relevant purchase made with a discounted price under the same membership level!'));
                } else {
                    // this membership package has never been used
                    try {
                        $endTime = date('Y-m-d H:i:s', strtotime($memberPackage->getEndTime() . '-' . $package->getDuration() . ' ' . $package->getTimeUnit() . 's'));
                        if ($endTime == $memberPackage->getStartTime()) {
                            $memberPackage->delete();
                            $this->_updatePaymentHistory($creditmemo->getOrderId(), $item->getProductId());
                        } else {
                            $memberPackage->setEndTime($endTime);
                            $memberPackage->updateStatus();
                            $memberPackage->save();
                            $this->_updatePaymentHistory($creditmemo->getOrderId(), $item->getProductId());
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }else{
                $memberPackageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection')->addFieldToFilter('order_ids',$item->getOrderItemId());
                if (count($memberPackageCollection)) {
                    $memberPackageCollection = $memberPackageCollection->getFirstItem();
                }
                $boughtItemTotal = $memberPackageCollection->getBoughtItemTotal();
                $savedTotal = $memberPackageCollection->getSavedTotal();
                $boughtItemTotal = $boughtItemTotal - 1;
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                $baseProductPrice = $product->getData('price');
                $savedAmount = ($baseProductPrice - $item->getBasePrice()) ;

                $memberPackageCollection->setData('bought_item_total',$boughtItemTotal);
                $memberPackageCollection->setData('saved_total',$savedTotal - $savedAmount);
                $memberPackageCollection->save();

            }
        }

        return $this;
    }

    /**
     * @param $customerId
     * @return int|null memberId
     */
    protected function _getMember($customerId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        $collection->addFieldToFilter('customer_id', $customerId);
        if (count($collection)) {
            /** @var \Magestore\Membership\Model\Member $member */
            $member = $collection->getFirstItem();
            return $member->getId();
        }
        return null;
    }

    /**
     * @return $this
     */
    protected function _setPackageProduct()
    {
        // get all packages' productIds as an associative array ['productId' => 'packageId']
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packageCollection */
        $packageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        /** @var \Magestore\Membership\Model\Package $item */
        foreach ($packageCollection as $item) {
            $this->_packageProduct[$item->getProductId()] = $item->getId();
        }

        return $this;
    }

    /**
     * update payment history after refunding a package
     */
    protected function _updatePaymentHistory($orderId, $packageProductId)
    {
        $payments = $this->_paymentHistory->getCollection()->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('package_product_id', $packageProductId)
            ->addFieldToFilter('status', PaymentStatus::STATUS_PAID);
        if (count($payments)) {
            /** @var \Magestore\Membership\Model\PaymentHistory $payment */
            $payment = $payments->getFirstItem();
            $payment->setData('status', PaymentStatus::STATUS_REFUNDED)->save();
        }
        return;
    }
}