<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/
class Amasty_Imgupload_Model_System_Config_Source_Dragable
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'moveable',  'label'=>Mage::helper('adminhtml')->__('Item header')),
            array('value'=>'am_item',   'label'=>Mage::helper('adminhtml')->__('Whole item block')),
            array('value'=>'am_image',  'label'=>Mage::helper('adminhtml')->__('Item image')),
        );
    }
}
