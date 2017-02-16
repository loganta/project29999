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

namespace Magestore\Membership\Block;

/**
 * class MembershipPlan
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MembershipPlan extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Membership\Model\SystemConfig
     */
    protected $_msConfig;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;


    /**
     * Membership constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Membership\Model\SystemConfig $msConfig
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Membership\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Membership\Model\SystemConfig $msConfig,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Membership\Helper\Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_msConfig = $msConfig;
        $this->_priceHelper = $priceHelper;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {

        $this->pageConfig->getTitle()->set(__('Membership Area'));

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'membership',
                [
                    'label' => __('Membership'),
                    'title' => __('Membership'),
                    'link' => $this->getUrl('membership')
                ]
            )->addCrumb(
                'detail',
                [
                    'label' => __('Membership Area'),
                ]
            );
        }

        return parent::_prepareLayout();
    }

    public function getMyPackages()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\MemberPackage\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\MemberPackage\Collection');
        $collection->addFieldToFilter('member_id', $this->_getMemberId())
            ->setOrder('member_package_id', 'ASC');

        return $collection;
    }

    public function getPackageById($packageId)
    {
        /** @var \Magestore\Membership\Model\Package $package */
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package');
        return $package->load($packageId);
    }

    protected function _getMemberId()
    {
        $customerId = $this->_customerSession->getCustomerId();
        return $this->_helper->getMemberId($customerId);
    }

    public function isMemberEnabled()
    {
        $customerId = $this->_customerSession->getCustomerId();
        return $this->_helper->isMemberEnabled($customerId);
    }

    public function getPaymentHistory()
    {
        /** @var \Magestore\Membership\Model\ResourceModel\PaymentHistory\Collection $collection */
        $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\PaymentHistory\Collection');
        $collection->addFieldToFilter('member_id', $this->_getMemberId())
            ->setOrder('start_time', 'DESC');

        return $collection;
    }

    public function getRenewUrl($packageId)
    {
        return $this->getUrl('membership/index/addToCart', ['id' => $packageId]);
    }

    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

}