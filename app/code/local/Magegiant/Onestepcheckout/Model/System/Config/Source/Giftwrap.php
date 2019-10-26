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
class Magegiant_Onestepcheckout_Model_System_Config_Source_Giftwrap extends Mage_Core_Model_Abstract
{
    const PER_ORDER = 0;
    const PER_ITEM  = 1;

    public function toOptionArray()
    {
        return array(
            self::PER_ORDER => Mage::helper('onestepcheckout')->__('Per Order'),
            self::PER_ITEM  => Mage::helper('onestepcheckout')->__('Per Item')
        );
    }
}