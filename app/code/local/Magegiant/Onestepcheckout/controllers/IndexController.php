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
class Magegiant_Onestepcheckout_IndexController extends Mage_Checkout_Controller_Action
{
    /**
     * @return Magegiant_Onestepcheckout_IndexController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();

        $checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($checkoutSessionQuote->getIsMultiShipping()) {
            $checkoutSessionQuote->setIsMultiShipping(false);
            $checkoutSessionQuote->removeAllAddresses();
        }

        if (!$this->_canShowForUnregisteredUsers()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            return;
        }

        return $this;
    }

    public function indexAction()
    {
        if (!Mage::helper('onestepcheckout/config')->isEnabled()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The one step checkout is disabled.'));
            $this->_redirect('checkout/cart');

            return;
        }
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');

            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');

            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();


        //Set billing and shipping data from session
        $currentData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
        if ($currentData && array_key_exists('billing', $currentData)) {
            if (isset($currentData['billing_address_id'])) {
                Mage::helper('onestepcheckout/address')->saveBilling($currentData['billing'], $currentData['billing_address_id']);
            }

            if (isset($currentData['billing']['use_for_shipping'])
                && $currentData['billing']['use_for_shipping'] == 0
                && isset($currentData['shipping_address_id'])
            ) {
                Mage::helper('onestepcheckout/address')->saveShipping($currentData['shipping'], $currentData['shipping_address_id']);
            }
        }

        $this->initAddress();
        $this->initShippingMethod();
        $this->initPaymentMethod();
        // Check default shipping method
        $shippingRates = $this->getShippingAddress()
            ->collectTotals()
            ->collectShippingRates()
            ->getAllShippingRates();
        //if single shipping rate available then apply it as shipping method
        if (count($shippingRates) == 1) {
            $shippingMethod = $shippingRates[0]->getCode();
            $this->getShippingAddress()->setShippingMethod($shippingMethod);
        }
        // Set Default Shipping Method
        Mage::helper('onestepcheckout/address')->setDefaultShippingMethod($shippingRates, $this->getShippingAddress());

        //Enterprise Giftwrap reset
        $wrappingInfo = array('gw_add_card' => false);
        if ($this->getShippingAddress()) {
            $this->getShippingAddress()->addData($wrappingInfo);
        }
        $this->getQuote()->addData($wrappingInfo);
        $this->getQuote()->setTotalsCollectedFlag(false);
        $this->getQuote()->collectTotals()->save();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('onestepcheckout/config')->getCheckoutTitle());
        $this->renderLayout();
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    /**
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    protected function _canShowForUnregisteredUsers()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
        || $this->getRequest()->getActionName() == 'index'
        || Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
        || !Mage::helper('onestepcheckout')->isCustomerMustBeLogged();
    }

    public function initAddress()
    {
        $helperAddress = Mage::helper('onestepcheckout/address');
        if ($this->getQuote()->getBillingAddress()->getCustomerAddressId()) {
            $data = array(
                'use_for_shipping' => true,
            );
            $helperAddress->saveBilling($data, $this->getQuote()->getBillingAddress()->getCustomerAddressId());
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->getQuote()->collectTotals();
            $this->getQuote()->save();

            return;
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($primaryBillingAddress = $customer->getPrimaryBillingAddress()) {
            $customerAddressId = $primaryBillingAddress->getId();
            $data              = array(
                'use_for_shipping' => true,
            );
            $helperAddress->saveBilling($data, $customerAddressId);
        } else {
            if (!is_null($this->getQuote()->getBillingAddress()->getId())) {
                return;
            }
            $data = array(
                'country_id'       => Mage::getStoreConfig('general/country/default'),
                'use_for_shipping' => true,
            );
            $helperAddress->saveBilling($data);
        }
    }

    /**
     * set shipping method for first load checkout page
     */
    public function initShippingMethod()
    {
        $helper = Mage::helper('onestepcheckout/shipping');
        if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
            $shippingRates = $helper->getShippingRates();
            if ((count($shippingRates) == 1)) {
                $currentShippingRate = current($shippingRates);
                if (count($currentShippingRate) == 1) {
                    $shippingRate   = current($currentShippingRate);
                    $shippingMethod = $shippingRate->getCode();
                }
            } elseif ($lastShippingMethod = $helper->getLastShippingMethod()) {
                $shippingMethod = $lastShippingMethod;
            } elseif ($defaultShippingMethod = Mage::helper('onestepcheckout/config')->getDefaultShippingMethod()) {
                $shippingMethod = $defaultShippingMethod;
            }
            if (isset($shippingMethod)) {
                $this->getOnepage()->saveShippingMethod($shippingMethod);
            }
        }
    }

    /**
     * set shipping method for first load checkout page
     */
    public function initPaymentMethod()
    {
        $helper = Mage::helper('onestepcheckout/payment');
        // check if payment saved to quote
        if (!$this->getQuote()->getPayment()->getMethod()) {
            $data           = array();
            $paymentMethods = $helper->getPaymentMethods();
            if ((count($paymentMethods) == 1)) {
                $currentPaymentMethod = current($paymentMethods);
                $data['method']       = $currentPaymentMethod->getCode();
            } elseif ($lastPaymentMethod = $helper->getLastPaymentMethod()) {
                $data['method'] = $lastPaymentMethod;
            } elseif ($defaultPaymentMethod = Mage::helper('onestepcheckout/config')->getDefaultPaymentMethod()) {
                $data['method'] = $defaultPaymentMethod;
            }
            if (!empty($data)) {
                try {
                    $this->getOnepage()->savePayment($data);
                } catch (Exception $e) {
                    // catch this exception
                }
            }
        }
    }

    public function addProductAction()
    {
        $products   = Mage::getModel('catalog/product')
            ->getCollection();
        $product_id = null;
        foreach ($products as $product) {
            $stock_item = $product->getStockItem();
            if ($stock_item && $stock_item->getIsInStock() == 1) {
                $product_id = $product->getId();
                break;
            }
        }
        $cart = Mage::getSingleton('checkout/cart');
        try {
            $cart->addProduct(Mage::getModel('catalog/product')->load($product_id));
            $cart->save();
        } catch (Exception $e) {
        }
        $this->_redirect('onestepcheckout');

        return;
    }

    public function addToCart()
    {
        $productId = $this->getRequest()->getParam('id');
        if (!$productId)
            return $this;
        $optionId    = $this->getRequest()->getParam('optid');
        $optionValue = $this->getRequest()->getParam('optval');
        $edition     = $this->getRequest()->getParam('edt');
        $cart        = Mage::getSingleton('checkout/cart');

        try {
            $params = array(
                'product' => $productId,
                'qty'     => 1,
                'options' => array(
                    $optionId => $optionValue,
                ),
                'links'   => array($edition)
            );
            $cart->addProduct(Mage::getModel('catalog/product')->load($productId), $params);
            $cart->save();
        } catch (Exception $e) {
        }
        $this->_redirect('onestepcheckout');

        return;
    }
}