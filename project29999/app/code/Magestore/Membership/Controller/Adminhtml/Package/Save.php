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

/**
 * class Save
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Save extends \Magestore\Membership\Controller\Adminhtml\Package
{

    /**
     * Dispatch request
     * @return mixed
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getPostValue()) {

            $packageId = (int)$this->getRequest()->getParam($this->_param_crud_id);

            /** @var \Magestore\Membership\Helper\Data $helper */
            $helper = $this->_objectManager->create('Magestore\Membership\Helper\Data');

            /** @var \Magestore\Membership\Model\Package $model */
            $model = $this->_objectManager->create('Magestore\Membership\Model\Package');

            $filterManager = $this->_objectManager->create('Magento\Framework\Filter\FilterManager');
            if (isset($data['url_key'])) {
                $data['url_key'] = $filterManager->translitUrl($data['url_key']);
            }

            if ($packageId) {
                $model->load($packageId);
            }

            try {
                $data['product_id'] = $helper->saveMembershipProduct($data['package_name'], $data['package_description'], (int)$data['package_price'], $data['package_status'], (int)$model->getData('product_id'));

                $model->addData($data);

                $model->save();

                if (isset($data['package_groups'])) {
                    $groups = $this->_getIdsFromParseStr($data['package_groups']);
                    if ($packageId) {
                        $helper->reassignGroupsToPackage($model->getId(), $groups);
                    } else {
                        $helper->assignGroupsToPackage($model->getId(), $groups);
                    }
                }

                if (isset($data['package_products'])) {
                    $products = $this->_getIdsFromParseStr($data['package_products']);
                    if ($packageId) {
                        $helper->reassignProductsToPackage($model->getId(), $products);
                    } else {
                        $helper->assignProductsToPackage($model->getId(), $products);
                    }
                }

                $this->messageManager->addSuccess(__('The package has been saved.'));
                $this->_getSession()->setFormData(false);

                return $this->_getBackResultRedirect($resultRedirect, $model->getId());

            } catch (\Exception $e) {
                if ($e instanceof \Magento\Framework\Exception\AlreadyExistsException) {
                    $this->messageManager->addWarning(__('Package name already exists, please choose another name !'));
                }
                $this->messageManager->addException($e, __('Something went wrong while saving the record.'));

                $this->_getSession()->setFormData($data);

                $resultRedirect->setPath(
                    '*/*/edit', [$this->_param_crud_id => $model->getId()]
                );

                return $resultRedirect;
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $string
     * @return array
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