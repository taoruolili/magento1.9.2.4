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
class Magegiant_Onestepcheckout_Model_System_Config_Source_Address_Suggest
{


    public function getTriggerOption()
    {
        return array(
            ''       => Mage::helper('onestepcheckout')->__('No'),
            'pca'    => Mage::helper('onestepcheckout')->__('Postcode Any Where'),
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