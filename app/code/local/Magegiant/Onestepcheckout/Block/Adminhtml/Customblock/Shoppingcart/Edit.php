<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_CheckoutPromotion
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customblock_Shoppingcart_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    const PAGE_TABS_BLOCK_ID = 'onestepcheckout_tabs';

    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'onestepcheckout';
        $this->_controller = 'adminhtml_customblock_shoppingcart';

        $this->_updateButton('save', 'label', Mage::helper('onestepcheckout')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('onestepcheckout')->__('Delete Rule'));

        $this->_addButton('saveandcontinue', array(
            'label'   => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
            'class'   => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('onestepcheckout_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'onestepcheckout_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'onestepcheckout_content');
            }


            function saveAndContinueEdit(urlTemplate){
                var urlTemplateSyntax = /(^|.|\\r|\\n)({{(\\w+)}})/;
                var template = new Template(urlTemplate, urlTemplateSyntax);
                var url = template.evaluate({tab_id:" . self::PAGE_TABS_BLOCK_ID . "JsTabs.activeTab.id});
                editForm.submit(url);
            }

        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('customblock_shoppingcart_data')
            && Mage::registry('customblock_shoppingcart_data')->getId()
        ) {
            return Mage::helper('onestepcheckout')->__("Edit Rule '%s'",
                $this->htmlEscape(Mage::registry('customblock_shoppingcart_data')->getName())
            );
        }

        return Mage::helper('onestepcheckout')->__('Add Rule');
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'tab'        => '{{tab_id}}',
            'active_tab' => null
        ));
    }

}