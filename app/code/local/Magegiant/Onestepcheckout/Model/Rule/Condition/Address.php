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
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */
class Magegiant_Onestepcheckout_Model_Rule_Condition_Address extends Mage_Rule_Model_Condition_Abstract
{
    protected $_addressAttributes = array(
        'weight',
        'shipping_method',
        'postcode',
        'region',
        'region_id',
        'country_id'
    );

    public function loadAttributeOptions()
    {
        $helper = Mage::helper('onestepcheckout');

        $attributes = array(
            'base_subtotal'   => $helper->__('Subtotal'),
            'items_qty'       => $helper->__('Total Items Quantity'),
            'weight'          => $helper->__('Total Weight'),
            'method'          => $helper->__('Payment Method'),
            'shipping_method' => $helper->__('Shipping Method'),
            'postcode'        => $helper->__('Shipping Postcode'),
            'region'          => $helper->__('Shipping Region'),
            'region_id'       => $helper->__('Shipping State/Province'),
            'country_id'      => $helper->__('Shipping Country'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'weight':
            case 'items_qty':
                return 'numeric';

            case 'shipping_method':
            case 'method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'method':
            case 'country_id':
            case 'region_id':
                return 'select';
        }
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
                        ->toOptionArray();
                    break;

                case 'method':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * Fix issue when validate address
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $addressObject     = $object;
        $addressAttributes = $this->_addressAttributes;
        if ($this->getAttribute() == 'method') {
            $addressObject = $object->getPayment();
        }
        if (in_array($this->getAttribute(), $addressAttributes)) {
            if ($object->isVirtual()) {
                $addressObject = $object->getBillingAddress();
            } else {
                $addressObject = $object->getShippingAddress();
            }

            try {
                $countryId = $addressObject->getCountryId();
                if (!is_null($countryId)) {
                    $numOfCountryRegions = count(
                        Mage::getModel('directory/country')
                            ->loadByCode($countryId)
                            ->getRegions()
                            ->getData()
                    );

                    if ($numOfCountryRegions == 0) {
                        $addressObject->setRegionId('0');
                    }
                }
            } catch (Exception $e) {
            }
        }

        return parent::validate($addressObject);
    }
}