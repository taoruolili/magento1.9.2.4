<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Block_Adminhtml_Renderer_Icon extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
     
    public function render(Varien_Object $row)
    {
        $html = '<img ';
        $html .= 'id="' . $this->getColumn()->getId() . '" ';
        $html .= 'src="' .Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."amfile/". $row->getData($this->getColumn()->getIndex()) . '"';
        $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '"/>';
       // $html .= '<br/><p>'.$row->getData($this->getColumn()->getIndex()).'</p>';
        return $html;
    }
}
