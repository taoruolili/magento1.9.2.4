<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Mysql4_Icon extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('amfile/icon', 'id');
    }

    public function getIcon($fileUrl) 
    {
        // if (preg_match('/\.(\w+)$/', $fileUrl, $match)) // single extensions only(dots in file names)
        if (preg_match('/\.([\.\w]+)$/', $fileUrl, $match))
        {
            $select = $this->_getReadAdapter()
                ->select()
                ->from($this->getTable('amfile/icon'), 'image')
                ->where('active = 1')
                ->where('FIND_IN_SET(?, type)', $match[1])
            ;

            return $this->_getReadAdapter()->fetchOne($select);
        }
    }
}