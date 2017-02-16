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

use Magestore\Membership\Model\PaymentStatus;

/**
 * class PaymentHistory
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class PaymentHistory extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeTime;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * PaymentHistory constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeTime
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeTime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_localeTime = $localeTime;
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
        $this->_init('Magestore\Membership\Model\ResourceModel\PaymentHistory');
    }

    /**
     * Get time unit label
     * @return \Magento\Framework\Phrase|string
     */
    public function getUnitTimeLabel()
    {
        switch ($this->getTimeUnit()) {
            case DurationUnit::DAY:
                $label = __('day(s)');
                break;
            case DurationUnit::WEEK:
                $label = __('week(s)');
                break;
            case DurationUnit::MONTH:
                $label = __('month(s)');
                break;
            case DurationUnit::YEAR:
                $label = __('year(s)');
                break;
            default:
                $label = '';
                break;
        }
        return $label;
    }

    /**
     * get Order Date
     * @return null|string
     */
    public function getOrderDate()
    {
        if ($order = $this->getOrder()) {
            return $this->_localeTime->formatDateTime($this->getOrder()->getData('created_at'), \IntlDateFormatter::LONG);
        }
        return null;
    }

    /**
     * get the order related to payment
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        if ($orderId = $this->getOrderId()) {
            return $this->_objectManager->create('Magento\Sales\Model\Order')->load((int)$orderId);
        }
        return null;
    }

    /**
     * get order status
     * @return null|string
     */
    public function getOrderStatus()
    {
        if ($order = $this->getOrder()) {
            return $order->getStatus();
        }
        return null;
    }

    /**
     * get Order Increment Id
     * @return null|string
     */
    public function getOrderIncrementId()
    {
        if ($order = $this->getOrder()) {
            return $order->getIncrementId();
        }
        return null;
    }

    /**
     * get payment status
     * @return null|string
     */
    public function getPaymentStatus()
    {
        switch ($this->getStatus()) {
            case PaymentStatus::STATUS_PAID:
                return 'Paid';
                break;
            case PaymentStatus::STATUS_REFUNDED:
                return 'Refunded';
                break;
            default:
                return '';
        }
    }
}