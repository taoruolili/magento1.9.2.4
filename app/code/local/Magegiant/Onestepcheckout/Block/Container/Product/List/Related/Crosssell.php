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
class Magegiant_Onestepcheckout_Block_Container_Product_List_Related_Crosssell extends Magegiant_Onestepcheckout_Block_Container_Product_List_Related_Abstract
{
    public function getUrlToAddProductToWishlist()
    {
        $isSecure = Mage::app()->getStore()->isCurrentlySecure();

        return Mage::getUrl(
            'onestepcheckout/ajax/addProductToWishlist',
            array(
                '_secure'  => $isSecure,
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            )
        );
    }

    public function getUrlToAddProductToCompareList()
    {
        $isSecure = Mage::app()->getStore()->isCurrentlySecure();

        return Mage::getUrl(
            'onestepcheckout/ajax/addProductToCompareList',
            array(
                '_secure'  => $isSecure,
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            )
        );
    }
}