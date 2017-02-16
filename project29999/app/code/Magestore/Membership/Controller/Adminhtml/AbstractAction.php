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

namespace Magestore\Membership\Controller\Adminhtml;

/**
 * class AbstractAction
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * string
     */
    protected $_param_crud_id = '';

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Membership::magestoremembership');
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param null $paramCrudId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _getBackResultRedirect(
        \Magento\Backend\Model\View\Result\Redirect $resultRedirect,
        $paramCrudId = null
    )
    {
        switch ($this->getRequest()->getParam('back')) {
            case 'edit':
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        $this->_param_crud_id => $paramCrudId,
                        '_current' => true,
                    ]
                );
                break;
            case 'new':
                $resultRedirect->setPath('*/*/new');
                break;
            default:
                $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }
}