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

namespace Magestore\Membership\Model\ResourceModel\MemberPackage;

use Magestore\Membership\Controller\Adminhtml\Member;
use Magestore\Membership\Model\Order\Status;
use Magestore\Membership\Model\MemberPackageStatus as MembershipStatus;

/**
 * class Collection
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'Magestore\Membership\Model\MemberPackage',
            'Magestore\Membership\Model\ResourceModel\MemberPackage'
        );
    }

    /**
     * Delete all the entities in the collection
     *
     * @return $this
     */
    public function delete()
    {
        foreach ($this->getItems() as $item) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Disable memberships
     */
    public function disable()
    {
        foreach ($this->getItems() as $item) {
            $item->setStatus(MembershipStatus::STATUS_DISABLE);
            $item->save();
        }
    }

    /**
     * Enable memberships
     */
    public function enable()
    {
        foreach ($this->getItems() as $item) {
            $item->setStatus(MembershipStatus::STATUS_ENABLED);
            $item->save();
        }
    }
}