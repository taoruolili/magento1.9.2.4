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
class Magegiant_Onestepcheckout_Model_Sales_Quote_Rewrite_Address extends Mage_Sales_Model_Quote_Address
{
    /**
     * Retreive errors
     *
     * @return array
     */
    protected function _getErrors()
    {
        return $this->_errors;
    }

    /**
     * Reset errors array
     *
     * @return Mage_Customer_Model_Address_Abstract
     */
    protected function _resetErrors()
    {
        $this->_errors = array();

        return $this;
    }

    /**
     * Validate address attribute values
     *
     * @return array | bool
     */
    public function validate()
    {
        $this->_resetErrors();

        $this->implodeStreetAddress();

        $this->_basicCheck();

        if (!$this->_getErrors()) {
            Mage::dispatchEvent('customer_address_validation_after', array('address' => $this));
        }

        $errors = $this->_getErrors();

        $this->_resetErrors();

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }

        return $errors;
    }

    /**
     * Perform basic validation
     *
     * @return void
     */
    protected function _basicCheck()
    {
        $attribute = Mage::getModel('customer/attribute');
        $firstname = $attribute->loadByCode($this->_getEntityType()->getId(), 'firstname');
        if ($firstname->getIsRequired() && !Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $this->addError(Mage::helper('customer')->__('Please enter the first name.'));
        }
        $lastname = $attribute->loadByCode($this->_getEntityType()->getId(), 'lastname');
        if ($lastname->getIsRequired() && !Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $this->addError(Mage::helper('customer')->__('Please enter the last name.'));
        }
        $street = $attribute->loadByCode($this->_getEntityType()->getId(), 'street');
        if ($street->getIsRequired() && !Zend_Validate::is($this->getStreet(1), 'NotEmpty')) {
            $this->addError(Mage::helper('customer')->__('Please enter the street.'));
        }
        $city = $attribute->loadByCode($this->_getEntityType()->getId(), 'city');
        if ($city->getIsRequired() && !Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            $this->addError(Mage::helper('customer')->__('Please enter the city.'));
        }
        $telephone = $attribute->loadByCode($this->_getEntityType()->getId(), 'telephone');
        if ($telephone->getIsRequired() && !Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
            $this->addError(Mage::helper('customer')->__('Please enter the telephone number.'));
        }
        $postcode           = $attribute->loadByCode($this->_getEntityType()->getId(), 'postcode');
        $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
        if ($postcode->getIsRequired() && !in_array($this->getCountryId(), $_havingOptionalZip)
            && !Zend_Validate::is($this->getPostcode(), 'NotEmpty')
        ) {
            $this->addError(Mage::helper('customer')->__('Please enter the zip/postal code.'));
        }
        $counttryId = $attribute->loadByCode($this->_getEntityType()->getId(), 'country_id');
        if ($counttryId->getIsRequired() && !Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $this->addError(Mage::helper('customer')->__('Please enter the country.'));
        }
        $region = $attribute->loadByCode($this->_getEntityType()->getId(), 'region');
        if ($region->getIsRequired() && $this->getCountryModel()->getRegionCollection()->getSize()
            && !Zend_Validate::is($this->getRegionId(), 'NotEmpty')
            && Mage::helper('directory')->isRegionRequired($this->getCountryId())
        ) {
            $this->addError(Mage::helper('customer')->__('Please enter the state/province.'));
        }
    }

    protected function _getEntityType()
    {
        if (is_null($this->_entityType)) {
            $this->_entityType = Mage::getSingleton('eav/config')->getEntityType('customer_address');
        }

        return $this->_entityType;
    }
}