<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Store extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amfile/store', 'id');
    }
}
