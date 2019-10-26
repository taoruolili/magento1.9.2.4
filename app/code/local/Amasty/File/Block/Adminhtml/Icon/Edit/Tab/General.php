<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Label
*/
class Amasty_File_Block_Adminhtml_Icon_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $hlp = Mage::helper('amfile');
        $model = Mage::registry('amfile_icon');
        $fieldset = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('type', 'text', array(
            'name' => 'type',
            'label' => Mage::helper('checkout')->__('Type'),
            'title' => Mage::helper('checkout')->__('Type'),
            'note'      => $hlp->__('Separate multiple extensions by comma'),
            'required' => true,
        ));
        $fieldset->addField('file', 'file', array(
            'name' => 'image',
            'label' => Mage::helper('checkout')->__('Image'),
            'title' => Mage::helper('checkout')->__('Image'),
            'required' => false,
        ));
        $fieldset->addField('active', 'select', array(
            'label' => Mage::helper('amfile')->__('Active'),
            'name' => 'active',
            'values' => array(
            array( 'value'  => 1, 'label' => Mage::helper('amfile')->__("Yes") ),
            array( 'value'  => 0, 'label' => Mage::helper('amfile')->__("No") )
        )
        ));

         $form->setValues($model->getData()); 
        
        return parent::_prepareForm();
    }
}