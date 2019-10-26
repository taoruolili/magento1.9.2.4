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


class Magegiant_Onestepcheckout_Model_System_Config_Source_Shipping_Methods
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $shippingMethodsOptionArray = array(
            array(
                'label' => '',
                'value' => '',
            )
        );
        $carrierMethodsList = Mage::getSingleton('shipping/config')->getActiveCarriers();
        ksort($carrierMethodsList);
        foreach ($carrierMethodsList as $carrierMethodCode => $carrierModel) {
            foreach ($carrierModel->getAllowedMethods() as $shippingMethodCode => $shippingMethodTitle) {
                $shippingMethodsOptionArray[] = array(
                    'label' => $this->_getShippingMethodTitle($carrierMethodCode) . ' - ' . $shippingMethodTitle,
                    'value' => $carrierMethodCode . '_' . $shippingMethodCode,
                );
            }
        }
        return $shippingMethodsOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $shippingMethodsArray = array();
        $carrierMethodsList = Mage::getSingleton('shipping/config')->getActiveCarriers();
        ksort($carrierMethodsList);
        foreach ($carrierMethodsList as $carrierMethodCode => $carrierModel) {
            foreach ($carrierModel->getAllowedMethods() as $shippingMethodCode => $shippingMethodTitle) {
                $shippingCode = $carrierMethodCode . '_' . $shippingMethodCode;
                $shippingTitle = $this->_getShippingMethodTitle($carrierMethodCode) . ' - ' . $shippingMethodTitle;
                $shippingMethodsArray[$shippingCode] = $shippingTitle;
            }
        }
        return $shippingMethodsArray;
    }

    protected function _getShippingMethodTitle($shippingMethodCode)
    {
        if (!$shippingMethodTitle = Mage::getStoreConfig("carriers/$shippingMethodCode/title")) {
            $shippingMethodTitle = $shippingMethodCode;
        }
        return $shippingMethodTitle;
    }
}