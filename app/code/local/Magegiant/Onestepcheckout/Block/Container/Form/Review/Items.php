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
class Magegiant_Onestepcheckout_Block_Container_Form_Review_Items extends Mage_Checkout_Block_Onepage_Review_Info
{
    /**
     * get ajax cart url: (remove item, add a item, sub a item )
     *
     * @return string
     */
    public function getAjaxCartItemUrl()
    {
        $isSecure = Mage::app()->getStore()->isCurrentlySecure();

        return Mage::getUrl('onestepcheckout/ajax/ajaxCartItem', array('_secure' => $isSecure));
    }
}