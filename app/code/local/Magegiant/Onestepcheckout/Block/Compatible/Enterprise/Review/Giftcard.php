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
class Magegiant_Onestepcheckout_Block_Compatible_Enterprise_Review_Giftcard extends Magegiant_Onestepcheckout_Block_Container
{
    public function canShow()
    {
        if (Mage::helper('onestepcheckout/enterprise_giftcard')->isGiftcardEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getApplyEnterpriseGiftcardAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/enterprise_ajax/applyEnterpriseGiftcard', array('_secure' => true));
    }

    /**
     * @return string
     */
    public function getRemoveEnterpriseGiftcardAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/enterprise_ajax/removeEnterpriseGiftcard', array('_secure' => true));
    }
}