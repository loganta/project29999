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
 * class CustomerSaveAfterObserver
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class CustomerSaveAfterObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;


    /**
     * CustomerSaveAfterObserver constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getEvent()->getCustomer();

        /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $memberCollection */
        $memberCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
        /** @var \Magestore\Membership\Model\Member $member */
        $member = $memberCollection->addFieldToFilter('customer_id', $customer->getId())->getFirstItem();

        if ($member->getId()) {
            $member->setData('name', $customer->getName())->setData('email', $customer->getEmail());
            $member->save();
        }
        return $this;
    }
}