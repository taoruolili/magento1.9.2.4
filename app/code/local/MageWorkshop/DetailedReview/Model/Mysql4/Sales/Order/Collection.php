<?php
/**
 * MageWorkshop
 * Copyright (C) 2012  MageWorkshop <mageworkshophq@gmail.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2012 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DetailedReview_Model_Mysql4_Sales_Order_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{
    protected $_salesFlatOrderItemTable;

    public function __construct()
    {
        parent::__construct();
        $this->_salesFlatOrderItemTable   = $this->getTable('sales/order_item');
    }

    /**
     * Add filter by specified product
     *
     * @param int $product
     * @return Mage_Sales_Model_Mysql4_Order_Collection
     */
    public function addProductFilter($product_id)
    {
        $fromTables = $this->_select->getPart(Zend_Db_Select::FROM);

        if(!isset ($fromTables[$this->_salesFlatOrderItemTable])) {
            $this->getSelect()->joinInner(array('soi' => $this->_salesFlatOrderItemTable),'main_table.entity_id = soi.order_id');
        }

        $this->addFilter('product', $this->getConnection()->quoteInto('soi.product_id=?', $product_id), 'string');

        return $this;
    }
}
