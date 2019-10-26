<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Block_Adminhtml_Catalog_Product_Tab_File extends Mage_Core_Block_Template 
{

    public function _prepareLayout() 
    {
        parent::_prepareLayout();
        return $this;
    }

    public function getTabData() 
    {
        $results = Mage::getModel('amfile/file')->getTabData();
        return $results;
    }

    public function getDownloadUrl()
    {
        return $this->getUrl('amfile/file/download');
    }
    public function getCurrentStore()
    {
          return Mage::app()->getRequest()->getParam('store');
    }

}
