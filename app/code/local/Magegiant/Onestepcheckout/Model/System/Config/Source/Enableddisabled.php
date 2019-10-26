<?php
/**
* Magegiant
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
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */


class Magegiant_Onestepcheckout_Model_System_Config_Source_Enableddisabled
{
    const DISABLED_CODE = 0;
    const ENABLED_CODE  = 1;
    const DISABLED_LABEL = 'Disabled';
    const ENABLED_LABEL  = 'Enabled';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ENABLED_CODE,
                'label' => Mage::helper('onestepcheckout')->__(self::ENABLED_LABEL),
            ),
            array(
                'value' => self::DISABLED_CODE,
                'label' => Mage::helper('onestepcheckout')->__(self::DISABLED_LABEL),
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::ENABLED_CODE  => Mage::helper('onestepcheckout')->__(self::ENABLED_LABEL),
            self::DISABLED_CODE => Mage::helper('onestepcheckout')->__(self::DISABLED_LABEL),
        );
    }
}