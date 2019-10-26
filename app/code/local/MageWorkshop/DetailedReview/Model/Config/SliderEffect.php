<?php
/**
 * MageWorkshop
 * Copyright (C) 2012  MageWorkshop <mageworkshophq@gmail.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2012 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DetailedReview_Model_Config_SliderEffect
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'none', 'label'=> $helper->__('No Slider')),
                array('value'=>'swing', 'label'=> $helper->__('Swing')),
                array('value'=>'easeInQuad', 'label'=> $helper->__('easeInQuad')),
                array('value'=>'easeOutQuad', 'label'=> $helper->__('easeOutQuad')),
                array('value'=>'easeInOutQuad', 'label'=> $helper->__('easeInOutQuad')),
                array('value'=>'easeInQuint', 'label'=> $helper->__('easeInQuint')),
                array('value'=>'easeOutQuint', 'label'=> $helper->__('easeOutQuint')),
                array('value'=>'easeInOutQuint', 'label'=> $helper->__('easeInOutQuint')),
                array('value'=>'easeInExpo', 'label'=> $helper->__('easeInExpo')),
                array('value'=>'easeOutExpo', 'label'=> $helper->__('easeOutExpo')),
                array('value'=>'easeInOutExpo', 'label'=> $helper->__('easeInOutExpo')),
                array('value'=>'easeInElastic', 'label'=> $helper->__('easeInElastic')),
                array('value'=>'easeOutElastic', 'label'=> $helper->__('easeOutElastic')),
                array('value'=>'easeInOutElastic', 'label'=> $helper->__('easeInOutElastic')),
                array('value'=>'easeInBack', 'label'=> $helper->__('easeInBack')),
                array('value'=>'easeOutBack', 'label'=> $helper->__('easeOutBack')),
                array('value'=>'easeInOutBack', 'label'=> $helper->__('easeInOutBack')),
                array('value'=>'easeInBounce', 'label'=> $helper->__('easeInBounce')),
                array('value'=>'easeOutBounce', 'label'=> $helper->__('easeOutBounce')),
                array('value'=>'easeInOutBounce', 'label'=> $helper->__('easeInOutBounce')),
            );
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('-- Please Select --')));
        }

        return $options;
    }
}