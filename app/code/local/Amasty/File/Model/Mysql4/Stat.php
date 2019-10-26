<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Mysql4_Stat extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amfile/stat', 'id');
    }

    public function deleteStat()
    {
        $this->_getWriteAdapter()->truncate($this->getMainTable());
    }
}
