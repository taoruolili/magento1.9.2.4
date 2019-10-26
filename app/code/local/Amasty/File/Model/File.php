<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_File extends Mage_Core_Model_Abstract {

    public function _construct() 
    {
        $this->_init('amfile/file', 'file_id');
    }

    public function getUploadDir()
    {
        return Mage::getBaseDir() . DS . 'media' . DS . 'amfile' . DS . 'files' . DS;
    }

    public function getFullName($name = null)
    {
        if (!$name)
            $name = $this->getFileUrl();

        return $this->getUploadDir() . $name;
    }

    public function delete()
    {
        $this->removeOldFile();

        return parent::delete();
    }

    public function saveFile($fileName, $tempFileName)
    {
        $this->removeOldFile();
        $fileName = strtolower($fileName);

        $fileName = preg_replace('/[^\w\.-]/', '', $fileName);

        $fileName = $this->newFileName($fileName);
        $uploadFile = $this->getFullName($fileName);

        if (move_uploaded_file($tempFileName, $uploadFile))
            $this->setFileUrl($fileName);
        else
            throw new Exception(Mage::helper('amfile')->__("Can't upload file to '%s'. Check the permissions and directory exists", $this->getUploadDir()));
    }

    public function newFileName($fileName) 
    {
        $i = 0;
        $newFileName = $fileName;

        while (file_exists($this->getFullName($newFileName)))
            $newFileName = "(" . ++$i . ")" . $fileName;

        return $newFileName;
    }

    public function removeOldFile()
    {
        $linksCount = Mage::getResourceModel('amfile/file_collection')
            ->addFieldToFilter('file_url', $this->getOrigData('file_url'))
            ->getSize();

        if ($linksCount > 1)
            return;

        $fullName = $this->getFullName($this->getOrigData('file_url'));

        $this->setFileUrl(null);
        $this->setFileName(null);

        if (is_file($fullName))
            unlink($fullName);
    }

    public function title()
    {
        if ($this->getLabel())
            return $this->getLabel();
        else if ($this->getFileName())
            return $this->getFileName();
        else
            return $this->getFileLink();
    }

    public function update()
    {

    }

    public function getIcon()
    {
        $url = $this->getFileUrl() ? $this->getFileUrl() : $this->getFileLink();

        return Mage::getModel('amfile/icon')->getIcon($url);
    }
}
