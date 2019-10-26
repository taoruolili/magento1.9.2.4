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
class Magegiant_Onestepcheckout_Model_System_Config_Source_Customer_Group
{
    const CUSTOMER_GROUP_ALL            = 'ALL';
    const CUSTOMER_GROUP_NOT_REGISTERED = 'NOT_REGISTERED';

    public function toOptionArray()
    {
        $res   = Mage::helper('customer')->getGroups()->toOptionArray();
        $found = false;
        foreach ($res as $group) {
            if ($group['value'] == 0) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            array_unshift(
                $res,
                array(
                    'value' => self::CUSTOMER_GROUP_NOT_REGISTERED,
                    'label' => Mage::helper('onestepcheckout')->__('Not registered')
                )
            );
        }

        array_unshift(
            $res, array('value' => self::CUSTOMER_GROUP_ALL, 'label' => Mage::helper('onestepcheckout')->__('All groups'))
        );

        return $res;
    }
}