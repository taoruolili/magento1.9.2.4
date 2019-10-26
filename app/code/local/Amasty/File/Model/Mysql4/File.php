<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Mysql4_File extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() 
    {
        $this->_init('amfile/file', 'file_id');
    }
}