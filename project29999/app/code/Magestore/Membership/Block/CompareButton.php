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
 * class CompareButton
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class CompareButton extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magestore\Membership\Helper\Data
     */
    protected $_helper;

    /**
     * CompareButton constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Membership\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Membership\Helper\Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    public function getCompareUrl()
    {
        return $this->getUrl('membership/package/compare', ['pid' => $this->getRequest()->getParam('id')]);
    }

    public function isMembershipProduct()
    {
        $productId = $this->getRequest()->getParam('product_id');
        if (isset($productId)){
            return count($this->_helper->getPackagesOfProduct($productId)) > 0;
        }else
            return count($this->_helper->getPackagesOfProduct($this->getRequest()->getParam('id'))) > 0;
    }
}