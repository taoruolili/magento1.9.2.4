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
class Magegiant_Onestepcheckout_Block_Container_Form_Address extends Magegiant_Onestepcheckout_Block_Container
{
    protected $_taxvat;
    protected $_billingAddress;
    protected $_shippingAddress;

    public function getAddressHelper()
    {
        return Mage::helper('onestepcheckout/address');
    }


    public function getGoogleSpecificCountry($store = null)
    {
        return $this->getHelperConfig()->getGoogleSpecificCountry($store);
    }

    /**
     * get billing address data
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        if (is_null($this->_billingAddress)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_billingAddress = $this->getQuote()->getBillingAddress();
                if (!$this->_billingAddress->getFirstname()) {
                    $this->_billingAddress->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if (!$this->_billingAddress->getLastname()) {
                    $this->_billingAddress->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_billingAddress = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_billingAddress;
    }

    /**
     * get Shipping Address Data
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        if (is_null($this->_shippingAddress)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_shippingAddress = $this->getQuote()->getShippingAddress();
            } else {
                $this->_shippingAddress = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_shippingAddress;
    }

    public function getAddressTriggerElements()
    {
        $triggers = array();
        if ($addressTrigger = $this->getHelperConfig()->getAddressTriggerElements())
            $triggers = explode(',', $addressTrigger);

        return $triggers;
    }

    public function getShippingTriggerElements()
    {
        $triggers = array();
        foreach ($this->getAddressTriggerElements() as $element) {
            $triggers[] = 'shipping:' . $element;
        }

        return Mage::helper('core')->jsonEncode($triggers);
    }

    public function getWidgetHtml($name, $type = null, $template = null)
    {
        switch ($name) {
            case 'name':
                $version = '';
                if (version_compare(Mage::getVersion(), '1.7.0.0', '<')) {
                    $version = '1.6/';
                }
                if ($type == 'billing') {
                    $html = $this->getLayout()
                        ->createBlock('customer/widget_name')
                        ->setTemplate('magegiant/onestepcheckout/container/form/address/customer/widget/' . $version . $template . '.phtml')
                        ->setObject($this->_getObjectForCustomerNameWidget())
                        ->setForceUseCustomerRequiredAttributes(!$this->isCustomerLoggedIn())
                        ->setFieldIdFormat('billing:%s')
                        ->setFieldNameFormat('billing[%s]')
                        ->toHtml();
                } else {
                    $html = $this->getLayout()
                        ->createBlock('customer/widget_name')
                        ->setTemplate('magegiant/onestepcheckout/container/form/address/customer/widget/' . $version . 'name.phtml')
                        ->setObject($this->_getObjectForCustomerNameWidget())
                        ->setFieldIdFormat('shipping:%s')
                        ->setFieldNameFormat('shipping[%s]')
                        ->setFieldParams('onchange="shipping.setSameAsBilling(false)"')
                        ->toHtml();
                }

                break;
            case 'dob':
                $html = $this->getLayout()
                    ->createBlock('customer/widget_dob')
                    ->setDate($this->_getDateForDOBWidget())
                    ->setFieldIdFormat('billing:%s')
                    ->setFieldNameFormat('billing[%s]')
                    ->toHtml();
                break;
            case 'gender':
                $genderBlock = $this->getLayout()
                    ->createBlock('customer/widget_gender')
                    ->setGender($this->getBillingDataFromSession('gender'))
                    ->setFieldIdFormat('billing:%s')
                    ->setFieldNameFormat('billing[%s]');
                if ($genderBlock->isEnabled())
                    $html = $genderBlock->toHtml();
                else $html = '';
                break;
            case 'country':
                $countryId = $this->getDataFromSession('country_id');
                if (is_null($countryId)) {
                    $countryId = Mage::helper('onestepcheckout/config')->getDefaultCountryId();
                }
                $countryBlock = $this->getLayout()->createBlock('core/html_select')
                    ->setName($type . '[country_id]')
                    ->setId($type . ':country_id')
                    ->setTitle($this->__('Country'))
                    ->setClass('validate-select')
                    ->setValue($countryId)
                    ->setOptions($this->getCountryOptions());
                $html         = $countryBlock->getHtml();
                break;
            case 'taxvat':
                $html = $this->getCustomerWidgetTaxvat()
                    ->setTaxvat($this->getDataFromSession('taxvat'))
                    ->setFieldIdFormat('billing:%s')
                    ->setFieldNameFormat('billing[%s]')
                    ->toHtml();
                break;
            default:
                $html = '';

        }

        return $html;
    }

    /**
     * Get Customer Taxvat Widget block
     *
     * @return Mage_Customer_Block_Widget_Taxvat
     */
    protected function getCustomerWidgetTaxvat()
    {
        if (!$this->_taxvat) {
            $this->_taxvat = $this->getLayout()->createBlock('customer/widget_taxvat');
        }

        return $this->_taxvat;
    }

