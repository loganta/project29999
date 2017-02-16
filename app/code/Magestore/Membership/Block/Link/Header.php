<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 13/5/2016
 * Time: 10:25 AM
 */

namespace Magestore\Membership\Block\Link;

class Header extends \Magento\Framework\View\Element\Html\Link
{

    /**
     * @var \Magestore\Membership\Model\SystemConfig
     */
    protected $_msConfig;

    /**
     * Header constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Membership\Model\SystemConfig $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Membership\Model\SystemConfig $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_msConfig = $config;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('membership');
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->_msConfig->isShowHeadLink())
            return parent::_toHtml();
        return '';
    }
}