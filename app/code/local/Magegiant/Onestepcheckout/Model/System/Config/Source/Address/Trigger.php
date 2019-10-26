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
class Magegiant_Onestepcheckout_Model_System_Config_Source_Address_Trigger
{


    public function getTriggerOption()
    {
        return array(
            'street1'    => Mage::helper('onestepcheckout')->__('Street'),
            'country_id' => Mage::helper('onestepcheckout')->__('Country Id'),
            'region'     => Mage::helper('onestepcheckout')->__('Region '),
            'region_id'  => Mage::helper('onestepcheckout')->__('Region Id'),
            'city'       => Mage::helper('onestepcheckout')->__('City'),
            'postcode'   => Mage::helper('onestepcheckout')->__('Postcode'),
        );
    }

    public function toOptionArray()
    {

        $options = array();
        foreach ($this->getTriggerOption() as $code => $label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }

        return $options;
    }
}