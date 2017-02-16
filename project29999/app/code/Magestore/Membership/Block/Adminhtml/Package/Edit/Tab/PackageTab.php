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

namespace Magestore\Membership\Block\Adminhtml\Package\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * class PackageTab
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class PackageTab extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;


    /**
     * PackageTab constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_systemStore = $systemStore;
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magestore\Membership\Model\Package $model */
        $model = $this->_coreRegistry->registry('membership_package_model');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('Package Information')]);

        if ($model->getId()) {
            $fieldset->addField('package_id', 'hidden', ['name' => 'package_id']);
        }

        $fieldset->addField(
            'package_name',
            'text',
            [
                'name' => 'package_name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'package_price',
            'text',
            [
                'name' => 'package_price',
                'label' => __('Package Price'),
                'title' => __('Package Price'),
                'class' => 'validate-number',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'discount_type',
            'select',
            [
                'name' => 'discount_type',
                'label' => __('Discount Type'),
                'title' => __('Discount Type'),
                'required' => true,
                'values' => \Magestore\Membership\Model\DiscountType::getDiscountType(),
                'note' => __('If promotion type is percentage and promotion value is 5, each product in this package will be sale off 5%')
            ]
        );

        $fieldset->addField(
            'package_product_price',
            'text',
            [
                'name' => 'package_product_price',
                'label' => __('Discount Value'),
                'title' => __('Discount Value'),
                'class' => 'validate-number',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'duration',
            'text',
            [
                'name' => 'duration',
                'label' => __('Duration'),
                'title' => __('Duration'),
                'required' => true,
                'class' => 'validate-number',
                'note' => __('If duration period is month and duration value is 3, this package will be expired in 3 months.')
            ]
        );

        $fieldset->addField(
            'time_unit',
            'select',
            [
                'name' => 'time_unit',
                'label' => __('Duration Unit'),
                'title' => __('Duration Unit'),
                'required' => true,
                'values' => \Magestore\Membership\Model\DurationUnit::getTimeUnits()
            ]
        );

        $fieldset->addField(
            'package_description',
            'text',
            [
                'name' => 'package_description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'url_key',
            'text',
            [
                'name' => 'url_key',
                'label' => __('Url Key'),
                'title' => __('Url Key'),
            ]
        );

        $fieldset->addField(
            'is_featured',
            'select',
            [
                'name' => 'is_featured',
                'label' => __('Is Featured Package'),
                'title' => __('Is Featured Package'),
                'values' => \Magestore\Membership\Model\FeaturedOptions::getOptions(),
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'class' => 'validate-number',
            ]
        );

        $fieldset->addField(
            'package_status',
            'select',
            [
                'name' => 'package_status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => \Magento\Catalog\Model\Product\Attribute\Source\Status::getOptionArray()
            ]
        );


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return 'Package Information';
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return 'Package Information';
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}