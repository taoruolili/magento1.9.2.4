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
 * GiantPoints Spend for Order by Point Model (use to print invoice PDF)
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Model_Total_Pdf_Giftwrap extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        if (!$this->getAmount())
            return $this;
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }
        $label    = Mage::helper('onestepcheckout')->__($this->getTitle()). ':';
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total    = array(
            'amount'    => $amount,
            'label'     => $label,
            'font_size' => $fontSize
        );

        return array($total);
    }

    /**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getSource()->getGiantGiftwrapAmount();
    }
}
