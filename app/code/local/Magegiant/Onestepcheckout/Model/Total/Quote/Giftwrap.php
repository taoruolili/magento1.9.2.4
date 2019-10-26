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

/**
 * GiantPoints Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Model_Total_Quote_Giftwrap
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /**
     * collect reward points that customer earned (per each item and address) total
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote         $quote
     * @return Magegiant_Onestepcheckout_Model_Total_Quote_Point
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote           = $address->getQuote();
        $_giftWrapHelper = Mage::helper('onestepcheckout/giftwrap');
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $session = Mage::getSingleton('checkout/session');
        if (!$_giftWrapHelper->isEnabled($quote->getStoreId()) || !$session->getData('is_used_giftwrap')) {
            return $this;
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $giftWrapBaseAmount = $_giftWrapHelper->getGiftWrapAmount($quote);
        $giftWrapAmount     = $quote->getStore()->convertPrice($giftWrapBaseAmount);
        if ($giftWrapAmount > 0) {
            $address->setGiantGiftwrapBaseAmount($giftWrapBaseAmount);
            $address->setGiantGiftwrapAmount($giftWrapAmount);
            $address->setBaseGrandTotal($address->getGrandTotal() + $giftWrapBaseAmount);
            $address->setGrandTotal($address->getGrandTotal() + $giftWrapAmount);
        }
        Mage::dispatchEvent('onestepcheckout_collect_total_giftwrap_before', array(
            'address' => $address,
        ));

        return $this;
    }

    /**
     * fetch
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this|array
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getGiantGiftwrapAmount();
        if ($amount != 0) {
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => Mage::helper('sales')->__('Gift Wrap'),
                'value' => $amount
            ));
        }

        return $this;
    }
}
