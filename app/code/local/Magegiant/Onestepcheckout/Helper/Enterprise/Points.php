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
class Magegiant_Onestepcheckout_Helper_Enterprise_Points extends Mage_Core_Helper_Data
{
    protected $_pointsBlock;

    /**
     * Check is Points & Rewards enabled
     */
    public function isPointsEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_Reward') && $this->isModuleOutputEnabled('Enterprise_Reward')) {
            if (Mage::helper('enterprise_reward')->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * get Points Unit Name
     *
     * @return string
     */
    public function getPointsUnitName()
    {
        return $this->__('Reward points');
    }

    /**
     * @return mixed
     */
    public function isEnabledPointsBlock()
    {
        return $this->_getPointsBlock()->getCanUseRewardPoints();
    }

    /**
     * get Enterprise Reward Points Block
     *
     * @return mixed
     */
    protected function _getPointsBlock()
    {
        if (!$this->_pointsBlock) {
            $this->_pointsBlock = Mage::app()->getLayout()->createBlock('enterprise_reward/checkout_payment_additional');
        }

        return $this->_pointsBlock;
    }

    /**
     * get points summary
     *
     * @return mixed
     */
    public function getSummaryForCustomer()
    {
        return $this->_getPointsBlock()->getPointsBalance();
    }

    /**
     * get money for points
     *
     * @return mixed
     */
    public function getMoneyForPoints()
    {
        return $this->_getPointsBlock()->getCurrencyAmount();
    }

    /**
     * @return mixed
     */
    public function useRewardPoints()
    {
        return $this->_getPointsBlock()->useRewardPoints();
    }

}