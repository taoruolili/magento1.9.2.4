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
 * Onestepcheckout Spend for Order by Point Model
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Model_Total_Creditmemo_Giftwrap extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    /**
     * Collect total when create Creditmemo
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($creditmemo->getGrandTotal() == 0) {
            $creditmemo->setIsLastCreditmemo(false);
        }
        if ($order->getGiantGiftwrapAmount() < 0.0001) {
            return;
        }
        $creditmemo->setGiantGiftwrapBaseAmount(0);
        $creditmemo->setGiantGiftwrapAmount(0);
        $totalGiftwrapAmount     = 0;
        $totalGiftwrapBaseAmount = 0;
        /** @var $item Mage_Sales_Model_Order_Credimemo_Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }
            $itemQty            = $item->getQty();
            $giftwrapBaseAmount = $orderItem->getGiantGiftwrapBaseAmount() * $itemQty;
            $giftwrapAmount     = $orderItem->getGiantGiftwrapAmount() * $itemQty;
            $item->setGiantGiftwrapBaseAmount($giftwrapBaseAmount);
            $item->setGiantGiftwrapAmount($giftwrapAmount);
            $totalGiftwrapBaseAmount += $giftwrapBaseAmount;
            $totalGiftwrapAmount += $giftwrapAmount;
        }
        $creditmemo->setGiantGiftwrapBaseAmount($totalGiftwrapBaseAmount);
        $creditmemo->setGiantGiftwrapAmount($totalGiftwrapAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalGiftwrapAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $totalGiftwrapBaseAmount);
    }

    /**
     * check credit memo is last or not
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return boolean
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }

        return true;
    }
}
