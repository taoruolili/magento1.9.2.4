
<?php

class Jeasy_Checkout_Model_Observer
{
    public function onAddProductComplete($observer)
    {
        /** @var Mage_Core_Controller_Response_Http $response */
        $response = $observer->getResponse();
        $url = Mage::helper('checkout/url')->getCheckoutUrl();
        $response->setRedirect($url);

        $session = Mage::getSingleton('checkout/session');
        $session->setNoCartRedirect(true);
    }
}
