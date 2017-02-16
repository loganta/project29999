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

namespace Magestore\Membership\Cron;

use Magestore\Membership\Model\MemberPackageStatus;

/**
 * class MembershipWarningEmail
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MembershipWarningEmail
{

    /**
     * @var \Magestore\Membership\Helper\Email
     */
    protected $_email;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * MembershipWarningEmail constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Membership\Helper\Email $email
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Membership\Helper\Email $email
    )
    {
        $this->_email = $email;
        $this->_objectManager = $objectManager;
    }

    /**
     * runs on cron job
     */
    public function execute()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $collection->addFieldToFilter('end_time', ['datetime' => true, 'to' => date('Y-m-d H:i:s')]);
        if (count($collection)) {
            /** @var \Magestore\Membership\Model\MemberPackage $memberPackage */
            foreach ($collection as $memberPackage) {
                if ($memberPackage->updateStatus() == MemberPackageStatus::STATUS_WARNING) {
                    $this->_email->notifyRenewPackage($memberPackage);
                }
            }
        }
    }
}