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
class Magegiant_Onestepcheckout_Block_Compatible_Enterprise_Review_Points extends Magegiant_Onestepcheckout_Block_Container
{
    /**
     * Check Enterprise Reward Points is enable or not
     *
     * @return bool
     */
    public function canShow()
    {
        if (Mage::helper('onestepcheckout/enterprise_points')->isPointsEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function isEnabledPointsBlock()
    {
        return Mage::helper('onestepcheckout/enterprise_points')->isEnabledPointsBlock();
    }

    /**
     * @return string
     */
    public function getPointsUnitName()
    {
        return Mage::helper('onestepcheckout/enterprise_points')->getPointsUnitName();
    }

    /**
     * @return mixed
     */
    public function getSummaryForCustomer()
    {
        return Mage::helper('onestepcheckout/enterprise_points')->getSummaryForCustomer();
    }

    /**
     * @return mixed
     */
    public function getMoneyForPoints()
    {
        return Mage::helper('onestepcheckout/enterprise_points')->getMoneyForPoints();
    }

    /**
     * @return mixed
     */
    public function useRewardPoints()
    {
        return Mage::helper('onestepcheckout/enterprise_points')->useRewardPoints();
    }

    /**
     * @return string
     */
    public function getApplyPointsAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/enterprise_ajax/applyEnterprisePoints', array('_secure' => $this->isSecure()));
    }
}