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

namespace Magestore\Membership\Block\Adminhtml\Member\Edit\Tab;

/**
 * class RadioButton
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class RadioButton extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * RadioButton constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return "
        <input class='customer_select'" . $this->getCheckedStatus($row) . " type='radio' name='customer_select' value='" . $row->getData('entity_id') . "'>
        ";
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    protected function getCheckedStatus(\Magento\Framework\DataObject $row)
    {
        if ($memberId = $this->getRequest()->getParam('member_id')) {
            /** @var \Magestore\Membership\Model\Member $member */
            $member = $this->_objectManager->create('Magestore\Membership\Model\Member')->load($memberId);

            return $row->getData('entity_id') == $member->getData('customer_id') ? 'checked' : '';
        }
        return '';
    }
}