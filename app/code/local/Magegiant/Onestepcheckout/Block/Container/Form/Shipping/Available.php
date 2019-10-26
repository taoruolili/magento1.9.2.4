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
class Magegiant_Onestepcheckout_Block_Container_Form_Shipping_Available extends Magegiant_Onestepcheckout_Block_Container_Form_Shipping
{
    protected $_rates = null;
    protected $_address = null;

    /**
     * get all shipping rate
     *
     * @return array|null
     */
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getShippingAddress()->collectShippingRates()->save();
            $groups = $this->getShippingAddress()->getGroupedAllShippingRates();

            return $this->_rates = $groups;
        }
        /* Don't show collect in store rate as an available option. */
        if (!Mage::getStoreConfig('carriers/collectinstore/onestep') && Mage::getStoreConfig('carriers/collectinstore/active') && array_key_exists('collectinstore', $this->_rates)) {
            unset($this->_rates['collectinstore']);
        }

        return $this->_rates;
    }

    /**
     * get Shipping Address From Quote
     *
     * @return Mage_Sales_Model_Quote_Address|null
     */
    public function getShippingAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }

        return $this->_address;
    }

    /**
     * get default shipping method
     *
     * @return mixed
     */
    public function getDefaultShippingMethod()
    {
        return $this->getHelperConfig()->getDefaultShippingMethod();
    }

}