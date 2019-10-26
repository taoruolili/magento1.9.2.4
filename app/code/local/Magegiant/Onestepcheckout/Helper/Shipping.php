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
class Magegiant_Onestepcheckout_Helper_Shipping extends Mage_Core_Helper_Data
{
    const TEMPLATE_PATH = 'magegiant/onestepcheckout/';
    const EVENT_PREFIX  = 'magegiant_one_step_checkout_';

    /**
     * get shippimg method temple
     *
     * @return string
     */
    public function getShippingMethodTemplate()
    {
        $template = new Varien_Object(array(
            'file_path' => 'container/form/shipping.phtml'
        ));
        Mage::dispatchEvent(self::EVENT_PREFIX . 'get_shipping_method_template_before', array(
            'template' => $template
        ));

        return self::TEMPLATE_PATH . $template->getFilePath();
    }

    public function getShippingRates()
    {
        $address = Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getShippingAddress()
            ->collectShippingRates()
            ->save();

        return $address->getGroupedAllShippingRates();;
    }

    public function getLastShippingMethod()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer->getId()) {
            return false;
        }
        $orderCollection = Mage::getModel('sales/order')
            ->getCollection()
            ->addFilter('customer_id', $customer->getId())
            ->addFieldToFilter('shipping_method', array('neq' => ''))
            ->addAttributeToSort('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->setPageSize(1);

        $lastOrder = $orderCollection->getFirstItem();
        if (!$lastOrder->getId()) {
            return false;
        }

        return $lastOrder->getShippingMethod();
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}