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
 * class Save
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Save extends \Magestore\Membership\Controller\Adminhtml\Member
{

    /**
     * action dispatcher
     * @return mixed
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        // there is a customer selected
        if ($customerId = $this->getRequest()->getParam('customer_select')) {
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            // get needed data of the customer for a membership member
            $memberData = [
                'customer_id' => $customer->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];

            /** @var \Magestore\Membership\Model\Member $model */
            $model = $this->_objectManager->create('Magestore\Membership\Model\Member');

            if ($memberId = $this->getRequest()->getParam('member_id')) {
                // edit a membership member
                $model->load($memberId);
                $model->addData($memberData);
            } else {
                // create new membership member
                $memberData['joined_time'] = date('Y-m-d H:i:s');
                $model->setData($memberData);
            }

            try {
                $model->save();

                /** @var \Magestore\Membership\Helper\Data $helper */
                $helper = $this->_objectManager->get('Magestore\Membership\Helper\Data');
                $memberData = $this->getRequest()->getPostValue();

                if (isset($memberData['member_packages'])) {
                    // assign packages to member
                    $packageIds = $this->_getIdsFromParseStr($memberData['member_packages']);
                    $helper->assignPackagesToMember($model->getId(), $packageIds);
                }

                $this->messageManager->addSuccess(__('The member has been saved.'));
                $this->_getSession()->setFormData(false);

                return $this->_getBackResultRedirect($resultRedirect, $model->getId());

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the record.'));

                $this->_getSession()->setFormData($memberData);

                $resultRedirect->setPath(
                    '*/*/edit', [$this->_param_crud_id => $model->getId()]
                );
            }
        } else {
            $this->messageManager->addError(__('We can\'t save membership member. There is no customer selected !'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * extract ids from parse string
     * @param $string (1&2&3&...)
     * @return array [1,2,3,...]
     */
    protected function _getIdsFromParseStr($string)
    {
        $extractedIds = explode('&', $string);
        $ids = [];
        foreach ($extractedIds as $id) {
            if (is_numeric($id)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }
}