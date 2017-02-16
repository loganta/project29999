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
 * class Edit
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Edit extends \Magestore\Membership\Controller\Adminhtml\Member
{
    /**
     * Execute action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('member_id');

        /** @var \Magestore\Membership\Model\Member $model */
        $model = $this->_objectManager->create('Magestore\Membership\Model\Member');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_getSession()->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_objectManager->get('Magento\Framework\Registry')->register('membership_member_model', $model);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Magestore_Membership::magestoremembership');

        if ($model->getId()){
            $strTitle = 'Edit Member: '.$model->getName();
        }else{
            $strTitle = 'New Member';
        }
        $resultPage->getConfig()->getTitle()->set(__($strTitle));

        return $resultPage;
    }
}
