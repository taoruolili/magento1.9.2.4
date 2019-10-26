<?php

class Jeasy_Sales_Model_Observer
{
    const XML_PATH_BLACKLIST_ENABLED = 'jeasy_sales/blacklist/enable';

    /**
     * salesGridBlockHtmlBefore
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesGridBlockHtmlBefore($observer)
    {
        $block = $observer->getData('block');
        /** @var Mage_Adminhtml_Block_Sales_Order_Grid $block */
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            $block->addExportType('*/*/exportXlsx', Mage::helper('core')->__('Excel'));
        }
    }

    /**
     * check blacklist before quote submit
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function quoteSubmitBefore($observer)
    {
        if (!Mage::getStoreConfig(self::XML_PATH_BLACKLIST_ENABLED)) {
            return;
        }

        /** @var Jeasy_Sales_Helper_Data $helper */
        $helper = Mage::helper('jeasy_sales');

        $errs = [];
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        $billingEmail = $order->getBillingAddress()->getEmail();
        if (in_array($billingEmail, $helper->getEmailBlacklist())) {
            $errs[] = $helper->__("billing address email [%s] found in blacklist", $billingEmail);
        }

        $customerEmail = $order->getCustomerEmail();
        if (in_array($customerEmail, $helper->getEmailBlacklist())) {
            $errs[] = $helper->__('customer email [%s] found in blacklist', $customerEmail);
        }

        $billingTelephone = $order->getBillingAddress()->getTelephone();
        if (in_array($billingTelephone, $helper->getTelephoneBlacklist())) {
            $errs[] = $helper->__('billing address telephone [%s] found in blacklist', $billingTelephone);
        }

        $shippingTelephone = $order->getShippingAddress()->getTelephone();
        if (in_array($shippingTelephone, $helper->getTelephoneBlacklist())) {
            $errs[] = $helper->__('shipping address telephone [%s] found in blacklist', $billingTelephone);
        }

        $firstname = $order->getBillingAddress()->getFirstname();
        $lastname = $order->getBillingAddress()->getLastname();
        $name = $firstname . ' ' . $lastname;
        if (in_array($name, $helper->getNameBlacklist())) {
            $errs[] = $helper->__('billing address customer name [%s] found in blacklist', $name);
        }

        $_firstname = $order->getShippingAddress()->getFirstname();
        $_lastname = $order->getShippingAddress()->getLastname();
        $_name = $_firstname . ' ' . $_lastname;
        if (in_array($_name, $helper->getNameBlacklist())) {
            $errs[] = $helper->__('shipping address customer name [%s] found in blacklist', $_name);
        }

        $allowedCountries = explode(',', (string)Mage::getStoreConfig('general/country/allow'));
        $countryId = $order->getShippingAddress()->getCountryId();
        if (!in_array($countryId, $allowedCountries)) {
            $errs[] = $helper->__('shipping address country [%s] is not allowed', $countryId);
        }

        $city = $order->getBillingAddress()->getCity();
        if (in_array($city, $helper->getCityBlacklist())) {
            $errs[] = $helper->__('billing address city [%s] found in blacklist', $city);
        }

        $_city = $order->getShippingAddress()->getCity();
        if (in_array($billingEmail, $helper->getCityBlacklist())) {
            $errs[] = $helper->__('shipping address city [%s] found in blacklist', $_city);
        }

        $ip = $order->getRemoteIp();
        if (in_array($ip, $helper->getIpBlacklist())) {
            $errs[] = $helper->__('ip [%s] found in blacklist', $_city);
        }

        if (count($errs)) {
            Mage::log('Blacklist info: '. var_export($errs, true));
            throw new Mage_Core_Exception('An error occurred, please try again later.');
        }

    }
}