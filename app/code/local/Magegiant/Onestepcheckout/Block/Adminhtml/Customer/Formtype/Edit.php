<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magegiant.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */

/**
 * CustomerAttributes Edit Form Content Tab Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Retrieve current form type instance
     *
     * @return Mage_Eav_Model_Form_Type
     */
    protected function _getFormType()
    {
        return Mage::registry('current_form_type');
    }

    /**
     * Initialize Form Container
     *
     */
    public function __construct()
    {
        $this->_objectId   = 'type_id';
        $this->_blockGroup = 'onestepcheckout';
        $this->_controller = 'adminhtml_customer_formtype';

        parent::__construct();

        $editMode = Mage::registry('edit_mode');
        if ($editMode == 'edit') {
            $this->_updateButton('save', 'onclick', 'formType.save(false)');
            $this->_addButton('save_and_edit_button', array(
                'label'     => Mage::helper('onestepcheckout')->__('Save and Continue Edit'),
                'onclick'   => 'formType.save(true)',
                'class'     => 'save'
            ));

            if ($this->_getFormType()->getIsSystem()) {
                $this->_removeButton('delete');
            }

            $this->_headerText = Mage::helper('onestepcheckout')->__('Edit Form Type "%s"', $this->_getFormType()->getCode());
        } else {
            $this->_headerText = Mage::helper('onestepcheckout')->__('New Form Type');
        }
    }
}
