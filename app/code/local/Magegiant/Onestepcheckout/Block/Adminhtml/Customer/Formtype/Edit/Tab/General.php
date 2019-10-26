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
class Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize Edit Form
     *
     */
    public function __construct()
    {
        $this->setDestElementId('edit_form');
        $this->setShowGlobalIcon(false);
        parent::__construct();
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Magegiant_Onestepcheckout_Block_Adminhtml_Customer_Formtype_Grid_Tab_General
     */
    protected function _prepareForm()
    {
        /* @var $model Mage_Eav_Model_Form_Type */
        $model      = Mage::registry('current_form_type');

        $form       = new Varien_Data_Form();
        $fieldset   = $form->addFieldset('general_fieldset', array(
            'legend'    => Mage::helper('onestepcheckout')->__('General Information')
        ));

        $fieldset->addField('continue_edit', 'hidden', array(
            'name'      => 'continue_edit',
            'value'     => 0
        ));
        $fieldset->addField('type_id', 'hidden', array(
            'name'      => 'type_id',
            'value'     => $model->getId()
        ));

        $fieldset->addField('form_type_data', 'hidden', array(
            'name'      => 'form_type_data'
        ));

        $fieldset->addField('code', 'text', array(
            'name'      => 'code',
            'label'     => Mage::helper('onestepcheckout')->__('Form Code'),
            'title'     => Mage::helper('onestepcheckout')->__('Form Code'),
            'required'  => true,
            'class'     => 'validate-code',
            'disabled'  => true,
            'value'     => $model->getCode()
        ));

        $fieldset->addField('label', 'text', array(
            'name'      => 'label',
            'label'     => Mage::helper('onestepcheckout')->__('Form Title'),
            'title'     => Mage::helper('onestepcheckout')->__('Form Title'),
            'required'  => true,
            'value'     => $model->getLabel()
        ));

        $options = Mage::getModel('core/design_source_design')->getAllOptions(false, true);
        array_unshift($options, array(
            'label' => Mage::helper('onestepcheckout')->__('All Themes'),
            'value' => ''
        ));
        $fieldset->addField('theme', 'select', array(
            'name'      => 'theme',
            'label'     => Mage::helper('onestepcheckout')->__('For Theme'),
            'title'     => Mage::helper('onestepcheckout')->__('For Theme'),
            'values'    => $options,
            'value'     => $model->getTheme(),
            'disabled'  => true
        ));

        $fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => Mage::helper('onestepcheckout')->__('Store View'),
            'title'     => Mage::helper('onestepcheckout')->__('Store View'),
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            'value'     => $model->getStoreId(),
            'disabled'  => true
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('onestepcheckout')->__('General');
    }

    /**
     * Retrieve Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('onestepcheckout')->__('General');
    }

    /**
     * Check is can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
