<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Imgupload
*/
class Amasty_Imgupload_Block_Adminhtml_Catalog_Product_Edit_Tab_Images extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_product = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amimgupload/product/edit/tab/images.phtml');
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_initProduct();
    }
    
    protected function _initProduct()
    {
        if ($this->getProduct())
        {
            $this->_product = $this->getProduct();
        } elseif (Mage::registry('product'))
        {
            $this->_product = Mage::registry('product');
        }
    }
    
    public function getStoreId()
    {
        return Mage::app()->getRequest()->getParam('store', 0);
    }
    
    public function getCurrentProduct()
    {
        return $this->_product;
    }
    
    public function getProductId()
    {
    	if ($this->_product->getId())
    	{
        	return $this->_product->getId();
    	}
    	return 0;
    }
    
    public function getImages()
    {
        $images  = array();
        if ($media = $this->_product->getData('media_gallery'))
        {
            if (isset($media['images']))
            {
                $images = $media['images'];
                foreach ($images as &$image) {
                    $image['url'] = Mage::getSingleton('catalog/product_media_config')
                                        ->getMediaUrl($image['file']);
                }
            }
        }
        return $images;
    }
    
    public function getImageTypes()
    {
        $imageTypes = array();
        foreach ($this->_product->getMediaAttributes() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $imageTypes[$attribute->getAttributeCode()] = array(
                'label' => $attribute->getFrontend()->getLabel(),
                'field' => $attribute->getAttributeCode(),
            );
        }
        return $imageTypes;
    }
    
    public function getImagesValues()
    {
        $values = array();
        foreach ($this->_product->getMediaAttributes() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $values[$attribute->getAttributeCode()] = $this->_product->getData(
                $attribute->getAttributeCode()
            );
        }
        return $values;
    }
    
    public function isAjax()
    {
        return Mage::app()->getRequest()->isAjax();
    }

    /**
    * Implementing interface
    */
    
    public function getTabLabel()
    {
        return $this->__('Product Images');
    }
    
    public function getTabTitle()
    {
        return $this->__('Product Images');
    }
    
    public function canShowTab()
    {
    	$action = Mage::app()->getRequest()->getActionName();
    	$setId  = $this->getRequest()->getParam('set', null);
    	if ( ('new' != $action) || ('new' == $action && $setId) )
    	{
        	return true;
    	}
    	return false;
    }
    
    public function isHidden()
    {
        $action = Mage::app()->getRequest()->getActionName();
    	$setId  = $this->getRequest()->getParam('set', null);
    	if ( ('new' != $action) || ('new' == $action && $setId) )
    	{
        	return false;
    	}
        return true;
    }
}