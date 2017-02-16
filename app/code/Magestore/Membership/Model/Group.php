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

namespace Magestore\Membership\Model;

use Magestore\Membership\Model\Status as GroupStatus;

/**
 * class Group
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Group extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Group constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Membership\Model\ResourceModel\Group');
    }

    /**
     * return group's productIds in array
     * @return array
     */
    public function getProductIds()
    {
        if ($this->isEnabled()) {
            /** @var \Magestore\Membership\Model\ResourceModel\GroupProduct\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Membership\Model\ResourceModel\GroupProduct\Collection');
            return $collection->addFieldToFilter('group_id', $this->getId())->getColumnValues('product_id');
        }
        return [];
    }

    /**
     * check if group is enabled or not (true => enabled, false => disable)
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getGroupStatus() == GroupStatus::STATUS_ENABLED;
    }
}