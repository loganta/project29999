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

/**
 * class MemberPackageStatus
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class MemberPackageStatus
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 2;

    const STATUS_WARNING = 3;

    const STATUS_EXPIRED = 4;

    /**
     * Get available statuses.
     *
     * @return void
     */
    public static function getStatus()
    {
        return [
            self::STATUS_ENABLED => __('Activated'),
            self::STATUS_DISABLED => __('Disable'),
            self::STATUS_WARNING => __('Warning'),
            self::STATUS_EXPIRED => __('Expired')
        ];
    }
}