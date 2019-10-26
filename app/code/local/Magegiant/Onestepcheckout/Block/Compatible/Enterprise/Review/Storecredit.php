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
class Magegiant_Onestepcheckout_Block_Compatible_Enterprise_Review_Storecredit extends Magegiant_Onestepcheckout_Block_Container
{
    public function canShow()
    {
        if (Mage::helper('onestepcheckout/enterprise_storecredit')->isStoreCreditEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * Check Block is enabled
     *
     * @return mixed
     */
    public function isEnabledStoreCreditBlock()
    {
        return Mage::helper('onestepcheckout/enterprise_storecredit')->isEnabledStoreCreditBlock();
    }

    public function getApplyStorecreditAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/enterprise_ajax/applyEnterpriseStorecredit', array('_secure' => $this->isSecure()));
    }

    /**
     * Get customer Store Credit Balance
     *
     * @return mixed
     */
    public function getBalance()
    {
        return Mage::helper('onestepcheckout/enterprise_storecredit')->getBalance();
    }

    /**
     * Check balance is used
     *
     * @return mixed
     */
    public function isCustomerBalanceUsed()
    {
        return Mage::helper('onestepcheckout/enterprise_storecredit')->isCustomerBalanceUsed();
    }

    public function formatPrice($value)
    {
        return $this->getHelperData()->formatPrice($value);
    }

}