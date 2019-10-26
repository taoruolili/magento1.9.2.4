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
class Mage_Reports_Model_Resource_Review_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_useAnalyticFunction = true;
    }
    /**
     * Join review table to result
     *
     * @return Mage_Reports_Model_Resource_Review_Product_Collection
     */
    public function joinReview()
    {
        $helper    = Mage::getResourceHelper('core');

        $subSelect = clone $this->getSelect();
        $subSelect->reset()
            ->from(array('rev' => $this->getTable('review/review')), 'COUNT(DISTINCT rev.review_id)')
            ->where('e.entity_id = rev.entity_pk_value');

        $this->addAttributeToSelect('name');

        $this->getSelect()
            ->join(
                array('r' => $this->getTable('review/review')),
                'e.entity_id = r.entity_pk_value',
                array(
                    'review_cnt'    => new Zend_Db_Expr(sprintf('(%s)', $subSelect)),
                    'last_created'  => 'MAX(r.created_at)',))
            ->group('e.entity_id');

        $joinCondition      = array(
            'e.entity_id = table_rating.entity_pk_value',
            $this->getConnection()->quoteInto('table_rating.store_id > ?', 0)
        );

        /**
         * @var $groupByCondition array of group by fields
         */
        $groupByCondition   = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
        $percentField       = $this->getConnection()->quoteIdentifier('table_rating.percent');
        $sumPercentField    = $helper->prepareColumn("SUM({$percentField})", $groupByCondition);
        $sumPercentApproved = $helper->prepareColumn('SUM(table_rating.percent_approved)', $groupByCondition);
        $countRatingId      = $helper->prepareColumn('COUNT(table_rating.rating_id)', $groupByCondition);

        $this->getSelect()
            ->joinLeft(
                array('table_rating' => $this->getTable('rating/rating_vote_aggregated')),
                implode(' AND ', $joinCondition),
                array(
                    'avg_rating'          => sprintf('%s/%s', $sumPercentField, $countRatingId),
                    'avg_rating_approved' => sprintf('%s/%s', $sumPercentApproved, $countRatingId),
            ));
//ECHO $this->getSelect();dd();
        return $this;
    }

    /**
     * Add attribute to sort
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Review_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (in_array($attribute, array('review_cnt', 'last_created', 'avg_rating', 'avg_rating_approved'))) {
            $this->getSelect()->order($attribute.' '.$dir);
            return $this;
        }

        return parent::addAttributeToSort($attribute, $dir);
    }
}
