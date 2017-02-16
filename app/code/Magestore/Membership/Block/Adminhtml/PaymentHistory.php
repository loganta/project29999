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

namespace Magestore\Membership\Block\Adminhtml;

/**
 * class PaymentHistory
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class PaymentHistory extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_paymentHistory';
        $this->_blockGroup = 'Magestore_Membership';
        $this->removeButton('add');
    }
}