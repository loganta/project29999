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

namespace Magestore\Membership\Controller\Adminhtml\Group;

use Magento\Framework\Controller\ResultFactory;

/**
 * class Products
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Products extends \Magestore\Membership\Controller\Adminhtml\Group
{

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->getLayout()->getBlock('membership.admin.group.product.grid')
            ->setProducts($this->getRequest()->getPost('products', null));

        return $resultLayout;
    }
}