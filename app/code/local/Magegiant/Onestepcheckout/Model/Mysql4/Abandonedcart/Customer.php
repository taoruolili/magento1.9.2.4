<?php

class Magegiant_Onestepcheckout_Model_Mysql4_Abandonedcart_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('onestepcheckout/abandonedcart_customer', 'id');
    }

}