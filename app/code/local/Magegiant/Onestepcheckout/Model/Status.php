<?php
/**
 * MageGiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageGiant
 * @package     MageGiant_CheckoutPromotion
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 *
 * @category    MageGiant
 * @package     Magegiant_Onestepcheckout
 * @author      MageGiant Developer
 */
class Magegiant_Onestepcheckout_Model_Status extends Varien_Object
{
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;

    /**
     * get model option as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED  => Mage::helper('onestepcheckout')->__('Active'),
            self::STATUS_DISABLED => Mage::helper('onestepcheckout')->__('Inactive')
        );
    }


    public function toOptionArray()
    {
        return self::getOptionHash();
    }


    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }

        return $options;
    }
}