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
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Model_Total_Invoice_Giftwrap extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect total when create Invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getGiantGiftwrapAmount() < 0.0001) {
            return;
        }
        $invoice->setGiantGiftwrapBaseAmount(0);
        $invoice->setGiantGiftwrapAmount(0);
        $totalGiftwrapAmount     = 0;
        $totalGiftwrapBaseAmount = 0;
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }
            $itemQty       = $item->getQty();
            $giftwrapBaseAmount = $orderItem->getGiantGiftwrapBaseAmount() * $itemQty;
            $giftwrapAmount     = $orderItem->getGiantGiftwrapAmount() * $itemQty;
            $item->setGiantGiftwrapBaseAmount($giftwrapBaseAmount);
            $item->setGiantGiftwrapAmount($giftwrapAmount);
            $totalGiftwrapBaseAmount += $giftwrapBaseAmount;
            $totalGiftwrapAmount += $giftwrapAmount;
        }
        $invoice->setGiantGiftwrapBaseAmount($totalGiftwrapBaseAmount);
        $invoice->setGiantGiftwrapAmount($totalGiftwrapAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalGiftwrapAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $totalGiftwrapBaseAmount);

        return $this;
    }

}
