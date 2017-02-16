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

namespace Magestore\Membership\Block\Adminhtml\Member;

/**
 * class Edit
 *
 * @category Magestore
 * @package  Magestore_Membership
 * @module   Membership
 * @author   Magestore Developer
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected function _construct()
    {
        $this->_objectId = 'member_id';
        $this->_blockGroup = 'Magestore_Membership';
        $this->_controller = 'adminhtml_member';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->update('delete', 'label', __('Delete'));

        $this->addButton(
            'save',
            [
                'label' => __('Save Member'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ],
            ],
            1
        );

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'back' => 'edit', 'target' => '#edit_form'],
                    ],
                ],
            ],
            -100
        );

        $this->buttonList->add(
            'new-button',
            [
                'label' => __('Save and New'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndNew', 'back' => 'new', 'target' => '#edit_form'],
                    ],
                ],
            ],
            10
        );
        $this->_formScripts[] = '   
            function toggleEditor() {
                if (tinyMCE.getInstanceById(\'template_content\') == null) {
                    tinyMCE.execCommand(\'mceAddControl\', false, \'template_content\');
                } else {
                    tinyMCE.execCommand(\'mceRemoveControl\', false, \'template_content\');
                }
            }

         
                        
                    require([
                            "jquery",
                            "underscore",
                            "mage/mage",
                            "mage/backend/tabs",
                            "domReady!"
                        ], function($) {
                       
                            var $form = $(\'#edit_form\');
                            $form.mage(\'form\', {
                                handlersData: {
                                    save: {},
                                    saveAndNew: {
                                        action: {
                                            args: {back: \'new\'}
                                        }
                                    },
                                }
                            });

                        });
                    

        ';
    }
}