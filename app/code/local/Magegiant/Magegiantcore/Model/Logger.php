<?php

class Magegiant_Magegiantcore_Model_Logger extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magegiantcore/logger');
    }	
	
}