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
 * class Edit
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Edit extends \Magestore\Membership\Controller\Adminhtml\Group
{
    /**
     * Execute action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('group_id');

        /** @var \Magestore\Membership\Model\Group $model */
        $model = $this->_objectManager->create('Magestore\Membership\Model\Group');

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

        $this->_objectManager->get('Magento\Framework\Registry')->register('membership_group_model', $model);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Magestore_Membership::magestoremembership');

        if ($model->getId()){
            $strTitle = 'Edit Product Group: '.$model->getGroupName();
        }else{
            $strTitle = 'New Product Group';
        }
        $resultPage->getConfig()->getTitle()->set(__($strTitle));

        return $resultPage;
    }
}
