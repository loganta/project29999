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

namespace Magestore\Membership\Helper;

use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * class Email
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Sender email config path
     */
    const XML_PATH_EMAIL_SENDER = 'contact/email/sender_email_identity';

    /**
     * Support email config path
     */
    const XML_PATH_SUPPORT_EMAIL = 'trans_email/ident_support/email';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Membership\Model\SystemConfig
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_logger;


    /**
     * Email constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Membership\Model\SystemConfig $config
     * @param StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Membership\Model\SystemConfig $config,
        StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
    }


    /**
     * @param $memberPackage
     */
    public function sendNewPackageNotice($memberPackage)
    {

        $storeId = $this->_storeManager->getStore()->getId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        /** @var \Magestore\Membership\Model\Package $package */
        $templateId = $this->_config->getNewPackageEmailTemplate();
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package')->load($memberPackage->getPackageId());
        /** @var \Magestore\Membership\Model\Member $member */
        $member = $this->_objectManager->create('Magestore\Membership\Model\Member')->load($memberPackage->getMemberId());
        $templateVars = [
            'member' => $member,
            'package' => $package,
            'memberPackage' => $memberPackage,
            'support_email' => $this->_scopeConfig->getValue(self::XML_PATH_SUPPORT_EMAIL, $storeScope)
        ];

        try {
            $this->_inlineTranslation->suspend();
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                // ->setTemplateModel('Magento\Email\Model\BackendTemplate')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateVars)
                ->setFrom($this->_scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
                ->addTo($member->getEmail(), $member->getName())
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @param $memberPackage
     */
    public function notifyRenewPackage($memberPackage)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        /** @var \Magestore\Membership\Model\Package $package */
        $templateId = $this->_config->getNotifyRenewPackageEmailTemplate();
        $package = $this->_objectManager->create('Magestore\Membership\Model\Package')->load($memberPackage->getPackageId());
        /** @var \Magestore\Membership\Model\Member $member */
        $member = $this->_objectManager->create('Magestore\Membership\Model\Member')->load($memberPackage->getMemberId());
        $templateVars = [
            'member' => $member,
            'package' => $package,
            'memberPackage' => $memberPackage,
            'support_email' => $this->_scopeConfig->getValue(self::XML_PATH_SUPPORT_EMAIL, $storeScope)
        ];

        try {
            $this->_inlineTranslation->suspend();
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                // ->setTemplateModel('Magento\Email\Model\BackendTemplate')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateVars)
                ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
                ->addTo($member->getEmail(), $member->getName())
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e);
        }
    }
}