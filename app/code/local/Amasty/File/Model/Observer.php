<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Observer 
{
    public function saveProductTabData(Varien_Event_Observer $observer)
    {
        $result = $this->updateFileData();
        if ($result['errors'])
            foreach ($result['errors'] as $error)
                Mage::getSingleton('adminhtml/session')->addError($error);
    }

    public function updateFileData()
    {
        $storeId = Mage::app()->getRequest()->getParam('store', 0);
        $files = Mage::app()->getRequest()->getPost('files');

        $result = array(
            'errors' => array(),
            'updated' => array(),
        );

        if (!$files)
            return $result;

        foreach ($files as $id => $fileData)
        {
            $fileData['product_id'] = Mage::app()->getRequest()->getParam('id');

            try
            {
                $file = Mage::getModel("amfile/file");

                if ($id > 0)
                    $file->load($id);

                $file->addData($fileData);

                if ($file->getDelete() == 1)
                {
                    if (!$file->isObjectNew())
                        $file->delete();
                    continue;
                }

                if ($file->getUse() == 'file')
                {
                    $file->setFileLink('');

                    if (isset($_FILES['files']['error'][$id]['file']))
                    {
                        $code = $_FILES['files']['error'][$id]['file'];
                        if (0 == $code)
                        {
                            $file->saveFile($_FILES['files']['name'][$id]['file'], $_FILES['files']['tmp_name'][$id]['file']);
                            if ($fileData['file_name'])
                                $file->setFileName($fileData['file_name']);
                            else
                                $file->setFileName($_FILES['files']['name'][$id]['file']);
                        }
                        else
                        {
                            if ($code !== UPLOAD_ERR_NO_FILE) // Not error
                            {
                                $result['errors'][$id] = Mage::helper('amfile')->getUploadErrorMessage($code);
                                continue;
                            }
                        }
                    }
                }
                else
                    $file->removeOldFile();

                if ($file->getFileUrl() || $file->getFileLink())
                {
                    $file->save();
                    $result['updated'][]= $file->getId();

                    $storeData = array(
                        'file_id' => +$file->getId(),
                        'store_id' => $file->getOrigData() === null ? 0 : +$storeId, // New object. Can't use isObjectNew
                        'label' => (string)$file->getTitle(),
                        'position' => +$file->getPosition(),
                        'visible' => +$file->getVisible(),
                        'use_default_label' => +$file->getDefaultTitle(),
                        'use_default_visible' => +$file->getDefaultVisible()
                    );

                    Mage::getSingleton('core/resource')
                        ->getConnection('core/write')
                        ->insertOnDuplicate(
                            Mage::getSingleton('core/resource')->getTableName('amfile/store'),
                            $storeData
                        );
                }
                else
                {
                    if ($file->getId()) // skip new empty entries
                        throw new Exception(Mage::helper('amfile')->__("File or file url must be specified"));
                }
            }
            catch (Exception $e)
            {
                $result['errors'][$id] = $e->getMessage();
            }
        }

        return $result;
    }

    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        $massactionClass  = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $productGridClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_grid');
        if ($massactionClass == get_class($block) && $productGridClass == get_class($block->getParentBlock()))
        {
            $hlp = Mage::helper('amfile');

            $block->addItem('amfile_clear', array(
                'label'      => $hlp->__('Remove All Attachments'),
                'url'        => $block->getParentBlock()->getUrl('amfile/adminhtml_actions/clear/'),
            ));

            $additional = array('source' => array(
                'name'  => 'source',
                'type'  => 'text',
                'class' => 'required-entry validate-greater-than-zero',
                'label' => $hlp->__('Source Product Id'),
            ));

            $block->addItem('amfile_copy', array(
                'label'      => $hlp->__('Copy Attachments'),
                'url'        => $block->getParentBlock()->getUrl('amfile/adminhtml_actions/copy/'),
                'additional' => $additional,
            ));
        }

        return $this;
    }

    public function onProductDeleteBefore($observer)
    {
        $product = $observer->getProduct();

        $files = Mage::getResourceModel('amfile/file_collection')
            ->addFieldToFilter('product_id', $product->getId());

        foreach ($files as $file)
            $file->delete();
    }
}
