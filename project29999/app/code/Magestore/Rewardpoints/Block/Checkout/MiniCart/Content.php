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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPoints Show Spending Point on Shopping Cart Page
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
namespace Magestore\Rewardpoints\Block\Checkout\MiniCart;
class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Rewardpoints\Helper\Point
     */
    protected $helperPoint;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    protected $_customerSession;

    protected $_calculationEarning;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * Content constructor.
     * @param \Magestore\Rewardpoints\Helper\Point $helperPoint
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Rewardpoints\Helper\Calculation\Earning $calculationEarning
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        \Magestore\Rewardpoints\Helper\Point $helperPoint,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Rewardpoints\Helper\Calculation\Earning $calculationEarning,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->helperPoint = $helperPoint;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_customerSession = $customerSession;
        $this->_calculationEarning = $calculationEarning;
        $this->_urlInterface = $urlInterface;
    }

    /**
     * Check store is enable for display on minicart sidebar
     *
     * @return type
     */
    public function enableDisplay()
    {
        return $this->helperPoint->showOnMiniCart();
    }

    /**
     * get Image (HTML) for reward points
     *
     * @param boolean $hasAnchor
     * @return string
     */
    public function getImageHtml($hasAnchor = true)
    {
        return $this->helperPoint->getImageHtml($hasAnchor);
    }

    /**
     * @return array
     */

    public function knockoutData()
    {
        $earning = $this->_calculationEarning;
        $results = [];
        if ($this->enableDisplay() && $earningPoint = $earning->getTotalPointsEarning()){
            $results['enableReward'] = $this->enableDisplay();
            $results['getImageHtml'] = $this->getImageHtml(true);
            $results['customerLogin'] = $this->_customerSession->isLoggedIn();
            $results['earnPoint'] = $this->helperPoint->format($earningPoint);
            $results['urlRedirectLogin'] = $this->_urlBuilder->getUrl('rewardpoints/index/redirectLogin',
                array(
                    'redirect'=>$this->_urlInterface->getCurrentUrl()
                )
            );
        }

        return $results;
    }

}