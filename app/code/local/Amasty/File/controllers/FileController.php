<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */
class Amasty_File_FileController extends Mage_Core_Controller_Front_Action
{
    public function downloadAction()
    {
        $fileId = $this->getRequest()->getParam('file_id');

        Mage::helper('amfile')->giveFile($fileId);
    }
}
