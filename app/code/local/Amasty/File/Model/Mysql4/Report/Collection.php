<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */
class Amasty_File_Model_Mysql4_Report_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('amfile/stat');
    }

    protected function _joinFields($from = '', $to = '')
    {
        $store = +Mage::app()->getRequest()->getParam('store', 0);
        $webSite = +Mage::app()->getRequest()->getParam('website');
        $group = +Mage::app()->getRequest()->getParam('group');

        $adapter = $this->getConnection();
        $select = $this->getSelect();
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute('name');

        $this->addFieldToFilter('main_table.date', array('from' => $from, 'to' => $to, 'datetime' => true));
        $select
            ->columns(array('rating' => 'sum(rating)'))
            ->joinLeft(
                array('file_store' => $this->getTable('amfile/store')),
                'main_table.file_id = file_store.file_id AND file_store.store_id = 0',
                array('label')
            )
            ->joinLeft(
                array('default_product' => $attribute->getBackendTable()),
                'main_table.product_id = default_product.entity_id AND default_product.attribute_id = ' . $attribute->getAttributeId() . ' AND default_product.store_id = 0',
                array()
            )
            ->joinLeft(
                array('product' => $attribute->getBackendTable()),
                'main_table.product_id = product.entity_id AND product.attribute_id = ' . $attribute->getAttributeId() . ' AND product.store_id = ' . $store,
                array('name' => $this->getCheckSql("product.value IS NULL", 'default_product.value', 'product.value'))
            )
            ->group(array('file_url'))
        ;

        if ($webSite || $group)
        {
            $select->join(
                array('store' => $this->getTable('core/store')),
                'store.store_id = main_table.store',
                array()
            );
        }

        if ($store)
            $select->where('main_table.store = ?', $store);
        else if ($webSite)
            $select->where('store.website_id = ?', $webSite);
        else if ($group)
            $select->where('store.group_id = ?', $group);

        return $this;
    }

    public function setDateRange($from, $to)
    {
        $this->_reset()
                ->_joinFields($from, $to);

        return $this;
    }

    public function setStoreIds($storeIds)
    {
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
