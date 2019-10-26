<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */

class Amasty_File_Adminhtml_FileController extends Mage_Adminhtml_Controller_Action
{
    public function downloadAction()
    {
        $fileId = $this->getRequest()->getParam('file_id');

        Mage::helper('amfile')->giveFile($fileId);
    }

    public function updateAction()
    {
        $result = Mage::getSingleton('amfile/observer')->updateFileData();

        $productId = Mage::app()->getRequest()->getParam('id');
        $storeId = Mage::app()->getRequest()->getParam('store', 0);

        $files = Mage::getResourceModel('amfile/file_collection')->getFilesAdmin($productId, $storeId);

        $files->getSelect()
            ->where('main_table.file_id IN (?)', $result['updated']);

        $content = '';
        foreach ($files as $file)
        {
            $block = new Mage_Core_Block_Template();

            $content .= $block
                ->setTemplate('amfile/tab/item.phtml')
                ->setStoreId($storeId)
                ->setItem($file)
                ->toHtml();
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(
                array(
                    'errors' => array_values($result['errors']),
                    'content' => $content
                )
            )
        );
    }
}
