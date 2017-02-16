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
 * class MemberPackage
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MemberPackage extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Default number of days before expiration to change status into Warning
     * @int
     */
    const DEFAULT_NUMBER_OF_DAYS_BEFORE_EXP = 5;

    /**
     * @var SystemConfig
     */
    protected $_msConfigs;

    /**
     * MemberPackage constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param SystemConfig $msConfigs
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Membership\Model\SystemConfig $msConfigs,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_msConfigs = $msConfigs;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Membership\Model\ResourceModel\MemberPackage');
    }

    public function updateStatus()
    {
        $status = MemberPackageStatus::STATUS_ENABLED;
        $endTime = $this->getData('end_time');

        $warningDays = $this->_msConfigs->getDaysBeforeExp();
        $warningDays = ($warningDays && (floatval($warningDays) > 0)) ? $warningDays : self::DEFAULT_NUMBER_OF_DAYS_BEFORE_EXP;

        $warningTime = date('Y-m-d H:i:s', strtotime($endTime . '-' . $warningDays . ' days'));

        if (date('Y-m-d H:i:s') >= $endTime) {
            $status = MemberPackageStatus::STATUS_EXPIRED;
        }
        if ((date('Y-m-d H:i:s') < $endTime) && date('Y-m-d H:i:s') >= $warningTime) {
            $status = MemberPackageStatus::STATUS_WARNING;
        }

        $this->setStatus($status);

        return $this->getStatus();
    }

    public function isNeedToRenew()
    {
        $endTime = $this->getEndTime();
        if ($endTime > date('Y-m-d H:i:s')) {
            return false;
        }
        if ($this->getStatus() == MemberPackageStatus::STATUS_EXPIRED) {
            return false;
        }
        return true;
    }

    public function getViewPackageUrl()
    {
        return $this->getUrl('membership/package/view', ['id' => $this->getPackageId()]);
    }

    public function getFormatedEndTime()
    {
        $date = date_create($this->getEndTime());
        return date_format($date, 'd, M, Y');
    }

    public function getRenewUrl()
    {
        return $this->getUrl('membership/package/view', ['id' => $this->getPackageId()]);
    }
}