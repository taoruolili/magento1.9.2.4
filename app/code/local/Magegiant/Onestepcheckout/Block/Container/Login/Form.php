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
class Magegiant_Onestepcheckout_Block_Container_Login_Form extends Magegiant_Onestepcheckout_Block_Container
{
    public function canShow()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }

        return true;
    }

    public function getLoginUrl()
    {

        return Mage::getUrl('onestepcheckout/ajax/login', array('_secure' => $this->isSecure()));
    }

    public function getForgotUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/forgotPassword', array('_secure' => $this->isSecure()));
    }

}