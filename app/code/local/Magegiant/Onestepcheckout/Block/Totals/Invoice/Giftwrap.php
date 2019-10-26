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
 * Onestepcheckout Total Point Spend Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Block_Totals_Invoice_Giftwrap extends Mage_Core_Block_Template
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $invoice     = $totalsBlock->getInvoice();
        if ($invoice && $invoice->getGiantGiftwrapAmount() > 0.01) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'        => 'giant_giftwrap_label',
                'label'       => $this->__('Gift wrap'),
                'value'       => $invoice->getGiantGiftwrapAmount(),
                'is_formated' => false,
            )), 'subtotal');
        }
    }
}
