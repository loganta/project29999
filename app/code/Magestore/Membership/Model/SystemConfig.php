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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * class SystemConfig
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class SystemConfig
{

    /**
     * Default email template to notify customer about membership sign-up
     * @string
     */
    const DEFAULT_NEW_PACKAGE_TEMPLATE = 'magestore_membership_new_package';

    /**
     * Default email template to notify customer to renew their membership package
     * @string
     */
    const DEFAULT_NOTIFY_RENEW_PACKAGE_TEMPLATE = 'magestore_membership_renew_package';

    /**
     * Activate membership package or update membership purchase when order status is
     * @string
     */
    const DEFAULT_ORDER_STATUS = 'complete';

    /**
     * @string
     */
    const FULL_MODULENAME_MAGESTORE_MEMBERSHIP = 'Magestore_Membership';

    /**
     * @string
     */
    const XML_PATH_DAY_BEFORE_EXPIRATION = 'membership/general/days_before_expiration';

    /**
     * @string
     */
    const XML_PATH_STYLE = 'membership/membership_style';

    /**
     * @string
     */
    const XML_PATH_SHORT_DESCRIPTION = 'membership/general/short_description';

    /**
     * @string
     */
    const XML_PATH_NEW_PACKAGE_TEMPLATE = 'membership/general/new_package_email_template';

    /**
     * @string
     */
    const XML_PATH_NOTIFY_RENEW_PACKAGE_TEMPLATE = 'membership/general/notify_renew_package_email_template';

    /**
     * @string
     */
    const XML_PATH_IS_SHOW_HEAD_LINK = 'membership/general/is_show_head_link';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * SystemConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get config by path.
     *
     * @param $path
     *
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        if ($storeId !== null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }

        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return mixed
     */
    public function getDaysBeforeExp()
    {
        return $this->getConfig(self::XML_PATH_DAY_BEFORE_EXPIRATION);
    }


    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->getConfig(self::XML_PATH_SHORT_DESCRIPTION);
    }

    /**
     * @return mixed
     */
    public function getStyle()
    {
        return $this->getConfig(self::XML_PATH_STYLE);
    }

    /**
     * @return mixed
     */
    public function getNewPackageEmailTemplate()
    {
        $template = $this->getConfig(self::XML_PATH_NEW_PACKAGE_TEMPLATE);
        $template = $template ? $template : self::DEFAULT_NEW_PACKAGE_TEMPLATE;
        return $template;
    }

    /**
     * @return mixed
     */
    public function getNotifyRenewPackageEmailTemplate()
    {
        $template = $this->getConfig(self::DEFAULT_NOTIFY_RENEW_PACKAGE_TEMPLATE);
        $template = $template ? $template : self::DEFAULT_NOTIFY_RENEW_PACKAGE_TEMPLATE;
        return $template;
    }

    /**
     * @return mixed
     */
    public function isShowHeadLink()
    {
        return $this->getConfig(self::XML_PATH_IS_SHOW_HEAD_LINK);
    }
}