<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Block_File extends Mage_Core_Block_Template 
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amfile/files.phtml');
    }

    protected function _toHtml()
    {
        if (!Mage::getStoreConfig('amfile/general/turnedon'))
            return '';
        else
        {
            $storeId = Mage::app()->getStore()->getStoreId();
            $productId = Mage::registry('current_product')->getId();

            $files = Mage::getResourceModel('amfile/file_collection')->getFilesFrontend($productId, $storeId);

            if (sizeof($files) == 0)
                return '';
            else
            {
                $this->setFiles($files);

                return parent::_toHtml();
            }
        }
    }

    public function getBlockTitle()
    {
        return Mage::getStoreConfig('amfile/general/title');
    }
}
