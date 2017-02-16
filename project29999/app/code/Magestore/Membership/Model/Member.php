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

/**
 * class Member
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Member extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Member constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Membership\Model\ResourceModel\Member');
    }

    public function isEnable()
    {
        $status = $this->getMemberStatus();
        return $status == Status::STATUS_ENABLED;
    }

    /**
     * get all available packageIds which member registered
     * @return array
     */
    public function getPackageIds()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $memberPackage */
        $memberPackage = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $memberPackage->addFieldToFilter(
            'status',
            [
                'in' => [
                    MemberPackageStatus::STATUS_ENABLED,
                    MemberPackageStatus::STATUS_WARNING
                ]
            ]
        )->addFieldToFilter('member_id', $this->getId());

        return $memberPackage->getColumnValues('package_id');
    }

    /**
     * get collection of available packages which member registered
     * @return ResourceModel\Package\Collection
     */
    public function getPackages()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packages */
        $packages = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        $packages->addFieldToFilter('package_id', ['in' => $this->getPackageIds()]);

        return $packages;
    }
}