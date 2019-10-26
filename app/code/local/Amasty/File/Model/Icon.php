<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Icon extends Mage_Core_Model_Abstract {

    public function _construct() 
    {
        $this->_init('amfile/icon');
    }

    public function removeOldFile()
    {
        $image = $this->getOrigData('image');

        $fileName = Mage::getBaseDir() . DS . 'media' . DS . 'amfile' . DS . $image;

        if (file_exists($fileName))
            unlink($fileName);
    }

    public function getIcon($file_url)
    {
        if ($icon = Mage::getResourceModel('amfile/icon')->getIcon($file_url))
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'amfile/' . $icon;
        else
            return false;
    }

    protected function _afterSave()
    {
        $newImage = $this->getData('image');
        $oldImage = $this->getOrigData('image');

        if ($newImage && $oldImage && $newImage != $oldImage)
            $this->removeOldFile();

        return parent::_afterSave();
    }

    protected function _afterDelete()
    {
        $this->removeOldFile();

        return parent::_afterDelete();
    }
}
