<?php

class MageWorkshop_DetailedReview_Model_Config_TimeFormat
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'HH:mm', 'label'=> $helper->__('HH:mm')),
                array('value'=>'HH:mm:ss', 'label'=> $helper->__('HH:mm:ss')),
                array('value'=>'hh:mm a', 'label'=> $helper->__('hh:mm a')),
                array('value'=>'hh:mm:ss a', 'label'=> $helper->__('hh:mm:ss a')),
                array('value'=>'Thh:mmTZD', 'label'=> $helper->__('Thh:mmTZD')),
                array('value'=>'Thh:mm:ssTZD', 'label'=> $helper->__('Thh:mm:ssTZD')),

            );
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('-- Please Select --')));
        }

        return $options;
    }
}