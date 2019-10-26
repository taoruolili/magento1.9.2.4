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
class Magegiant_Onestepcheckout_Helper_Customer extends Mage_Core_Helper_Abstract
{

    /**
     * Return available customer attribute form as select options
     *
     * @return array
     */
    public function getAttributeFormOptions()
    {
        return array(
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Checkout Register'),
                'value' => 'checkout_register'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Registration'),
                'value' => 'customer_account_create'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Account Edit'),
                'value' => 'customer_account_edit'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Admin Checkout'),
                'value' => 'adminhtml_checkout'
            ),
        );
    }

    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return 'customer';
    }

    public function sendForgotPasswordForCustomer(Mage_Customer_Model_Customer $customer)
    {
        if (method_exists(Mage::helper('customer'), 'generateResetPasswordLinkToken')) {
            $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
            $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
            $customer->sendPasswordResetConfirmationEmail();
        } else {
            $newPassword = $customer->generatePassword();
            $customer->changePassword($newPassword, false);
            $customer->sendPasswordReminderEmail();
        }
    }

}