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

namespace Magestore\Membership\Controller\Adminhtml\Package;

use Magento\Framework\Controller\ResultFactory;
use Magestore\Membership\Model\FeaturedOptions as FeaturedOptions;

/**
 * class MassFeature
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MassFeatured extends \Magestore\Membership\Controller\Adminhtml\Package
{

    /**
     * @return mixed
     */
    public function execute()
    {
        $entityIds = $this->getRequest()->getParam('packages');

        if (!is_array($entityIds) || empty($entityIds)) {
            $this->messageManager->addError(__('Please select record(s).'));
        } else {
            /** @var \Magestore\Membership\Model\ResourceModel\Package\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Package\Collection');
            $collection->addFieldToFilter('package_id', ['in' => $entityIds]);
            try {
                /** @var \Magestore\Membership\Model\ResourceModel\Package $item */
                foreach ($collection as $item) {
                    $item->setIsFeatured(FeaturedOptions::FEATURED)
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->messageManager->addSuccess(
                    __('%1 package(s) turned into featured package.', count($entityIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);;

        return $resultRedirect->setPath('*/*/');
    }
}