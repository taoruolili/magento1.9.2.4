<?php

class Magegiant_Onestepcheckout_Model_Mysql4_Abandonedcart_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('onestepcheckout/abandonedcart_customer');
    }
}