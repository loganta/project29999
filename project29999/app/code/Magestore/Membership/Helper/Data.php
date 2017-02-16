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

namespace Magestore\Membership\Helper;

use Magento\Catalog\Model\Product\Type;
use Magestore\Membership\Model\DiscountType;
use Magestore\Membership\Model\MemberPackageStatus as MembershipStatus;
use Magestore\Membership\Model\Status;

/**
 * class Data
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * string
     */
    CONST MEMBERSHIP_ATTRIBUTE_SET_NAME = 'Membership';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Email
     */
    protected $_emailHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param Email $emailHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\Membership\Helper\Email $emailHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime
    )
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_productFactory = $productFactory;
        $this->_emailHelper = $emailHelper;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_dateTime = $dateTime;
    }

    public function saveMembershipProduct($name, $description, $price, $status, $productId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_productFactory->create();

        if ($productId) {
            $product->load($productId);
        } else {
            $product->setAttributeSetId($this->getMembershipAttributeSetId());
            $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $product->setTypeId(Type::TYPE_VIRTUAL);
            $product->setSku('ms_' . $name . '_' . $price);
            $product->setData('_edit_mode', true);
            $product->setWebsiteIds([1]);

            $product->setTaxClassId(0);
            $product->setCreatedAt(date('Y-m-d H:i:s'));
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $product->setStoreId($storeId)
            ->setPrice($price)
            ->setName($name)
            ->setData('description', $description)
            ->setStockData(['qty' => 99999, 'is_in_stock' => 1])
            ->setStatus($status);

        try {
            $product->save();
            return $product->getId();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getMembershipProductIds()
    {

    }

    public function getMembershipAttributeSetId()
    {
        /** @var \Magento\Eav\Model\Entity\Type $entityType */
        $entityType = $this->_objectManager->create('Magento\Eav\Model\Entity\Type');
        $entityTypeId = $entityType->loadByCode('catalog_product')->getId();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->_objectManager->create('Magento\Catalog\Setup\CategorySetup');
        $defaultSetId = $categorySetup->getAttributeSetId($entityTypeId, 'Default');

        /** @var \Magento\Eav\Model\Entity\Attribute\Set $model */
        $model = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set');
        $model->load(self::MEMBERSHIP_ATTRIBUTE_SET_NAME, 'attribute_set_name')->setEntityTypeId($entityTypeId);

        if (!$model->getAttributeSetId()) {
            $model->setAttributeSetName(self::MEMBERSHIP_ATTRIBUTE_SET_NAME);
            try {
                $model->save();

                $model = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set')->load($model->getId());
                $model->initFromSkeleton($defaultSetId)->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return (int)$model->getAttributeSetId();
    }

    /**
     * @param $groupId
     * @param $productIds
     */
    public function assignProductsToGroup($groupId, $productIds)
    {
        /** @var \Magestore\Membership\Model\GroupProduct $productGroupModel */
        $productGroupModel = $this->_objectManager->create('Magestore\Membership\Model\GroupProduct');
        $productGroupModel->setData('group_id', $groupId);

        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                $productGroupModel->setData('product_id', $productId);
                $productGroupModel->save();
                $productGroupModel->setId(null);
            }
        }
    }

    public function reassignProductsToGroup($groupId, $productIds)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\GroupProduct\Collection $groupCollection */
        $groupCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\GroupProduct\Collection');
        $groupCollection->addFieldToFilter('group_id', $groupId)
            ->addFieldToFilter('product_id', ['nin' => $productIds]);
        $groupCollection->delete();
        $this->assignProductsToGroup($groupId,$productIds);
    }

    /**
     * add or update packages to a member
     * @param $memberId
     * @param array $packageIds
     */
    public function assignPackagesToMember($memberId, $packageIds)
    {
//        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $mpCollection */
        $mpCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection')
            ->addFieldToFilter('member_id', $memberId);

        $currentPackageIds = [];
        // ['mp_entityId' => 'packageId', ...]
        foreach ($mpCollection as $mp) {
            $currentPackageIds[$mp->getId()] = $mp->getPackageId();
        }

        // ['mp_entityId' => 'packageId', ...]
        $toRemovePackageIds = array_diff($currentPackageIds, $packageIds);
        // array of packageIds
        $toAddPackageIds = array_diff($packageIds, $currentPackageIds);

        /** @var \Magestore\Membership\Model\MemberPackage $memberPackgeModel */
        $memberPackgeModel = $this->_objectManager->create('Magestore\Membership\Model\MemberPackage');
        foreach ($toRemovePackageIds as $mpId => $packageId) {
            $memberPackgeModel->setId($mpId)->delete();
        }

        foreach ($toAddPackageIds as $packageId) {
            $memberPackgeModel->setId(null)->setData('member_id', $memberId)->setData('package_id', $packageId)->save();
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        $moduleName = $requestInterface->getModuleName();
        $controller = $requestInterface->getControllerName();
        $action     = $requestInterface->getActionName();


        foreach ($packageIds as $packageId) {
            // if ($moduleName!= 'membershipadmin' && $controller != 'member' &&  $action != 'save'){
            $this->addPackageToMember($memberId, $packageId, null);
            // }
        }

    }

    public function assignGroupsToPackage($packageId, $groupIds)
    {
        /** @var \Magestore\Membership\Model\PackageGroup $packageGroupModel */
        $packageGroupModel = $this->_objectManager->create('Magestore\Membership\Model\PackageGroup');
        $packageGroupModel->setData('package_id', $packageId);
        if (is_array($groupIds)) {
            foreach ($groupIds as $groupId) {
                $packageGroupModel->setData('group_id', $groupId);
                $packageGroupModel->save();
                $packageGroupModel->setId(null);
            }
        }
    }

    public function reassignGroupsToPackage($packageId, $groupIds)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\PackageGroup\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PackageGroup\Collection');
        $collection->addFieldToFilter('package_id', $packageId)
            ->addFieldToFilter('group_id', ['nin' => $groupIds]);
        $collection->delete();
        $this->assignGroupsToPackage($packageId,$groupIds);
    }

    public function assignProductsToPackage($packageId, $productIds)
    {
        /** @var \Magestore\Membership\Model\PackageProduct $packageProductModel */
        $packageProductModel = $this->_objectManager->create('Magestore\Membership\Model\PackageProduct');
        $packageProductModel->setData('package_id', $packageId);
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                $packageProductModel->setData('product_id', $productId);
                $packageProductModel->save();
                $packageProductModel->setId(null);
            }
        }
    }

    public function reassignProductsToPackage($packageId, $productIds)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\PackageProduct\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PackageProduct\Collection');
        $collection->addFieldToFilter('package_id', $packageId)
            ->addFieldToFilter('product_id', ['nin' => $productIds]);
        $collection->delete();
        $this->assignProductsToPackage($packageId,$productIds);
    }

    /**
     * check if a customer is available for membership
     * @param int $customerId
     * @return bool
     */
    public function isMemberEnabled($customerId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        $collection->addFieldToFilter('customer_id', $customerId);
        if (count($collection)) {
            /** @var \Magestore\Membership\Model\Member $member */
            $member = $collection->getFirstItem();
            return $member->isEnable();
        }
        return true;
    }


    /**
     * @param $productId
     * @return \Magestore\Membership\Model\Package
     */
    public function getPackageFromProductId($productId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        /** @var \Magestore\Membership\Model\Package $package */
        $package = $collection->addFieldToFilter('product_id', $productId)->getFirstItem();

        return $package;
    }

    /**
     * @param $customerId
     * @return mixed|null
     */
    public function checkIfCustomerJoinedMembership($customerId)
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
     * @param $customerId
     * @return mixed|null
     */
    public function saveMember($customerId)
    {
        $memberId = $this->checkIfCustomerJoinedMembership($customerId);
        if ($memberId) {
            return $memberId;
        } else {
            // add new member
            /** @var \Magestore\Membership\Model\Member $member */
            $member = $this->_objectManager->create('Magestore\Membership\Model\Member');

            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $customer->load($customerId);
            $memberData = [
                'customer_id' => $customer->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'joined_time' => $this->_dateTime->formatDate($this->_date->gmtTimestamp())
            ];
            $member->setData($memberData);
            try {
                $member->save();
            } catch (\Exception $e) {
                return null;
            }
            return $member->getId();
        }
    }

    public function addPackageToMember($memberId, $packageId, $orderId, $qty = 1)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        $moduleName = $requestInterface->getModuleName();
        $controller = $requestInterface->getControllerName();
        $action     = $requestInterface->getActionName();


        /** @var \Magestore\Membership\Model\Package $package */
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package');
        $package->load($packageId);

        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $memberPackageCollection */
        $memberPackageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $memberPackageCollection->addFieldToFilter('member_id', $memberId)->addFieldToFilter('package_id', $packageId);

        $duration = $package->getDuration() * $qty;

        if (count($memberPackageCollection)) {
            /** @var \Magestore\Membership\Model\MemberPackage $memberPackage */
            $memberPackage = $memberPackageCollection->getFirstItem();
            $order_ids = $memberPackage->getData('order_ids');
            if ($order_ids) {
                $order_id_check = explode(',', $order_ids);
                if (!in_array($orderId, $order_id_check)) {
                    $order_ids .= ',' . $orderId;
                }
            }

            $currentLocaleTime = $this->_dateTime->formatDate($this->_date->gmtTimestamp());
            // if package expired
            if ($memberPackage->getEndTime() <= $currentLocaleTime) {
                // if ($moduleName!= 'membershipadmin' && $controller != 'member' &&  $action != 'save' && $memberPackage->getEndTime()){
                $memberPackage->setEndTime($currentLocaleTime);
                // }
            }

            if ($moduleName!= 'membershipadmin' && $controller != 'member' &&  $action != 'save' && !$memberPackage->getEndTime()){
                $endTime = $this->_dateTime->formatDate(strtotime($memberPackage->getEndTime() . '+' . $duration . ' ' . $package->getTimeUnit() . 's'));

            }else{
                $endTime = $this->_dateTime->formatDate(strtotime($currentLocaleTime . '+' . $duration . ' ' . $package->getTimeUnit() . 's'));
            }
            // $endTime = $this->_dateTime->formatDate(strtotime($memberPackage->getEndTime() . '+' . $duration . ' ' . $package->getTimeUnit() . 's'));
            $memberPackage->setData('end_time', $endTime)
                ->setData('order_ids', $order_ids)
                ->updateStatus();

            $memberPackage->save();

        } else {
            $startTime = $this->_dateTime->formatDate($this->_date->gmtTimestamp());
            $endTime = date('Y-m-d H:i:s', strtotime($startTime . '+' . $duration . ' ' . $package->getTimeUnit() . 's'));
            /** @var \Magestore\Membership\Model\MemberPackage $memberPackage */
            $memberPackage = $this->_objectManager->create('Magestore\Membership\Model\MemberPackage');
            $memberPackage->setData('package_id', $packageId)
                ->setData('member_id', $memberId)
                ->setData('end_time', $endTime)
                ->setData('start_time', $startTime)
                ->setData('order_ids', $orderId)
                ->updateStatus();

            $memberPackage->save();
        }

        $this->_emailHelper->sendNewPackageNotice($memberPackage);
    }

    /**
     * @param $customerId
     * @param $productId
     * @return array
     */
    public function getPackagesForProduct($customerId, $productId)
    {
        $avaiableStatuses = [
            MembershipStatus::STATUS_ENABLED,
            MembershipStatus::STATUS_WARNING
        ];

        $packages = [];
        if ($memberId = $this->checkIfCustomerJoinedMembership($customerId)) {
            /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $memberPackageCollection */
            $memberPackageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
            $packageIds = $memberPackageCollection->addFieldToFilter('member_id', $memberId)
                ->addFieldToFilter('status', ['in' => $avaiableStatuses])
                ->addFieldToFilter('end_time', ['datetime' => true, 'from' => date('Y-m-d H:i:s')])
                ->getColumnValues('package_id');

            /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packageCollection */
            $packageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
            $packageCollection->addFieldToFilter('package_id', ['in' => $packageIds])
                ->addFieldToFilter('package_status', Status::STATUS_ENABLED);;

            /** @var \Magestore\Membership\Model\Package $package */
            foreach ($packageCollection as $package) {
                if (in_array($productId, $package->getAllProductIds())) {
                    $packages[] = $package;
                }
            }
        }

        return $packages;
    }

    public function getMembershipPrice($productId, \Magestore\Membership\Model\Package $package)
    {
        $resultPrice = 0;
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $basePrice = floatval($product->getData('price'));
        $productPrice = $package->getData('package_product_price');

        $discountType = $package->getData('discount_type');
        switch ($discountType) {
            case DiscountType::FIXED_AMOUNT :
                $resultPrice = $basePrice - $productPrice;
                break;
            case DiscountType::PERCENTAGE :
                $resultPrice = $basePrice - $basePrice * floatval($productPrice) / 100;
                break;
            default:
                $resultPrice = $productPrice;
        }

        return $resultPrice;
    }

    public function addPaymentHistory($memberId, $packageId, $orderId, $qty = 1, $paymentState = null)
    {
        /** @var \Magestore\Membership\Model\Package $package */
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package')->load($packageId);

        /** @var \Magestore\Membership\Model\MemberPackage $memberPackage */
        $memberPackage = $this->getMemberPackage($memberId, $packageId);
        $startTime = $memberPackage->getStartTime();
        $endTime = $memberPackage->getEndTime();

        /** @var \Magestore\Membership\Model\PaymentHistory $paymentHistory */
        $paymentHistory = $this->_objectManager->create('Magestore\Membership\Model\PaymentHistory');

        while ($qty--) {
            $paymentHistory->setId(null)
                ->setData('member_id', $memberId)
                ->setData('order_id', $orderId)
                ->setData('start_time', $startTime)
                ->setData('end_time', $endTime)
                ->setData('duration', $package->getData('duration'))
                ->setData('time_unit', $package->getData('time_unit'))
                ->setData('package_name', $package->getData('package_name'))
                ->setData('package_product_id', $package->getProductId())
                ->setData('price', $package->getData('package_price'))
                ->setData('status', $paymentState)
                ->save();
        }
    }

    public function updatePaymentHistory()
    {

    }

    public function getPackageByProductId($productId)
    {
        /** @var \Magestore\Membership\Model\Package $package */
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package');
        return $package->load($productId, 'product_id');
    }

    public function getMemberPackage($memberId, $packageId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $memberPackageCollection */
        $memberPackageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $memberPackageCollection->addFieldToFilter('member_id', $memberId)
            ->addFieldToFilter('package_id', $packageId);

        return $memberPackageCollection->getFirstItem();
    }

    public function getMemberId($customerId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $memberCollection */
        $memberCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        $member = $memberCollection->addFieldToFilter('customer_id', $customerId)->getFirstItem();

        return $member->getMemberId();
    }

    public function getMemberByCustomerId($customerId)
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $memberCollection */
        $memberCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        return $memberCollection->addFieldToFilter('customer_id', $customerId)->getFirstItem();
    }

    /**
     * @param $productId
     * @return array
     */
    public function getPackagesOfProduct($productId)
    {
        $productPackages = [];

        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packages */
        $packages = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        $packages->addFieldToFilter('package_status', Status::STATUS_ENABLED);
        /** @var \Magestore\Membership\Model\Package $package */
        foreach ($packages as $package) {
            $productIds = $package->getAllProductIds();
            if (in_array($productId, $productIds)) {
                $productPackages[] = $package->getPackageId();
            }
        }
        return $productPackages;
    }

    public function checkSignedUpPackage($memberId, $packageId)
    {

        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $mpCollection */
        $mpCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $mpCollection->addFieldToFilter('member_id', $memberId)
            ->addFieldToFilter('package_id', $packageId)
            ->setOrder('end_time', 'DESC');
        /** @var \Magestore\Membership\Model\MemberPackage $memberPackage */
        $memberPackage = $mpCollection->getFirstItem();
        if ($memberPackage->getId()) {
            return $memberPackage;
        }
        return null;
    }
}