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
class Magegiant_Onestepcheckout_AjaxController extends Mage_Checkout_Controller_Action
{

    protected $_resultArray = array(
        'success'     => true,
        'messages'    => array(),
        'blocks'      => array(),
        'grand_total' => ""
    );

    /**
     * Predispatch: should set layout area
     *
     * @return Mage_Checkout_OnepageController
     */
    public function preDispatch()
    {
        /*Copy paste from Mage_Checkout_OnepageController*/
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

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     *
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }

        return false;
    }

    /**
     * action for customer login
     */
    public function loginAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $result = array(
            'success' => false
        );

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getParam('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    $result['success'] = true;
                    $result['message'] = Mage::helper('customer')->__('Login Successfully. Please wait...');

                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $result['error'] = $message;
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                }
            } else {
                $result['error'] = Mage::helper('customer')->__('Login and password are required.');
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * action for customer forgot password
     */
    public function forgotPasswordAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }
        $result = array(
            'success'  => false,
            'messages' => array()
        );
        $email  = $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $session->setForgottenEmail($email);
                $result['error'] = Mage::helper('checkout')->__('Invalid email address.');
            } else {
                $formId       = 'user_forgotpassword';
                $captchaModel = Mage::helper('captcha')->getCaptcha($formId);
                if ($captchaModel->isRequired()) {
                    if (!$captchaModel->isCorrect($this->_getCaptchaString($this->getRequest(), $formId))) {
                        $result = array(
                            'success' => false,
                            'error'   => Mage::helper('captcha')->__('Incorrect CAPTCHA.'),
                            'captcha' => 'user_forgotpassword'
                        );
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                        return;
                    }
                }
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    try {
                        $customerHelper = Mage::helper('customer');
                        if (method_exists($customerHelper, 'generateResetPasswordLinkToken')) {
                            $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                            $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                            $customer->sendPasswordResetConfirmationEmail();
                        } else {
                            // 1.6.0.x and earlier
                            $newPassword = $customer->generatePassword();
                            $customer->changePassword($newPassword, false);
                            $customer->sendPasswordReminderEmail();
                        }
                        $result['message'] = Mage::helper('customer')->__('A new password has been sent.');
                        $result['success'] = true;
                    } catch (Exception $e) {
                        $result['error'] = $e->getMessage();
                    }
                }
                if (!isset($result['message']) && !$result['success'] && !$customer->getId()) {
                    $result['error'] = Mage::helper('customer')->__('There is no account belong to %s', Mage::helper('customer')->htmlEscape($email));
                }
            }
        } else {
            $result['error'] = Mage::helper('customer')->__('Please enter your email.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Get Captcha String
     *
     * @param Varien_Object $request
     * @param string        $formId
     * @return string
     */
    protected function _getCaptchaString($request, $formId)
    {
        $captchaParams = $request->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);

        return $captchaParams[$formId];
    }

    public function saveFormValuesAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array(
            'success' => true,
        );
        if ($this->getRequest()->isPost()) {
            $newData     = $this->getRequest()->getPost();
            $currentData = $this->getFormData();
            if (!is_array($currentData)) {
                $currentData = array();
            }
            $this->setFormData(array_merge($currentData, $newData));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * get One Step Checkout Form Data
     *
     * @return array
     */
    public function getFormData()
    {
        return Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
    }

    /**
     * Set One Step Checkout Form Data
     *
     * @param $data
     */
    public function setFormData($data)
    {
        Mage::getSingleton('checkout/session')->setData('onestepcheckout_form_values', $data);
    }

    /**
     * save checkout billing address
     */
    public function saveAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = $this->_resultArray;
        if ($this->getRequest()->isPost()) {
            $dataBilling       = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($dataBilling['email'])) {
                $dataBilling['email'] = trim($dataBilling['email']);
            }
            $saveBilling    = Mage::helper('onestepcheckout/address')->saveBilling($dataBilling, $customerAddressId);
            $useForShipping = isset($dataBilling['use_for_shipping']) ? $dataBilling['use_for_shipping'] : 0;
            if ($useForShipping == 0) {
                Mage::getSingleton('checkout/session')->setData('same_as_billing', 0);
                $data_shipping                    = $this->getRequest()->getPost('shipping', array());
                $data_shipping['same_as_billing'] = 0;
                $customerAddressId                = $this->getRequest()->getPost('shipping_address_id', false);
                $saveShipping                     = Mage::helper('onestepcheckout/address')->saveShipping($data_shipping, $customerAddressId);
            } else if ($useForShipping == 2) {
                if (Mage::helper('core')->isModuleOutputEnabled('Pook_CollectInStore') && Mage::getStoreConfigFlag('carriers/collectinstore/active')) {
                    $carrier = Mage::getModel('pook_collectinstore/carrier_collectInStore');
                    /* Set shipping address to configured store address... */
                    $data_shipping = array(
                        'address_id'           => null,
                        'firstname'            => $carrier->getConfigData('address_firstname'),
                        'lastname'             => $carrier->getConfigData('address_lastname'),
                        'company'              => $carrier->getConfigData('address_company'),
                        'street'               => array(
                            $carrier->getConfigData('address_line1'),
                            $carrier->getConfigData('address_line2')
                        ),
                        'city'                 => $carrier->getConfigData('address_city'),
                        'region_id'            => 1,
                        'region'               => $carrier->getConfigData('address_region'),
                        'postcode'             => $carrier->getConfigData('address_postcode'),
                        'country_id'           => $carrier->getConfigData('address_country'),
                        'telephone'            => $carrier->getConfigData('address_telephone'),
                        'save_in_address_book' => 0,
                        'same_as_billing'      => 2
                    );
                    $this->getQuote()->setTotalsCollectedFlag(true);
                    $saveShipping = $this->getOnepage()->saveShipping($data_shipping, false);
                    /* Set shipping method to collectinstore... */
                    $method = $carrier->getCode() . '_' . $carrier->getCode();
                    $this->getQuote()->setTotalsCollectedFlag(false);
                    /* Now reset TotalsCollectedFlag so the Shipping/shippingMethod totals are calculated. */
                    $this->getShippingAddress()->setShippingMethod($method);
                    Mage::getSingleton('checkout/session')->setData('same_as_billing', 2);
                }
            } else {
                Mage::getSingleton('checkout/session')->setData('same_as_billing', 1);
            }
            if (isset($saveShipping)) {
                $saveResult = array_merge($saveBilling, $saveShipping);
            } else {
                $saveResult = $saveBilling;
            }

            if (isset($saveResult['error'])) {
                $result['success'] = false;
                if (is_array($saveResult['message'])) {
                    $result['messages'] = array_merge($result['messages'], $saveResult['message']);
                } else {
                    $result['messages'][] = $saveResult['message'];
                }
            }
            $shippingRates = $this->getShippingAddress()->collectTotals()->collectShippingRates()->getAllShippingRates();
            if (count($shippingRates) == 1) {
                $shippingMethod = $shippingRates[0]->getCode();
                $this->getShippingAddress()->setShippingMethod($shippingMethod);
            }
            // Set Default Shipping Method
            Mage::helper('onestepcheckout/address')->setDefaultShippingMethod($shippingRates, $this->getShippingAddress());
            $this->getQuote()->collectTotals()->save();
            $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            if ($this->_isEnabledGrandTotal()) {
                $result['grand_total'] = $this->getGrandTotal();
            }
        } else {
            $result['success']    = false;
            $result['messages'][] = $this->__('Please specify billing address information.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Enabled Grand Total in Place Order Button
     *
     * @return mixed
     */
    protected function _isEnabledGrandTotal()
    {
        return Mage::helper('onestepcheckout/config')->showGrandTotal();
    }

    /**
     * get quote Grand Total
     *
     * @return mixed
     */
    public function getGrandTotal()
    {
        return Mage::helper('onestepcheckout')->getrandTotal($this->getOnepage()->getQuote());
    }

    /**
     * Get Checkout Onepage Quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    /**
     * Get Billing Address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * get Shipping Address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Shipping method save
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = $this->_resultArray;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            /*Hack for Magegiant Storepickup*/
            if ($data != 'storepickup_storepickup') {
                Mage::getSingleton('checkout/session')->setData('storepickup_session', array());
            }
            Mage::dispatchEvent(
                'one_step_checkout_save_shipping_method_before',
                array(
                    'request' => $this->getRequest(),
                    'quote'   => $this->getQuote()
                )
            );
            $saveResult = $this->getOnepage()->saveShippingMethod($data);
            Mage::dispatchEvent(
                'one_step_checkout_save_shipping_method_after',
                array(
                    'request' => $this->getRequest(),
                    'quote'   => $this->getOnepage()->getQuote()
                )
            );
            if (isset($saveResult['error'])) {
                $result['success']    = false;
                $result['messages'][] = $saveResult['message'];
            }
            $this->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
            $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            if ($this->_isEnabledGrandTotal()) {
                $result['grand_total'] = $this->getGrandTotal();
            }
        } else {
            $result['success']    = false;
            $result['messages'][] = $this->__('Please specify shipping method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Payment method save
     */
    public function savePaymentMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = $result = $this->_resultArray;
        try {
            if ($this->getRequest()->isPost()) {
                $data       = $this->getRequest()->getPost('payment', array());
                $saveResult = $this->getOnepage()->savePayment($data);
                if (isset($saveResult['error'])) {
                    $result['success']    = false;
                    $result['messages'][] = $saveResult['message'];
                }
                $this->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
                if ($this->_isEnabledGrandTotal()) {
                    $result['grand_total'] = $this->getGrandTotal();
                }
            } else {
                $result['success']    = false;
                $result['messages'][] = $this->__('Please specify payment method.');
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['error'][] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyCouponAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array(
            'success'        => true,
            'coupon_applied' => false,
            'messages'       => array(),
            'blocks'         => array(),
            'grand_total'    => ""
        );
        if (!$this->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            $couponCode    = (string)$this->getRequest()->getParam('coupon_code');
            $oldCouponCode = $this->getQuote()->getCouponCode();
            if (!strlen($couponCode) && !strlen($oldCouponCode)) {
                $result['success'] = false;
            } else {
                try {
                    $this->getShippingAddress()->setCollectShippingRates(true);
                    $this->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                        ->collectTotals()
                        ->save();
                    if ($couponCode == $this->getQuote()->getCouponCode()) {
                        $this->getShippingAddress()->setCollectShippingRates(true);
                        $this->getQuote()->setTotalsCollectedFlag(false);
                        $this->getQuote()->collectTotals()->save();
                        Mage::getSingleton('checkout/session')->getMessages(true);
                        if (strlen($couponCode)) {
                            $result['coupon_applied'] = true;
                            $result['messages'][]     = $this->__('Coupon code was applied.');
                        } else {
                            $result['coupon_applied'] = false;
                            $result['messages'][]     = $this->__('Coupon code was canceled.');
                        }
                    } else {
                        $result['success']    = false;
                        $result['messages'][] = $this->__('Coupon code is not valid.');
                    }
                    $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
                    if ($this->_isEnabledGrandTotal()) {
                        $result['grand_total'] = $this->getGrandTotal();
                    }
                } catch (Mage_Core_Exception $e) {
                    $result['success']    = false;
                    $result['messages'][] = $e->getMessage();
                } catch (Exception $e) {
                    $result['success']    = false;
                    $result['messages'][] = $this->__('Cannot apply the coupon code.');
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function placeOrderAction()
    {
        /*
        if (version_compare(Mage::getVersion(), '1.8.0.0') >= 0) {
                    if (!$this->_validateFormKey()) {
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                            'success'        => false,
                            'error'          => true,
                            'error_messages' => Mage::helper('onestepcheckout')->__('Invalid Form Key. Please refresh the page.')
                        )));

                        return;
                    }
                }
        */
        if ($this->_expireAjax()) {
            return;
        }

        try {
            if ($this->getRequest()->isPost()) {
                $billingData = $this->getRequest()->getPost('billing', array());
                $result      = $this->createAccountWhenCheckout($billingData);
                if ($result['success']) {
                    // Save billing address
                    $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
                    if (isset($billingData['email'])) {
                        $billingData['email'] = trim($billingData['email']);
                    }
                    $saveBilling = $this->getOnepage()->saveBilling($billingData, $customerAddressId);

                    // Save shipping address
                    if (!isset($billingData['use_for_shipping'])) {
                        $shippingData      = $this->getRequest()->getPost('shipping', array());
                        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                        $saveShipping      = $this->getOnepage()->saveShipping($shippingData, $customerAddressId);
                    }
                    if (isset($saveShipping)) {
                        $saveResult = array_merge($saveBilling, $saveShipping);
                    } else {
                        $saveResult = $saveBilling;
                    }
                    Mage::dispatchEvent(
                        'checkout_controller_onepage_save_shipping_method',
                        array(
                            'request' => $this->getRequest(),
                            'quote'   => $this->getOnepage()->getQuote()
                        )
                    );

                    if (isset($saveResult['error'])) {
                        $result['success'] = false;
                        if (!is_array($saveResult['message'])) {
                            $saveResult['message'] = array($saveResult['message']);
                        }
                        $result['messages'] = array_merge($result['messages'], $saveResult['message']);
                    } else {
                        $requiredAgreements = Mage::helper('onestepcheckout/config')->getRequiredAgreementIds();
                        $postedAgreements   = array_keys($this->getRequest()->getPost('one_step_checkout_agreement', array()));
                        if ($diff = array_diff($requiredAgreements, $postedAgreements) && Mage::helper('onestepcheckout/config')->isEnabledTerm()) {
                            $result['success']    = false;
                            $result['messages'][] = $this->__('Please agree to all the terms and conditions before placing the order.');
                        } else {
                            if ($data = $this->getRequest()->getPost('payment', false)) {
                                $this->getOnepage()->getQuote()->getPayment()->importData($data);
                            }
                            //is used delivery time
                            $is_used_delivery_time = $this->getRequest()->getPost('enabled_delivery_time', false);
                            if ($is_used_delivery_time) {
                                $delivery = $this->getRequest()->getPost('delivery', array());
                            } else {
                                $delivery = array();
                            }
                            //save data for use after order save
                            $data = array(
                                'comments'                        => $this->getRequest()->getPost('comments', false),
                                'delivery'                        => $delivery,
                                'onestepcheckout_survey_answer'   => $this->getRequest()->getPost('onestepcheckout_survey_answer', false),
                                'onestepcheckout_survey_question' => $this->getRequest()->getPost('onestepcheckout_survey_question', false),
                                'is_subscribed'                   => $this->getRequest()->getPost('is_subscribed', false),
                                'billing'                         => $this->getRequest()->getPost('billing', array()),
                            );
                            Mage::getSingleton('checkout/session')->setData('onestepcheckout_order_data', $data);

                            // Authorize.Net
                            if (@class_exists('Mage_Authorizenet_Model_Directpost_Session')) {
                                Mage::getSingleton('authorizenet/directpost_session')->setQuoteId(
                                    $this->getOnepage()->getQuote()->getId()
                                );
                            }
                            // 3D Secure
                            $method = $this->getOnepage()->getQuote()->getPayment()->getMethodInstance();
                            if ($method->getIsCentinelValidationEnabled()) {
                                $centinel = $method->getCentinelValidator();
                                if ($centinel && $centinel->shouldAuthenticate()) {
                                    $layout = $this->getLayout();
                                    $update = $layout->getUpdate();
                                    $update->load('onestepcheckout_index_saveorder');
                                    $this->_initLayoutMessages('checkout/session');
                                    $layout->generateXml();
                                    $layout->generateBlocks();
                                    $html                     = $layout->getBlock('centinel.frame')->toHtml();
                                    $result['is_centinel']    = true;
                                    $result['update_section'] = array(
                                        'name' => 'paypaliframe',
                                        'html' => $html
                                    );
                                    $result['success']        = false;

                                    return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                                }
                            }
                            // \3D Secure
                            // Sage Pay Suite
                            $paymentHelper = Mage::helper('onestepcheckout/payment');
                            $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
                            if ($paymentHelper->isSagePaySuiteMethod($paymentMethod)) {
                                $redirectUrl = $this->_sagePaySuiteProcess($this->getQuote()->getPayment()->getMethod());
                            } else {
                                $redirectUrl = $this
                                    ->getOnepage()
                                    ->getQuote()
                                    ->getPayment()
                                    ->getCheckoutRedirectUrl();
                                if (!$redirectUrl) {
                                    $this->getOnepage()->saveOrder();
                                    /* Compatibility for Mage_Authorizenet DPM */
                                    if ($paymentMethod == 'authorizenet_directpost') {
                                        $dpm      = Mage::helper('onestepcheckout/payment_authorizenet_directpost');
                                        $dpmError = $dpm->process(
                                            $this->getRequest()->getPost('payment', false)
                                        );
                                        if ($dpmError) {
                                            throw new Exception($dpmError);
                                        }
                                    }
                                    $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
                                }
                            }

                        }
                    }
                }
            } else {
                $result['success'] = false;
            }
        } catch (Exception $e) {
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getQuote(), $e->getMessage());
            $result['success']    = false;
            $result['messages'][] = $this->__('There was an error processing your order. Please contact us or try again later.');
            $result['messages'][] = $e->getMessage();
        }
        if ($result['success']) {
            $this->getOnepage()->getQuote()->save();
            if (isset($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Create accont when customer checkout
     *
     * @return array
     */
    public function createAccountWhenCheckout($billingData)
    {
        $result = array(
            'success'  => true,
            'messages' => array(),
        );
        if (!$this->getOnepage()->getCustomerSession()->isLoggedIn()) {
            if (isset($billingData['create_account'])) {
                $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
            } else {
                $this->getOnepage()->saveCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
            }
        }

        if (!$this->getQuote()->getCustomerId() &&
            Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()
        ) {
            if ($this->_customerEmailExists($billingData['email'], Mage::app()->getWebsite()->getId())) {
                $result['success']    = false;
                $result['messages'][] = $this->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
            }
        }

        return $result;
    }

    /**
     *
     */
    public function addProductToWishlistAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result   = array(
            'success' => true,
        );
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $result['success'] = false;
        }
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->getId() || !$product->isVisibleInCatalog()) {
                $result['success'] = false;
            } else {
                try {
                    $requestParams = $this->getRequest()->getParams();
                    $buyRequest    = new Varien_Object($requestParams);
                    if (is_string($wishlist->addNewItem($product, $buyRequest))) {
                        $result['success'] = false;
                    }
                    $wishlist->save();
                    Mage::helper('wishlist')->calculate();
                    $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
                } catch (Mage_Core_Exception $e) {
                    $result['error'] = $e->getMessage();
                } catch (Exception $e) {
                    $result['error'] = $e->getMessage();
                }
            }
        }
        if ($result['success']) {
            $result['messages'] = $this->__('Added to Wishlist');
        } else if (!isset($result['error'])) {
            $result['error'] = $this->__('Product can not add to Wishlist');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Retrieve wishlist object
     *
     * @param int $wishlistId
     * @return Mage_Wishlist_Model_Wishlist|bool
     */
    protected function _getWishlist()
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }
        try {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $wishlist   = Mage::getModel('wishlist/wishlist');
            $wishlist->loadByCustomer($customerId, true);
            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                $wishlist = null;
            }
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        return $wishlist;
    }

    /**
     *
     */
    public function addProductToCompareListAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result    = array(
            'success' => true,
        );
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId
            && (Mage::getSingleton('log/visitor')->getId() || Mage::getSingleton('customer/session')->isLoggedIn())
        ) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId() /* && !$product->isSuper()*/) {
                try {
                    Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                    $result['blocks']  = $this->getBlockHelper()->getActionBlocks();
                    $result['message'] = $this->__('Added to Compare');
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['error']   = $e->getMessage();
                }
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product' => $product));
            }

            Mage::helper('catalog/product_compare')->calculate();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return Magegiant_Onestepcheckout_Model_Updater
     */
    public function getBlockHelper()
    {
        return Mage::helper('onestepcheckout/block');
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
        || Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
        || !Mage::helper('checkout')->isCustomerMustBeLogged();
    }

    /**
     * @return Magegiant_Onestepcheckout_AjaxController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();

        return $this;
    }

    /**
     * helper
     *
     * @return null|Mage_Core_Controller_Front_Action
     */
    private function _getCustomerWishlistController($request, $response)
    {
        $fbIntegratorModuleName = 'Mage_Wishlist';
        $controllerName         = 'index';

        return $this->_createController($fbIntegratorModuleName, $controllerName, $request, $response);
    }

    /**
     * helper
     *
     * @return null|Mage_Core_Controller_Front_Action
     */
    private function _getProductCompareController($request, $response)
    {
        $fbIntegratorModuleName = 'Mage_Catalog';
        $controllerName         = 'product_compare';

        return $this->_createController($fbIntegratorModuleName, $controllerName, $request, $response);
    }

    /**
     * helper
     *
     * @param $moduleName
     * @param $controllerName
     * @param $request
     * @param $response
     *
     * @return Mage_Core_Controller_Front_Action|null
     */
    private function _createController($moduleName, $controllerName, $request, $response)
    {
        $router             = Mage::app()->getFrontController()->getRouter('standard');
        $controllerFileName = $router->getControllerFileName($moduleName, $controllerName);
        if (!$router->validateControllerFileName($controllerFileName)) {
            return null;
        }
        $controllerClassName = $router->getControllerClassName($moduleName, $controllerName);
        if (!$controllerClassName) {
            return null;
        }

        if (!class_exists($controllerClassName, false)) {
            if (!file_exists($controllerFileName)) {
                return null;
            }
            include $controllerFileName;

            if (!class_exists($controllerClassName, false)) {
                return null;
            }
        }
        $controllerInstance = Mage::getControllerInstance(
            $controllerClassName,
            $request,
            $response
        );

        return $controllerInstance;
    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int    $websiteId
     * @return false|Mage_Customer_Model_Customer
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }

        return false;
    }

    protected function _sagePaySuiteProcess($method)
    {
        switch ($method) {
            case 'sagepaypaypal':
                return Mage::getModel('core/url')->addSessionParam()->getUrl('sgps/paypalexpress/go', array('_secure' => true));
            case 'sagepaydirectpro':
                $this->_forward('saveOrder', 'directPayment', 'sgps', $this->getRequest()->getParams());
                break;
            case 'sagepayform':
                $this->_forward('saveOrder', 'formPayment', 'sgps', $this->getRequest()->getParams());
                break;
            case 'sagepayserver':
                $this->_forward('saveOrder', 'serverPayment', 'sgps', $this->getRequest()->getParams());
                break;
            default:
                return null;
        }
    }

    /**
     *
     */
    public function ajaxCartItemAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $action = $this->getRequest()->getParam('action');
        $id     = (int)$this->getRequest()->getParam('id');
        switch ($action) {
            case 'plus':
            case 'minus':
                $this->_updateCartItem($action, $id);
                break;
            default:
                $this->_removeCartItem($id);
        }
    }

    /**
     * @param $action
     * @param $id
     */
    protected function _updateCartItem($action, $id)
    {
        $cart      = $this->_getCart();
        $quoteItem = $cart->getQuote()->getItemById($id);
        $qty       = $quoteItem->getQty();
        $result    = array();
        if ($id) {
            try {
                if (isset($qty)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty    = $filter->filter($qty);
                }
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }
                if ($action == 'plus')
                    $qty++;
                else $qty--;
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
                $this->_getCart()->save();
                $message = $cart->getQuote()->getMessages();
                if ($message) {
                    $result['error']   = $message['qty']->getCode();
                    $result['success'] = 0;
                    $quoteItem->setQty($qty - 1)->save();
                    $this->_getCart()->save();
                }
                if (!$quoteItem->getHasError()) {
                    $result['success'] = 1;
                } else {
                    $result['success'] = 0;
                }
            } catch (Mage_Core_Exception $e) {
                $result['success'] = 0;
                $result['error']   = Mage::helper('core')->escapeHtml($e->getMessage());
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error']   = $this->__('Can not save item.');
            }
            if (array_key_exists('error', $result)) {
                $result['success'] = 0;
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } else {
                $this->_updateOrderReview();
            }
        }
    }

    /**
     * @param $id
     */
    protected function _removeCartItem($id)
    {
        $result = array();
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();
                $result['qty']     = $this->_getCart()->getSummaryQty();
                $result['success'] = 1;
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error']   = $e->getMessage();
            }
            if (array_key_exists('error', $result)) {
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } else {
                $this->_updateOrderReview();
            }
        }
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _updateOrderReview()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        try {
            if ($this->getRequest()->isPost()) {
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
                if ($this->_isEnabledGrandTotal()) {
                    $result['grand_total'] = $this->getGrandTotal();
                }
            } else {
                $result['success']    = false;
                $result['messages'][] = $this->__('Please specify payment method.');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['success'] = false;
            $result['error'][] = $this->__('Unable to update cart item');
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     *
     */
    public function addGiftWrapAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $is_used_giftwrap = $this->getRequest()->getParam('is_used_giftwrap', false);
        if ($is_used_giftwrap) {
            Mage::getSingleton('checkout/session')->setData('is_used_giftwrap', 1);
        } else {
            Mage::getSingleton('checkout/session')->setData('is_used_giftwrap', 0);
        }
        $this->_updateOrderReview();
    }

    public function applyGiantPointsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $session = Mage::getSingleton('checkout/session');
        $session->setData('is_used_point', $this->getRequest()->getParam('is_used_point'));
        $session->setRewardSalesRules(array(
            'rule_id'      => $this->getRequest()->getParam('reward_sales_rule'),
            'point_amount' => $this->getRequest()->getParam('reward_sales_point'),
        ));
        $result = array(
            'success'  => true,
            'messages' => array(),
            'blocks'   => array(),
        );
        try {
            if ($this->getRequest()->isPost()) {
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            } else {
                $result['success']    = false;
                $result['messages'][] = $this->__('Please specify payment method.');
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result['success'] = false;
            $result['error'][] = $this->__('Unable to update payment method');
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}