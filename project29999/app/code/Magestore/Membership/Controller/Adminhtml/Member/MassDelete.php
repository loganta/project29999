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

namespace Magestore\Membership\Controller\Adminhtml\Member;

use Magento\Framework\Controller\ResultFactory;

/**
 * class MassDelete
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MassDelete extends \Magestore\Membership\Controller\Adminhtml\Member
{
    /**
     * Execute action
     */
    public function execute()
    {
        $entityIds = $this->getRequest()->getParam('member');
        if (!is_array($entityIds) || empty($entityIds)) {
            $this->messageManager->addError(__('Please select record(s).'));
        } else {
            /** @var \Magestore\Membership\Model\ResourceModel\Member\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\Member\Collection');
            $collection->addFieldToFilter('member_id', ['in' => $entityIds]);
            try {
                /** @var \Magestore\Membership\Model\ResourceModel\Member $item */
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 member(s) have been deleted.', count($entityIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);;

        return $resultRedirect->setPath('*/*/');
    }
}