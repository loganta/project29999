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

namespace Magestore\Membership\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * class AddToCart
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class AddToCart extends \Magento\Framework\App\Action\Action
{

    /**
     * package crud param
     */
    const PACKAGE_CRUD_PARAM = 'id';

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        try {

            $package = $this->_initPackage();
            if (!$package) {
                $response = ['success' => false, 'error' => true, 'msg' => __('This package doesn\'t exist anymore !')];
                return $this->_initJsonResponse($response);
            }

            $product = $this->_getProduct($package->getProductId());
            if (!$product) {
                $response = ['success' => false, 'error' => true, 'msg' => __('Error occurs when adding package to cart !')];
                return $this->_initJsonResponse($response);
            }

            /** @var \Magento\Customer\Model\Session $customerSession */
            $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
            $customerCart = $this->_objectManager->create('Magento\Checkout\Model\Cart');

            $params = [];
            $params['qty'] = 1;

            if ($customerSession->getCustomerId()) {

                $customerCart->addProduct($product, $params);
                $customerCart->save();
                $this->messageManager->addSuccess(__(sprintf('Add %s (Membership Package) to cart successfully.', $product->getName())));
                $response = ['success' => true];

                return $this->_initJsonResponse($response);

            } else {
                $customerCart->addProduct($product, $params);
                $customerCart->save();
                $this->messageManager->addSuccess(__(sprintf('Add %s (Membership Package) to cart successfully.', $product->getName())));

                $customerSession->setBeforeAuthUrl($this->_url->getUrl('membership'));
                $response = [
                    'success' => false,
                    'msg' => __('Please login to buy a membership package.'),
                    'redirect' => $this->_url->getUrl('customer/account/login')
                ];
                return $this->_initJsonResponse($response);
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['success' => false, 'error' => true, 'msg' => __('%1', $e->getMessage())];
            return $this->_initJsonResponse($response);

        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => true, 'msg' => __('Error occurs when adding package to cart !')];
            return $this->_initJsonResponse($response);
        }
    }

    /**
     * get requested package to purchase
     * @return \Magestore\Membership\Model\Package|false
     */
    protected function _initPackage()
    {
        if ($packageId = (int)$this->getRequest()->getParam(self::PACKAGE_CRUD_PARAM)) {
            return $this->_objectManager->create('Magestore\Membership\Model\Package')->load($packageId);
        }
        return false;
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _getProduct($productId)
    {
        if ($productId) {
            return $this->_objectManager->create('Magento\Catalog\Model\Product')->load((int)$productId);
        }
        return false;
    }


    /**
     * init Json action result
     * @param $response
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function _initJsonResponse($response)
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($response);
    }
}