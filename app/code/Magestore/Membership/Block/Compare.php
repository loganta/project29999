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
 * class Compare
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Compare extends \Magento\Framework\View\Element\Template
{

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
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * CompareButton constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Membership\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Membership\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_priceHelper = $priceHelper;
        $this->_customerSession = $customerSession;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {

        $this->pageConfig->getTitle()->set(__('Compare'));

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
                    'label' => __('Compare'),
                ]
            );
        }

        return parent::_prepareLayout();
    }

    public function getPackages()
    {
        $packageIds = $this->_helper->getPackagesOfProduct($productId = $this->getRequest()->getParam('pid'));
        /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $packageCollection */
        $packageCollection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
        $packageCollection->addFieldToFilter('package_id', ['in' => $packageIds])
            ->setOrder('sort_order', 'ASC');
        return $packageCollection;
    }

    public function getPackageUrl(\Magestore\Membership\Model\Package $package)
    {
        return $this->getUrl('membership/package/view', ['id' => $package->getId()]);
    }

    public function getProduct()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        return $product->load($this->getRequest()->getParam('pid'));
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    public function getMembershipPrice(\Magestore\Membership\Model\Package $package)
    {
        return max(0, $this->_helper->getMembershipPrice($this->getRequest()->getParam('pid'), $package));
    }

    /**
     * @return \Magestore\Membership\Model\MemberPackage|null
     */
    public function getSignedUpPackageInfo($packageId)
    {
        if (!$customerId = $this->_customerSession->getCustomerId()) {
            return null;
        }
        if (!$memberId = $this->_helper->getMemberId($customerId)) {
            return null;
        }
        if (!$memberPackage = $this->_helper->checkSignedUpPackage($memberId, $packageId)) {
            return null;
        }
        return $memberPackage;
    }
}