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
 * class CssGen
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class CssGen extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magestore\Membership\Model\SystemConfig
     */
    protected $_msConfig;

    /**
     * CssGen constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Membership\Model\SystemConfig $msConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Membership\Model\SystemConfig $msConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_msConfig = $msConfig;
    }

    public function getStyle()
    {
        return $this->_msConfig->getStyle();
    }
}