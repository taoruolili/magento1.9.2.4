<?php

class Magegiant_Onestepcheckout_Model_Abandonedcart_Customer extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        $this->_init('onestepcheckout/abandonedcart_customer');
    }

    /*
     * Sends email
     * @return bool|Exception Sending result
     */
    public function sendEmail($params)
    {

        $customer_sent = $this->load($params['quote_id'], 'quote_id');
        if ($customer_sent && $customer_sent->getId() && $customer_sent->getIsSent() == 1) {
            return $this;
        }
        $helper    = Mage::helper('onestepcheckout/config');
        $store     = Mage::app()->getStore($params['store_id']);
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $url_resume = Mage::getUrl('onestepcheckout/abandonedcart/index', array('code' => $params['quote_id']));
        try {
            Mage::getModel('core/email_template')
                ->setDesignConfig(array(
                    'area'  => 'frontend',
                    'store' => $store->getId()
                ))->sendTransactional(
                    $helper->getAbandonedEmailTemplate($store),
                    $helper->getAbandonedEmailSender($store),
                    $params['customer_email'],
                    $params['customer_firstname'],
                    array(
                        'customer_name' => $params['customer_firstname'],
                        'url_resume'    => $url_resume,
                        'store'         => $store,
                        'quote'         => Mage::getModel('sales/quote')->load($params['quote_id']),
                    )
                );

            $translate->setTranslateInline(true);
            $this->updateIsSentEmail($params['quote_id'], $params['customer_email']);
        } catch (Exception $e) {
        }

        return $this;
    }

    public function updateIsSentEmail($quote_id, $email)
    {
        try {
            $this->setQuoteId($quote_id)
                ->setEmail($email)
                ->setIsSent(1)
                ->save();

            return $this;
        } catch (Exception $e) {
        }
    }

    /*
     * Validates rule basing on customer
     * @param array $params Parameters to inspect
     * @return bool Check result
     */
    public function validateByCustomer($params)
    {
        if (!isset($params['customer_email'])) {
            return false;
        }
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($params['customer_email']);

        return $this->_validateByCustomerGroup($customer);
    }

    /*
    * Validates rule basing on customer group
    * @param Mage_Core_Customer_Model_Customer $customer Customer to inspect
    * @return bool Check result
    */
    protected function _validateByCustomerGroup($customer)
    {
        $customerGroups = explode(',', Mage::helper('onestepcheckout/config')->getAbandonedCustomerGroup());
        if (!count($customerGroups)
            || in_array(Magegiant_Onestepcheckout_Model_System_Config_Source_Customer_Group::CUSTOMER_GROUP_ALL, $customerGroups)
        ) {
            return true;

        }
        if ($customer && $customer->getId()) {
            if ($customer->getGroupId() && !in_array($customer->getGroupId(), $customerGroups)) {
                return false;
            }
        } elseif (!in_array(
            Magegiant_Onestepcheckout_Model_System_Config_Source_Customer_Group::CUSTOMER_GROUP_NOT_REGISTERED, $customerGroups
        )
        ) {
            return false;
        }

        return true;
    }

    public function validateOrderOrCart($params)
    {
        $result = $this->_checkSaleAmount($params);

        return $result;
    }

    /*
    * Validates rule basing on sale amount condition
    * @param array $params Parameters to inspect
    * @param string $source Order or cart to inspect
    * @return bool Check result
    */
    protected function _checkSaleAmount($params)
    {
        $sale_rule = explode(',', Mage::helper('onestepcheckout/config')->getAbandonedSaleRule());
        $condition = $sale_rule[0];
        $value     = $sale_rule[1];
        if (!$condition || !$value) {
            return true;
        }
        $quote      = Mage::getModel('sales/quote')->load($params['quote_id']);
        $saleAmount = $quote->getGrandTotal();
        switch ($condition) {
            case '='  :
                $result = ($saleAmount == $value);
                break;
            case '>'  :
                $result = ($saleAmount > $value);
                break;
            case '>=' :
                $result = ($saleAmount >= $value);
                break;
            case '<'  :
                $result = ($saleAmount < $value);
                break;
            case '<=' :
                $result = ($saleAmount <= $value);
                break;
            case '!='  :
                $result = ($saleAmount != $value);
                break;
            default :
                return 'Unknown condition';
        }

        return (true === $result) ? true
            : ('Sale amount is not ' . $condition . ' ' . $value . ' (' . $saleAmount . ')');
    }

    /*
    * Validates rule basing on product properties
    * @param array $params Parameters to inspect
    * @return bool Check result
    */
    public function validateBySku($params)
    {
        $sku        = Mage::helper('onestepcheckout/config')->getAbandonedSku();
        $this->_sku = Magegiant_Onestepcheckout_Helper_Data::noEmptyValues(explode(',', $sku));
        // SKU rule
        if ($this->_sku) {
            if (count($this->_sku) && isset($params['sku']) && count($params['sku'])
                && !array_intersect(
                    $this->_sku, $params['sku']
                )
            ) {
                return false;
            }
        }

        return true;
    }

    public function validateByDate($params)
    {
        $schedule      = explode(',', Mage::helper('onestepcheckout/config')->getAbandonedSchedule());
        $total_time    = $schedule[0] * 3600 * 24 + $schedule[1] * 3600 + $schedule[2] * 60;
        $schedule_time = strtotime($params['updated_at']) + $total_time;
        return $schedule_time < time();
    }

    public function process($params)
    {
        if ($this->validateByDate($params) && $this->validateByCustomer($params) && $this->validateBySku($params) && $this->validateOrderOrCart($params)) {
            $this->sendEmail($params);
        }
    }
}