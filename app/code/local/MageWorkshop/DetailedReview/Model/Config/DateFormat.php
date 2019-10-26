<?php

class MageWorkshop_DetailedReview_Model_Config_DateFormat
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'dd/MM/yy', 'label'=> $helper->__('dd/MM/YY')),
                array('value'=>'dd-MM-yy', 'label'=> $helper->__('dd-MM-YY')),
                array('value'=>'MM/dd/yy', 'label'=> $helper->__('MM/dd/YY')),
                array('value'=>'MM-dd-yy', 'label'=> $helper->__('MM-dd-YY')),
                array('value'=>'y-MM-dd', 'label'=> $helper->__('YYYY-MM-dd')),
                array('value'=>'dd-MM-y', 'label'=> $helper->__('dd-MM-YYYY')),
                array('value'=>'dd MMM y', 'label'=> $helper->__('dd MMM YYYY')),
                array('value'=>'MMM dd y', 'label'=> $helper->__('MMM dd YYYY')),
            );
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('-- Please Select --')));
        }

        return $options;
    }
}