<?php

class Jeasy_Checkout_Model_Observer
{
    public function onAddProductComplete($observer)
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $observer->getRequest();
        $returnUrl = $request->getParam('return_url');
        if (!$returnUrl) {
            /** @var Mage_Core_Controller_Response_Http $response */
            $response = $observer->getResponse();

            $url = Mage::getUrl('onestepcheckout');
            $response->setRedirect($url);

            $session = Mage::getSingleton('checkout/session');
            $session->setNoCartRedirect(true);
        }
    }
}
