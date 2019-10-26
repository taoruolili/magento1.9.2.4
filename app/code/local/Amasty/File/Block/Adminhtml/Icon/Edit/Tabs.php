<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Label
*/
class Amasty_File_Block_Adminhtml_Icon_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('iconTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amfile')->__('Icon Options'));
    }

    protected function _beforeToHtml()
    {
        $name = Mage::helper('amfile')->__('General');
        $this->addTab('General', array(
            'label'     => $name,
            'content'   => $this->getLayout()->createBlock('amfile/adminhtml_icon_edit_tab_general')
                ->setTitle($name)->toHtml(),
        ));
        
            
        return parent::_beforeToHtml();
    }
}