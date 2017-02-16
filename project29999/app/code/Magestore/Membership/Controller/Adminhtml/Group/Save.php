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
 * class Save
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Save extends \Magestore\Membership\Controller\Adminhtml\Group
{

    /**
     * @return mixed
     */
    public function execute()
    {

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        /** @var \Magestore\Membership\Model\Group $model */
        $model = $this->_objectManager->create('Magestore\Membership\Model\Group');

        /** @var \Magestore\Membership\Helper\Data $helper */
        $helper = $this->_objectManager->get('Magestore\Membership\Helper\Data');

        if ($groupId = $this->getRequest()->getParam('group_id')) {
            $model->load($groupId);
        }

        if ($data = $this->getRequest()->getPostValue()) {
            $model->addData($data);
            try {
                $model->save();

                if (isset($data['group_products'])) {
                    $products = $this->_getIdsFromParseStr($data['group_products']);
                    if ($groupId) {
                        $helper->reassignProductsToGroup($model->getId(), $products);
                    } else {
                        $helper->assignProductsToGroup($model->getId(), $products);
                    }
                }

                $this->messageManager->addSuccess(__('The group has been saved.'));
                $this->_getSession()->setFormData(false);

                return $this->_getBackResultRedirect($resultRedirect, $model->getId());

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the record.'));

                $this->_getSession()->setFormData($data);

                $resultRedirect->setPath(
                    '*/*/edit', [$this->_param_crud_id => $model->getId()]
                );
            }
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