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


class Magegiant_Onestepcheckout_Model_System_Config_Source_Payment_Methods
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $paymentMethodsOptionArray = array(
            array(
                'label' => '',
                'value' => '',
            )
        );
        $paymentMethodsList = Mage::getModel('payment/config')->getActiveMethods();
        ksort($paymentMethodsList);
        foreach ($paymentMethodsList as $paymentMethodCode => $paymentMethod) {
            if ($paymentMethodCode == 'googlecheckout') {
                continue;
            }
            $paymentMethodsOptionArray[] = array(
                'label' => $paymentMethod->getTitle(),
                'value' => $paymentMethodCode,
            );
        }
        return $paymentMethodsOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $paymentMethodsArray = array();
        $paymentMethodsList = Mage::getModel('payment/config')->getActiveMethods();
        ksort($paymentMethodsList);
        foreach ($paymentMethodsList as $paymentMethodCode => $paymentMethod) {
            $paymentMethodsArray[$paymentMethodCode] = $paymentMethod->getTitle();
        }
        return $paymentMethodsArray;
    }
}