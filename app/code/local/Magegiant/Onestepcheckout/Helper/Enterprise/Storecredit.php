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
class Magegiant_Onestepcheckout_Helper_Enterprise_Storecredit extends Mage_Core_Helper_Data
{
    protected $_storeCreditBlock;

    /**
     * Check is Points & Rewards enabled
     */
    public function isStoreCreditEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_CustomerBalance')
            && $this->isModuleOutputEnabled('Enterprise_CustomerBalance')
        ) {
            if (Mage::helper('enterprise_customerbalance')->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return mixed
     */
    public function isEnabledStoreCreditBlock()
    {
        return $this->getStoreCreditBlock()->isAllowed();
    }

    public function getStoreCreditBlock()
    {
        if (!$this->_storeCreditBlock) {
            $this->_storeCreditBlock = Mage::app()->getLayout()->createBlock(
                'enterprise_customerbalance/checkout_onepage_payment_additional'
            );
        }

        return $this->_storeCreditBlock;
    }

    public function isCustomerBalanceUsed()
    {
        return $this->getStoreCreditBlock()->isCustomerBalanceUsed();
    }

    public function getBalance()
    {
        return $this->getStoreCreditBlock()->getBalance();
    }
}