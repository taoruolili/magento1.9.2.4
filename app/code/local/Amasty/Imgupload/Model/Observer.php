<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/
class Amasty_Imgupload_Model_Observer
{
    public function onCatalogProductPrepareSave($observer)
    {
        $product = $observer->getProduct();
        $request = $observer->getRequest();
        
        $imgUploadData = $request->getParam('amimgupload');
        $productData   = $request->getParam('product');
        
        if (!$product->getId() && $newImages = $request->getParam('amimgupload_new'))
        {
        	$this->addMediaImages($product, $newImages);
        }
        $this->saveMediaGallery($product, $imgUploadData, $productData);
    }
    
    public function addMediaImages($product, $imageData)
    {
    	if (is_array($imageData) && !empty($imageData))
    	{
    		$mediaGallery = $product->getMediaGallery();
    		foreach ($imageData as $file => $url)
    		{
    			$mediaGallery['images'][] = array(
	                'file'  => $file,
	                'url'   => $url,
	                'disabled' => 0,
	                'removed' => 0,
	                'position' => count($mediaGallery['images']) + 1,
	            );
    		}
    		$product->setMediaGallery($mediaGallery);
    	}
    }
    
    public function saveMediaGallery($product, $imgUploadData, $productData)
    {
        if (!$imgUploadData || !$productData)
        {
            return false;
        }
        
        $mediaGallery = $product->getMediaGallery();
        if (isset($mediaGallery['images']) && $mediaGallery['images'])
        {
            if (!is_array($mediaGallery['images']))
            {
                $mediaImages = Zend_Json::decode($mediaGallery['images']);
            } else
            {
                $mediaImages = $mediaGallery['images'];
            }
            if (is_array($mediaImages) && !empty($mediaImages))
            {
                foreach ($mediaImages as &$image)
                {
                    // applying disabled values
                    if (isset($imgUploadData['disable']) && is_array($imgUploadData['disable']) && !empty($imgUploadData['disable']))
                    {
                        foreach ($imgUploadData['disable'] as $file => $disabled)
                        {
                            if ($image['file'] == $file)
                            {
                                $image['disabled']         = $disabled;
                                $image['disabled_default'] = $disabled;
                            }
                        }
                    }
                    
                    // removing images if any
                    if (isset($imgUploadData['delete']) && is_array($imgUploadData['delete']) && !empty($imgUploadData['delete']))
                    {
                        foreach ($imgUploadData['delete'] as $file => $delete)
                        {
                            if ($image['file'] == $file)
                            {
                                if ($delete)
                                {
                                    $image['removed'] = 1;
                                } elseif(isset($image['removed']))
                                {
                                    unset($image['removed']);
                                }
                            }
                        }
                    }
                    
                    // applying labels
                    if (isset($imgUploadData['label']) && is_array($imgUploadData['label']) && !empty($imgUploadData['label']))
                    {
                        foreach ($imgUploadData['label'] as $file => $label)
                        {
                            if ($image['file'] == $file)
                            {
                                $image['label'] = $label;
                                $image['label_default'] = $label;
                            }
                        }
                    }
                    
                    // applying positions
                    if (isset($imgUploadData['position']) && is_array($imgUploadData['position']) && !empty($imgUploadData['position']))
                    {
                        foreach ($imgUploadData['position'] as $file => $position)
                        {
                            if ($image['file'] == $file)
                            {
                                $image['position'] = $position;
                                $image['position_default'] = $position;
                            }
                        }
                    }
                }
            }
            $mediaGallery['images'] = Zend_Json::encode($mediaImages);
        }
        
        if (isset($mediaGallery['values']) && $mediaGallery['values'])
        {
            if (!is_array($mediaGallery['values']))
            {
                $mediaValues = Zend_Json::decode($mediaGallery['values']);
            } else 
            {
                $mediaValues = $mediaGallery['values'];
            }
            $mediaValues['image']       = $productData['image'];
            $mediaValues['small_image'] = $productData['small_image'];
            $mediaValues['thumbnail']   = $productData['thumbnail'];
            $mediaGallery['values'] = Zend_Json::encode($mediaValues);
        }
        
        if (isset($productData['image'])) {
            $product->setImage($productData['image']);
        }
        if (isset($productData['small_image'])) {
            $product->setSmallImage($productData['small_image']);
        }
        if (isset($productData['thumbnail'])) {
            $product->setThumbnail($productData['thumbnail']);
        }
        
        $product->setMediaGallery($mediaGallery);
    }
}