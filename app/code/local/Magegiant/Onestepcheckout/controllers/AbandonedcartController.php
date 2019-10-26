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
class Magegiant_Onestepcheckout_AbandonedcartController extends Mage_Checkout_Controller_Action
{
    public function indexAction()
    {
        $quote_id = $this->getRequest()->getParam('code');
        if (!$quote_id) {
            $this->getResponse()->setRedirect(Mage::getBaseUrl());
        } else {
            $quote = Mage::getModel('sales/quote')->load($quote_id);
            Mage::getSingleton('checkout/session')->replaceQuote($quote);
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($quote->getCustomerEmail());
            if ($customerId = $customer->getId()) {
                $session = Mage::getSingleton('customer/session');
                if ($session->isLoggedIn() && $customerId != $session->getCustomerId()) {
                    $session->logout();
                }
                try {
                    $session->setCustomerAsLoggedIn($customer);
                } catch (Exception $ex) {
                    Mage::getSingleton('core/session')->addError($this->__("Your account isn't confirmed"));
                    $this->_redirect('/');
                }
            }
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
        }

        return;
    }
}