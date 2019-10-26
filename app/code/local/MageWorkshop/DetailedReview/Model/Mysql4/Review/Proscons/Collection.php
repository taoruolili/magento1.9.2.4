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

class MageWorkshop_DetailedReview_Model_Mysql4_Review_Proscons_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_entityType;
    protected $_entityName;
    protected $_className;

    protected function _construct()
    {
        $this->_init('detailedreview/review_proscons');
    }

    public function addStoreData()
    {
        foreach ($this as $item) {
            $item->getStoreIds();
        }
        return $this;
    }

    /**
     * Add store filter
     *
     * @param   int|array $storeId
     * @return  Varien_Data_Collection_Db
     */
    public function addStoreFilter($storeId = false)
    {
        if (!Mage::app()->isSingleStoreMode()) {
            if(!$storeId) {
//                if(!$storeId = Mage::registry('atata')) {
                    $storeId = Mage::app()->getStore()->getStoreId();
//                }
            }
            $storeTable = $this->getResource()->getTable('detailedreview/review_proscons_store');
            $this->getSelect()
                ->join(array('store'=>$storeTable), 'main_table.entity_id=store.entity_id', array())
                ->where("store.store_id IN (?)", $storeId)
                ->where('store.entity_type=?', $this->_entityType);
        }
        return $this;
    }

//  method was created for filter by store to work properly in admin panel
    public function addFieldToFilter($field, $condition=null)
    {
        switch( $field ) {
            case 'store_ids':
                if(isset($condition['eq'])) {
                    $this->addStoreFilter($condition['eq']);
                } else {
                    $this->addStoreFilter();
                }
                break;
            default:
                parent::addFieldToFilter($field, $condition);
        }
        return $this;
    }

    public function setType($type){
        $this->_entityType = $type;
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
        $this->addFieldToFilter('main_table.entity_type', array('eq' => $type));
        return $this;
    }

    public function addUserFilter(){
        $this->addFieldToFilter('wrote_by', array('eq' => 0));
        return $this;
    }

}
