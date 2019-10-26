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
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
     * Prepare form before rendering HTML
     *
     * @return Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Edit_Form
     */
    protected function _prepareForm()
    {
        $editMode = Mage::registry('edit_mode');
        if ($editMode == 'edit') {
            $saveUrl = $this->getUrl('*/*/save');
            $showNew = false;
        } else {
            $saveUrl = $this->getUrl('*/*/create');
            $showNew = true;
        }
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $saveUrl,
            'method'    => 'post'
        ));

        if ($showNew) {
            $fieldset = $form->addFieldset('base_fieldset', array(
                'legend' => Mage::helper('onestepcheckout')->__('General Information'),
                'class'  => 'fieldset-wide'
            ));

            $options = $this->_getFormType()->getCollection()->toOptionArray();
            array_unshift($options, array(
                'label' => Mage::helper('onestepcheckout')->__('-- Please Select --'),
                'value' => ''
            ));
            $fieldset->addField('type_id', 'select', array(
                'name'      => 'type_id',
                'label'     => Mage::helper('onestepcheckout')->__('Based On'),
                'title'     => Mage::helper('onestepcheckout')->__('Based On'),
                'required'  => true,
                'values'    => $options
            ));

            $fieldset->addField('label', 'text', array(
                'name'      => 'label',
                'label'     => Mage::helper('onestepcheckout')->__('Form Label'),
                'title'     => Mage::helper('onestepcheckout')->__('Form Label'),
                'required'  => true,
            ));

            $options = Mage::getModel('core/design_source_design')->getAllOptions(false);
            array_unshift($options, array(
                'label' => Mage::helper('onestepcheckout')->__('All Themes'),
                'value' => ''
            ));
            $fieldset->addField('theme', 'select', array(
                'name'      => 'theme',
                'label'     => Mage::helper('onestepcheckout')->__('For Theme'),
                'title'     => Mage::helper('onestepcheckout')->__('For Theme'),
                'values'    => $options
            ));

            $fieldset->addField('store_id', 'select', array(
                'name'      => 'store_id',
                'label'     => Mage::helper('onestepcheckout')->__('Store View'),
                'title'     => Mage::helper('onestepcheckout')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
            ));

            $form->setValues($this->_getFormType()->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
