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
class Magegiant_Onestepcheckout_Model_System_Config_Source_Layout
{
    const ONE_COLUMN    = '1column';
    const TWO_COLUMNS   = '2columns';
    const THREE_COLUMNS = '3columns';

    public function toOptionArray()
    {
        $options = array();

        $options[] = array(
            'label' => Mage::helper('onestepcheckout')->__('1 Column'),
            'value' => self::ONE_COLUMN
        );
        $options[] = array(
            'label' => Mage::helper('onestepcheckout')->__('2 Columns'),
            'value' => self::TWO_COLUMNS
        );
        $options[] = array(
            'label' => Mage::helper('onestepcheckout')->__('3 Columns'),
            'value' => self::THREE_COLUMNS
        );

        return $options;
    }
}
