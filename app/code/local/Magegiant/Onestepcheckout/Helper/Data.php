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
class Magegiant_Onestepcheckout_Helper_Data extends Magegiant_Onestepcheckout_Helper_Eav_Data
{

    const BLOCK_NUMBER_STORAGE_KEY = 'giant-osc-block-number';
    protected $currentNumber = '';

    public function isCustomerMustBeLogged()
    {
        $helper = Mage::helper('checkout');
        if (method_exists($helper, 'isCustomerMustBeLogged')) {
            return $helper->isCustomerMustBeLogged();
        }

        return false;
    }

    public function getGrandTotal($quote)
    {
        $grandTotal = $quote->getGrandTotal();

        return Mage::app()->getStore()->getCurrentCurrency()->format($grandTotal, array(), false);
    }
    /*
     *Customer attribute
     *
    */
    /**
     * Return available customer attribute form as select options
     *
     * @throws Mage_Core_Exception
     */
    public function getAttributeFormOptions()
    {
        Mage::throwException(Mage::helper('onestepcheckout')->__('Use helper with defined EAV entity'));
    }

    /**
     * Default attribute entity type code
     *
     * @throws Mage_Core_Exception
     */
    protected function _getEntityTypeCode()
    {
        Mage::throwException(Mage::helper('onestepcheckout')->__('Use helper with defined EAV entity'));
    }

    /**
     * Return available customer attribute form as select options
     *
     * @return array
     */
    public function getCustomerAttributeFormOptions()
    {
        return Mage::helper('onestepcheckout/attribute_customer')->getAttributeFormOptions();
    }

    /**
     * Return available customer address attribute form as select options
     *
     * @return array
     */
    public function getCustomerAddressAttributeFormOptions()
    {
        return Mage::helper('onestepcheckout/attribute_address')->getAttributeFormOptions();
    }

    /**
     * Returns array of user defined attribute codes for customer entity type
     *
     * @return array
     */
    public function getCustomerUserDefinedAttributeCodes()
    {
        return Mage::helper('onestepcheckout/attribute_customer')->getUserDefinedAttributeCodes();
    }

    /**
     * Returns array of user defined attribute codes for customer address entity type
     *
     * @return array
     */
    public function getCustomerAddressUserDefinedAttributeCodes()
    {
        return Mage::helper('onestepcheckout/attribute_address')->getUserDefinedAttributeCodes();
    }

    public function getAttributeFrontendLabel($attribute_code, $entity_type = 1)
    {
        return Mage::getSingleton('eav/entity_attribute')->loadByCode($entity_type, $attribute_code)->getFrontendLabel();
    }

    /**
     * @param        $extensionName
     * @param        $extVersion
     * @param string $operator
     * @return bool|mixed
     */
    public function checkExtensionVersion($extensionName, $extVersion, $operator = '>=')
    {
        if ($this->isExtensionInstalled($extensionName)
            && ($version = Mage::getConfig()->getModuleConfig($extensionName)->version)
        ) {
            return version_compare($version, $extVersion, $operator);
        }

        return false;
    }

    /**
     * Removes empty values from the array given
     *
     * @param mixed $data Array to inspect or data to be placed in new array as first value
     * @return array Array processed
     */
    public static function noEmptyValues($data)
    {
        $result = array();
        if (is_array($data)) {
            foreach ($data as $a) {
                if ($a) {
                    $result[] = $a;
                }
            }
        } else {
            $result = $data ? array() : array($data);
        }

        return $result;
    }

    public function getNumbering($increment = true)
    {
        $configHelper = Mage::helper('onestepcheckout/config');
        if (!$configHelper->isShowNumbering()) {
            return false;
        }
        if (!$this->currentNumber) {
            $this->currentNumber = 0;
        }
        $this->currentNumber++;
        if ($increment) {
            $this->currentNumber++;
        }

        return $this->currentNumber;
    }

    public function formatPrice($value)
    {
        return Mage::app()->getStore()->formatPrice($value);
    }
}