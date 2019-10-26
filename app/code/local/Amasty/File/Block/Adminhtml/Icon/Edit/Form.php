<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Block_Adminhtml_Icon_Edit_Form extends Mage_Adminhtml_Block_Widget_Form 
{

    /**
     * Init class
     */
      public function __construct()
    {
        parent::__construct();
        $this->setId('oconTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amfile')->__('Icon Options'));
    }

    protected function _prepareForm() 
    {
       $form = new Varien_Data_Form(array(
            'id'      => 'edit_form', 
            'action'  => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();

    }

}