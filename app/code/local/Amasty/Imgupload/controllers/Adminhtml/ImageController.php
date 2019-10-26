<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/
class Amasty_Imgupload_Adminhtml_ImageController extends Mage_Adminhtml_Controller_Action
{
    public function uploadAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $product   = Mage::getModel('catalog/product')->load($productId);
        $responce    = array();
        
        try {
            $uploader = new Mage_Core_Model_File_Uploader('file_select');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->addValidateCallback('catalog_product_image',
            Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath()
            );
            $result['url'] = Mage::getSingleton('catalog/product_media_config')->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';
            
            if ($product->getId())
    		{
	            // now saving image to product info
	            $mediaGallery = $product->getMediaGallery();
	            $mediaGallery['images'][] = array(
	                'file'  => $result['file'],
	                'url'   => $result['url'],
	                'disabled' => 0,
	                'removed' => 0,
	                'position' => count($mediaGallery['images']) + 1,
			'label' => '',
	            );
	            $product->setMediaGallery($mediaGallery);
	            $product->save();
    		}
		 $responce = array(
        		'url'	=> $result['url'],
        		'file'	=> $result['file'],
        	);
            
        } catch (Exception $e) {
             $responce = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }
       
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responce));
    }
    
    public function reloadtabAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $storeId   = $this->getRequest()->getParam('store');
        if (!$productId)
        {
            $response = $this->__('No product ID specified');
        }
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId())
        {
            $response = $this->__('Error occured while loading product');
        }
        if ($storeId)
        {
            $product->setStoreId($storeId);
        }

        // will save product image data first
        $imgUploadData = $this->getRequest()->getParam('amimgupload');
        $productData   = $this->getRequest()->getParam('product');
        Mage::getModel('amimgupload/observer')->saveMediaGallery($product, $imgUploadData, $productData);
        
        try
        {
            $product->save();
            $block = Mage::app()->getLayout()->createBlock('amimgupload/adminhtml_catalog_product_edit_tab_images', 'amimgupload_tab_images', array('product' => $product));
            $this->getResponse()->setBody($block->toHtml());
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
        catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('catalog')->__('Error saving product information ' . $e));
            Mage::logException($e);
        }
    }
}