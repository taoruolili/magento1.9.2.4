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
class Magegiant_Onestepcheckout_Helper_Giftwrap extends Mage_Core_Helper_Data
{
    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabled($store = null)
    {
        return Mage::helper('onestepcheckout/config')->isEnabledGiftWrap($store);
    }

    /**
     * get current checkout quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getGiftWrapAmount($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        $items       = $quote->getAllItems();
        $total_items = 0;
        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $total_items += $item->getQty();
        }
        $giftWrapType   = Mage::helper('onestepcheckout/config')->getGiftWrapType();
        $giftWrapAmount = Mage::helper('onestepcheckout/config')->getOrderGiftwrapAmount();
        if (!$total_items)
            return 0;
        if ($giftWrapType == Magegiant_Onestepcheckout_Model_System_Config_Source_Giftwrap::PER_ITEM) {
            $giftWrapAmount *= $total_items;
        }
        $this->_addGiftWrapToItems($quote, $giftWrapAmount / $total_items);

        return $giftWrapAmount;
    }

    protected function _addGiftWrapToItems($quote, $giftWrapBaseAmount)
    {
        $items          = $quote->getAllItems();
        $giftWrapAmount = $quote->getStore()->convertPrice($giftWrapBaseAmount);
        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $item->setGiantGiftwrapBaseAmount($item->getGiantGiftwrapBaseAmount() + $giftWrapBaseAmount);
            $item->setGiantGiftwrapAmount($item->getGiantGiftwrapAmount() + $giftWrapAmount);
        }
    }
}