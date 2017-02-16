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
 * class DurationUnit
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class DurationUnit
{
    const DAY = 'day';

    const WEEK = 'week';

    const MONTH = 'month';

    const YEAR = 'year';

    /**
     * Get available statuses.
     *
     * @return void
     */
    public static function getTimeUnits()
    {
        return [
            self::DAY => __('Day'),
            self::WEEK => __('Week'),
            self::MONTH => __('Month'),
            self::YEAR => __('Year')
        ];
    }
}