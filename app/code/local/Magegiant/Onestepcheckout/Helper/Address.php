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
class Magegiant_Onestepcheckout_Helper_Address extends Mage_Core_Helper_Data
{

    const TEMPLATE_PATH = 'magegiant/onestepcheckout/';
    const EVENT_PREFIX  = 'magegiant_one_step_checkout_';
    protected $address_require_fields = array(
        'telephone' => 'required-entry',
        'postcode'  => 'required-entry',
        'city'      => 'required-entry',
        'street'    => 'required-entry',
    );

    public function getAddressRequireFields()
    {
        return $this->address_require_fields;
    }

    public function setAddressRequireFields($fields)
    {
        $this->address_require_fields = $fields;
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function validateAddressData($data)
    {
        $validationErrors = array();
        $requiredFields   = array(
            'country_id',
            'city',
            'postcode',
            'region_id',
        );
        foreach ($requiredFields as $requiredField) {
            if (!isset($data[$requiredField])) {
                $validationErrors[] = $this->__("Field %s is required", $requiredField);
            }
        }

        return $validationErrors;
    }

    public function getAddressTemplate()
    {
        $template = new Varien_Object(array(
            'file_path' => 'container/form/address.phtml'
        ));
        Mage::dispatchEvent(self::EVENT_PREFIX . 'get_address_template_before', array(
            'template' => $template
        ));

        return self::TEMPLATE_PATH . $template->getFilePath();
    }

    public function getBillingTemplate()
    {
        $template = new Varien_Object(array(
            'file_path' => 'container/form/address/billing.phtml'
        ));
        Mage::dispatchEvent(self::EVENT_PREFIX . 'get_billing_address_template_before', array(
            'template' => $template
        ));

        return self::TEMPLATE_PATH . $template->getFilePath();
    }

    public function getShippingTemplate()
    {
        $template = new Varien_Object(array(
            'file_path' => 'container/form/address/shipping.phtml'
        ));
        Mage::dispatchEvent(self::EVENT_PREFIX . 'get_shipping_address_template_before', array(
            'template' => $template
        ));

        return self::TEMPLATE_PATH . $template->getFilePath();

    }

    public function setDefaultShippingMethod($shipping, $address)
    {
        if (count($shipping) > 1
            && $defaultShippingMethod = Mage::helper('onestepcheckout/config')->getDefaultShippingMethod()
        ) {
            $address->setShippingMethod($defaultShippingMethod);
        }
    }

    public function saveBilling($data, $customerAddressId = null)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }

        $address = $this->getQuote()->getBillingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array(
                        'error'   => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.'),
                    );
                }
                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                /*
                 *$addressErrors = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
                */
            }
        } else {
            if (@class_exists('Mage_Customer_Model_Form')) {
                $addressForm->setEntity($address);
                $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
                /*
                 $addressErrors = $addressForm->validateData($addressData);
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => array_values($addressErrors));
                }
                */
                $addressForm->compactData($addressData);
                foreach ($addressForm->getAttributes() as $attribute) {
                    if (!isset($data[$attribute->getAttributeCode()])) {
                        $address->setData($attribute->getAttributeCode(), null);
                    }
                }
                $address->setCustomerAddressId(null);
                // Additional form data, not fetched by extractData (as it fetches only attributes)
                $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            } else {
                $address->addData($data);
            }
        }
        $address->implodeStreetAddress();

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping       = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = array('customer_address_id');
                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                            && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping
                        ->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setShippingMethod($shippingMethod);
                    break;
            }
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        return array();
    }

    public function saveShipping($data, $customerAddressId = null)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array(
                        'error'   => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.'),
                    );
                }
                $address->importCustomerAddress($customerAddress);
                $addressForm->setEntity($address);
                /*
                $addressErrors = $addressForm->validateData($address->getData());
                 if ($addressErrors !== true) {
                     return array('error' => 1, 'message' => $addressErrors);
                 }
                */
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
            /*
            $addressErrors = $addressForm->validateData($addressData);
             if ($addressErrors !== true) {
                 return array('error' => 1, 'message' => $addressErrors);
             }
            */
            $addressForm->compactData($addressData);
            // unset shipping address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), null);
                }
            }

            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            $address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
        }
        $address->implodeStreetAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        return array();
    }


}