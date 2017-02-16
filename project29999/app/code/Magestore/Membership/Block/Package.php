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

use Magento\Catalog\Model\Product\Visibility as ProductVisibility;

/**
 * class Package
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Package extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Membership\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_productFactory = $productFactory;
        $this->_customerSession = $customerSession;
        $this->_priceHelper = $priceHelper;
        $this->_productVisibility = $productVisibility;
        $this->_imageHelper = $imageHelper;
        $this->_cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    /**
     * set product collection
     */
    protected function _construct()
    {
        parent::_construct();

        $visibilities = [
            ProductVisibility::VISIBILITY_BOTH,
            ProductVisibility::VISIBILITY_IN_CATALOG
        ];

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_productFactory->create()->getCollection();
        $collection->addAttributeToFilter('entity_id', ['in' => $this->getPackage()->getAllProductIds()])
            ->addAttributeToSelect('*')
            ->addFieldToFilter('price', ['gt' => 0])
            ->addAttributeToFilter('visibility', $visibilities)
            ->setOrder('price', 'DESC');

        $this->setCollection($collection);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getPackage()->getPackageName());

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
                    'label' => $this->getPackage()->getPackageName()
                ]
            );
        }

        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'membership.package.detail.pager');
        $pager->setCollection($this->getCollection())
            ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50, 100 => 100]);

        $this->setChild('pager', $pager);
        $this->getCollection()->load();

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return \Magestore\Membership\Model\Package
     */
    public function getPackage()
    {
        $packageId = $this->getRequest()->getParam('id');
        /** @var \Magestore\Membership\Model\Package $package */
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package');
        $package->load($packageId);
        return $package;
    }

    /**
     * @return \Magestore\Membership\Model\MemberPackage|null
     */
    public function getSignedUpPackageInfo()
    {
        $packageId = $this->getRequest()->getParam('id');

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

    /**
     * @param $productId
     * @param \Magestore\Membership\Model\Package $package
     * @return float|int|mixed
     */
    public function getMembershipPrice($productId, \Magestore\Membership\Model\Package $package)
    {
        return max(0, $this->_helper->getMembershipPrice($productId, $package));
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductThumbnail(\Magento\Catalog\Model\Product $product)
    {
        return $this->_imageHelper->init($product, 'rss_thumbnail')->resize(70)->getUrl();
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
}