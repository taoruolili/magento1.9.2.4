<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2013 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Mysql4_File_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{

    public function _construct() 
    {
        $this->_init('amfile/file');
    }

    public function getFilesFrontend($productId, $storeId)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.file_id AND s.store_id = ?", $storeId);
        $select = $this->getSelect()
            ->join(
                array('d' => $this->getTable('amfile/store')), "main_table.file_id = d.file_id ")
            ->joinLeft(
                array('s' => $this->getTable('amfile/store')), $joinCondition,
                array(
                    'label' => $this->getCheckSql("s.use_default_label = 0", 's.label', 'd.label'),
                    'visible' => $this->getCheckSql("s.use_default_visible = 0", 's.visible', 'd.visible'),
                    'position' => $this->getCheckSql("s.position IS NULL", 'd.position', 's.position'),
                    'store_id' => $this->getCheckSql("s.store_id IS NULL", 'd.store_id', 's.store_id'),
                ))
            ->where('main_table.product_id = ?', $productId)
            ->where('d.store_id = ?', 0)
            ->having('visible = 1')
            ->order("position ASC");

        return $this;
    }

    public function getFilesAdmin($productId, $storeId)
    {
        $adapter = $this->getConnection();
        $joinCondition = $adapter->quoteInto("s.file_id = main_table.file_id AND s.store_id = ?", $storeId);
        $select = $this->getSelect()
            ->join(
                array('d' => $this->getTable('amfile/store')), 'main_table.file_id = d.file_id')
            ->joinLeft(
                array('s' => $this->getTable('amfile/store')), $joinCondition, array(
                    'label' => $this->getCheckSql("s.use_default_label = 0", 's.label', 'd.label'),
                    'visible' => $this->getCheckSql("s.use_default_visible = 0", 's.visible', 'd.visible'),
                    'position' => $this->getCheckSql("s.position IS NULL", 'd.position', 's.position'),
                    'use_default_label' => $this->getCheckSql("s.use_default_label = 0", '0', '1'),
                    'use_default_visible' => $this->getCheckSql("s.use_default_visible = 0", '0', '1'),
            ))
            ->where('main_table.product_id = ?', $productId)
            ->where('d.store_id = ?',0);

        return $this;
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }
}
