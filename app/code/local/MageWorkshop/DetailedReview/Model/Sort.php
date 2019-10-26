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

class MageWorkshop_DetailedReview_Model_Sort
{
    public function getConnection(){
        return Mage::getSingleton('core/resource')->getConnection('default_setup');
    }

    public function refreshAllIndices()
    {
        $this->refreshReviewIndex();
        $this->refreshOrderIndex();
        
        return $this;
    }
    
    public function refreshReviewIndex()
    {
        $resource = Mage::getResourceSingleton('catalog/product');

        $resources = Mage::getSingleton('core/resource');
        $reviewTable  = $resources->getTableName('review/review');

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->getSelect()
                        ->join(array('review'=>$reviewTable), 'e.entity_id=review.entity_pk_value', 'COUNT(review.entity_pk_value) as total_reviews')
                        ->group('e.entity_id');

        $joinCondition      = array(
            'e.entity_id = table_rating.entity_pk_value',
            $this->getConnection()->quoteInto('table_rating.store_id > ?', 0)
        );

        $percentField       = $this->getConnection()->quoteIdentifier('table_rating.percent');
        $sumPercentField    = new Zend_Db_Expr("SUM({$percentField})");
        $countRatingId      = new Zend_Db_Expr('COUNT(table_rating.rating_id)');

        $productCollection->getSelect()
                ->joinLeft(
                    array('table_rating' => $resources->getTableName('rating/rating_vote_aggregated')),
                    implode(' AND ', $joinCondition),
                    array('avg_rating'          => sprintf('%s/%s', $sumPercentField, $countRatingId)));

        foreach($productCollection as $product) {
            $product->setData('popularity_by_reviews', ((int) $product->getTotalReviews()));
            $product->setData('popularity_by_rating', ((int) $product->getAvgRating()));
            $resource->saveAttribute($product, 'popularity_by_reviews');
            $resource->saveAttribute($product, 'popularity_by_rating');
        }
        
        $this->_updateFlatProductTable('popularity_by_reviews');
        $this->_updateFlatProductTable('popularity_by_rating');

    }


    
    protected function _updateFlatProductTable($attributeCode)
    {
        $indexer = Mage::getResourceModel('catalog/product_flat_indexer');
        $attribute = $indexer->getAttribute($attributeCode);
        foreach (Mage::app()->getStores() as $store) {
            $indexer->updateAttribute($attribute, $store->getId());
        } 
        
        return $this;
    }
    
    public function refreshOrderIndex()
    {        
        $resource = Mage::getResourceSingleton('catalog/product');
        
        /* @var $soldCollection Mage_Reports_Model_Mysql4_Product_Sold_Collection */
        $soldCollection = Mage::getResourceModel('reports/product_sold_collection');
        $this->addOrdersCountToProductCollection($soldCollection);

        $soldCollection->getSelect()->having('orders > 0');

        foreach($soldCollection as $product) {
            $product->setData('popularity_by_sells', (int) $product->getOrders());
            $resource->saveAttribute($product, 'popularity_by_sells');
        }
        
        return $this;
    }

    protected function addOrdersCountToProductCollection($collection) {
        $from = $this->_getFromDate();
        $to = $this->_getToday();

        $orderItemTableName = $collection->getTable('sales/order_item');
        $productFieldName   = 'e.entity_id';

        $collection->getSelect()
            ->joinLeft(
                array('order_items' => $orderItemTableName),
                "order_items.product_id = {$productFieldName}",
                array())
            ->columns(array('orders' => 'COUNT(order_items2.item_id)'))
            ->group($productFieldName);

        $dateFilter = array('order_items2.item_id = order_items.item_id');
        if ($from != '' && $to != '') {
            $dateFilter[] = sprintf('(order_items2.created_at BETWEEN "%s" AND "%s")', $from, $to);
        }

        $collection->getSelect()
            ->joinLeft(
                array('order_items2' => $orderItemTableName),
                implode(' AND ', $dateFilter),
                array()
            );
    }


    /**
     * Retrieve start time for report
     * 
     * @return string
     */
    protected function _getFromDate()
    {
        $date = new Zend_Date;
        $date->subDay(10);
        return $date->getIso();
    }
    
    /**
     * Retrieve now
     * 
     * @return string
     */
    protected function _getToday()
    {
        $date = new Zend_Date;
        return $date->getIso();
    } 
}