    public function allowShipToDifferentChecked()
    {
        if (Mage::getSingleton('checkout/session')->getData('same_as_billing') == 2) {
            return 2;
        }
        if ($address = $this->getQuote()->getShippingAddress()) {
            return $this->getQuote()->getShippingAddress()->getData('same_as_billing');
        }

        return false;
    }

    public function getAttributeValidationClass($attributeCode)
    {
        $helper          = Mage::helper('customer/address');
        $validationClass = '';
        if (method_exists($helper, 'getAttributeValidationClass')) {
            $validationClass = $helper->getAttributeValidationClass($attributeCode);
        }
        $requireFields = $this->getAddressHelper()->getAddressRequireFields();
        if (array_key_exists($attributeCode, $requireFields)) {
            $validationClass = $requireFields[$attributeCode];
        }

        return $validationClass;
    }

    public function getBillingDataFromSession($path)
    {
        $formData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values/billing');
        if (!empty($formData[$path])) {
            return $formData[$path];
        }

        return null;
    }

    public function getShippingDataFromSession($path)
    {
        $formData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values/shipping');
        if (!empty($formData[$path])) {
            return $formData[$path];
        }

        return null;
    }

    public function customerMustBeRegistered()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
        || Mage::helper('checkout')->isAllowedGuestCheckout($this->getQuote());
    }

    protected function _getObjectForCustomerNameWidget()
    {
        $formData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
        $address  = Mage::getModel('sales/quote_address');
        if (isset($formData['billing'])) {
            $address->addData($formData['billing']);
        }
        if ($address->getFirstname() || $address->getLastname()) {
            return $address;
        }

        return $this->getQuote()->getCustomer();
    }

    protected function _getDateForDOBWidget()
    {
        $formData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
        if (isset($formData['billing'])) {
            $billing = $formData['billing'];
            if (!empty($billing['year']) && !empty($billing['month']) && !empty($billing['day'])) {
                $zDate = new Zend_Date(array(
                    'year'  => $billing['year'],
                    'month' => $billing['month'],
                    'day'   => $billing['day'],
                ));

                return $zDate->toString();
            }
        }

        return '';
    }

    /**
     * Customer Taxvat Widget block
     *
     * @var Mage_Customer_Block_Widget_Taxvat
     */

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }

            $addressDetails = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
            if (isset($addressDetails[$type . '_address_id'])) {
                if (empty($addressDetails[$type . '_address_id'])) {
                    $addressId = 0;
                } else {
                    $addressId = $addressDetails[$type . '_address_id'];
                }
            } else {
                $addressId = $this->getQuote()->getBillingAddress()->getCustomerAddressId();
            }
            if (empty($addressId) && $addressId !== 0) {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
                if ($address) {
                    $addressId = $address->getId();
                }
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type . '_address_id')
                ->setId($type . '-address-select')
                ->setClass('address-select')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }

        return '';
    }

    public function isVatAttributeVisible()
    {
        $helper = Mage::helper('customer/address');
        if (method_exists($helper, 'isVatAttributeVisible')) {
            return $helper->isVatAttributeVisible();
        }

        return false;
    }

    public function getAddressShippingMethod()
    {
        return $this->getQuote()->getShippingAddress()->getShippingMethod();
    }

    public function getUseForShipping()
    {
        if (Mage::getSingleton('checkout/session')->getData('same_as_billing') == 2) {
            return 2;
        }
        if ($address = $this->getQuote()->getShippingAddress()) {
            return $this->getQuote()->getShippingAddress()->getData('same_as_billing');
        }

        return false;
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getQuote()->getShippingAddress()), true);
    }

    /**
     * Check whether taxvat is enabled
     *
     * @return bool
     */
    public function isTaxvatEnabled()
    {
        return $this->getCustomerWidgetTaxvat()->isEnabled();
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/' . $carrierCode . '/title')) {
            return $name;
        }

        return $carrierCode;
    }

    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

}