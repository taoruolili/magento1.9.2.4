<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */

class Amasty_File_Block_Adminhtml_Catalog_Product_Edit_Tab_File extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Product Attachments');
    }

    public function getTabTitle()
    {
        return $this->__('Product Attachments');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amfile/tab.phtml');
    }

    protected function _prepareLayout()
    {
        if ($this->getRequest()->getActionName() == 'new')
        {
            $this->setTemplate('amfile/new_tab.phtml');
            return $this;
        }

        $storeId = Mage::app()->getRequest()->getParam('store');
        $productId = Mage::registry('current_product')->getId();

        $item = new Varien_Object();
        $item->setId(-1);
        $item->setVisible(0);

        $newBlock = $this->getLayout()
            ->createBlock('core/template', 'new_item')
            ->setTemplate('amfile/tab/item.phtml')
            ->setStoreId(0)
            ->setItem($item)
        ;

        $itemsBlock = $this->getLayout()->createBlock('core/text_list', 'items');

        $files = Mage::getResourceModel('amfile/file_collection')->getFilesAdmin($productId, $storeId);

        foreach ($files as $file)
        {
            $itemBlock = $this->getLayout()
                ->createBlock('core/template')
                ->setTemplate('amfile/tab/item.phtml')
                ->setStoreId($storeId)
                ->setItem($file)
            ;
            $itemsBlock->insert($itemBlock, '', '-');
        }

        $this->insert($itemsBlock);
        $this->insert($newBlock);

        return $this;
    }
}