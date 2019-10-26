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
class Magegiant_Onestepcheckout_Model_System_Config_Source_FieldOption
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('adminhtml')->__('Hidden')),
            array('value' => 'opt', 'label' => Mage::helper('adminhtml')->__('Optional')),
            array('value' => 'req', 'label' => Mage::helper('adminhtml')->__('Required')),
        );
    }

    public function toOption()
    {
        return array(
            ''   => Mage::helper('onestepcheckout')->__('Hidden'),
            'opt' => Mage::helper('onestepcheckout')->__('Optional'),
            'req' => Mage::helper('onestepcheckout')->__('Required'),
        );
    }
}
