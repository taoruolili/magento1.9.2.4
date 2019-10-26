<?php

/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magegiant.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement.html
 */
class Magegiant_Onestepcheckout_Model_System_Config_Source_Color
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '#3399cc', 'label' => Mage::helper('onestepcheckout')->__('Default')),
            array('value' => 'orange', 'label' => Mage::helper('onestepcheckout')->__('Orange')),
            array('value' => 'green', 'label' => Mage::helper('onestepcheckout')->__('Green')),
            array('value' => 'black', 'label' => Mage::helper('onestepcheckout')->__('Black')),
            array('value' => 'blue', 'label' => Mage::helper('onestepcheckout')->__('Blue')),
            array('value' => 'darkblue', 'label' => Mage::helper('onestepcheckout')->__('Dark Blue')),
            array('value' => 'pink', 'label' => Mage::helper('onestepcheckout')->__('Pink')),
            array('value' => 'red', 'label' => Mage::helper('onestepcheckout')->__('Red')),
            array('value' => 'violet', 'label' => Mage::helper('onestepcheckout')->__('Violet')),
            array('value' => 'custom', 'label' => Mage::helper('onestepcheckout')->__('Custom')),
        );
    }
}
