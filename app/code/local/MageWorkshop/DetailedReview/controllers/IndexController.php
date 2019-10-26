<?php
/**
 * MageWorkshop
 * Copyright (C) 2012  MageWorkshop <mageworkshophq@gmail.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2012 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DetailedReview_IndexController extends Mage_Core_Controller_Front_Action {

    public function checkloginAction() {
        $params = $this->getRequest()->getParams();
        if (isset($params['login']['username']) && isset($params['login']['password'])) {
            try {
                $customerSession = Mage::getModel('customer/customer')
                                ->setWebsiteId(Mage::app()->getWebsite()->getWebsiteId())
                                ->authenticate($params['login']['username'], $params['login']['password']);
            } catch (Mage_Core_Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
                return;
            } catch (Exception $e) {
                $this->getResponse()->setBody($e->getMessage());
                return;
            }
            $this->getResponse()->setBody('1');
            return;
        }
        $this->getResponse()->setBody(Mage::helper('detailedreview')->__('Please, Fill Email and Password'));
        return;
    }

    public function checkregistrateAction() {
        $params = $this->getRequest()->getParams();

        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->getResponse()->setBody('1');
            return;
        }
        $errors = array();

        $helper = Mage::helper('detailedreview');



//        if (Mage::getStoreConfig("fontis_recaptcha/recaptcha/customer"))
//        { // check that recaptcha is actually enabled
//            $privatekey = Mage::getStoreConfig("fontis_recaptcha/setup/private_key");
//            // check response
//            $resp = Mage::helper("fontis_recaptcha")->recaptcha_check_answer(  $privatekey,
//                $_SERVER["REMOTE_ADDR"],
//                $params["recaptcha_challenge_field"],
//                $params["recaptcha_response_field"]
//            );
//            if ($resp != true)
//            { // if recaptcha response is correct, use core functionality
//                $message = array('error' => $helper->__('Your reCAPTCHA entry is incorrect. Please try again.'));
//                $this->getResponse()->setBody(json_encode($message));
//                return;
//            }
//        }

        if (!$customer = Mage::registry('current_customer')) {
            $customer = Mage::getModel('customer/customer')->setId(null);
        }

        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        $customerData = $customerForm->extractData($this->getRequest());

        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }

        /**
         * Initialize customer group id
         */
        $customer->getGroupId();

        try {
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $customer->setPassword($params['password']);
                $customer->setConfirmation($params['confirmation']);
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }
            }

            $validationResult = count($errors) == 0;

            if (true === $validationResult) {
                $customer->save();

                Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );

                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail(
                        'confirmation',
                        $session->getBeforeAuthUrl(),
                        Mage::app()->getStore()->getId()
                    );
//                    $session->addSuccess($helper->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
//                    $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
                    $result = array('success' => 'Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                    $this->getResponse()->setBody(json_encode($result));
                    return;
                } else {
                    $session->setCustomerAsLoggedIn($customer);
                    $this->getResponse()->setBody('1');
                    return;
                }
            } else {
                $this->getResponse()->setBody($helper->__('Invalid customer data'));
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = Mage::getUrl('customer/account/forgotpassword');
                $message = array('error' => $helper->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url));
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $this->getResponse()->setBody(json_encode($message));
        } catch (Exception $e) {
            $this->getResponse()->setBody($helper->__('Cannot save the customer.'));
        }
    }

    public function settimezoneAction() {
        Mage::getModel('customer/session')->setClientTimezone($this->getRequest()->getParam('time'));
    }

}

