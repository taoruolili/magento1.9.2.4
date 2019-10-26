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

class MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection extends Mage_Review_Model_Mysql4_Review_Collection
{

    protected $_salesFlatOrderTable;
    protected $_salesFlatOrderItemTable;
    protected $_ratingOptionVoteTable;
    protected $_reviewHelpfulTable;

    protected $_appliedFilters = array();

    public function __construct()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::__construct();
        }
        parent::__construct();
        $resources = Mage::getSingleton('core/resource');
        $this->_salesFlatOrderTable = $resources->getTableName('sales/order');
        $this->_salesFlatOrderItemTable = $resources->getTableName('sales/order_item');
        $this->_ratingOptionVoteTable = $resources->getTableName('rating/rating_option_vote');
        $this->_reviewHelpfulTable = $resources->getTableName('detailedreview/review_helpful');
    }

    protected function _initSelect()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_initSelect();
        }

        parent::_initSelect();
        $this->getSelect()
            ->columns(array(
                'detail.video',
                'detail.image',
                'detail.response',
                'detail.no_good_detail',
                'detail.good_detail',
                'detail.sizing',
                'detail.body_type',
                'detail.location',
                'detail.age',
                'detail.height',
                'detail.pros',
                'detail.cons',
                'detail.recommend_to'
            )
        );
        return $this;
    }

    public function resetTotalRecords()
    {
        if (!is_null($this->_totalRecords)) {
            $this->_totalRecords = null;
        }
        return $this;
    }

    public function setCustomOrder($sort = 'default')
    {
        if ($sort == 'date_asc') {
            $this->setDateOrder('ASC');
        } else if ($sort == 'rate_desc') {
            $this->setRateOrder('DESC');
        } else if ($sort == 'rate_asc') {
            $this->setRateOrder('ASC');
        } else if ($sort == 'most_helpful') {
            $this->setHelpfulOrder();
        } else if ($sort == 'ownership') {
            $this->setOwnershipOrder();
        } else {
            $this->setDateOrder();
        }
        return $this;
    }

    protected function setRateOrder($dir)
    {
        $fromTables = $this->_select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromTables['rov'])) {
            $this->getSelect()
                ->joinLeft(
                array('rov' => $this->_ratingOptionVoteTable),
                'rov.review_id = main_table.review_id',
                array('rate_value' => 'avg(rov.value)')
            )
                ->group('main_table.review_id');
        }
        $this->setOrder('rate_value', $dir);
    }

    public function addHelpfulInfo()
    {

        if (!isset($this->_appliedFilters['helpful'])) {

            $countHelpful = clone $this->getSelect();
            $countUnHelpful = clone $this->getSelect();
            $countCustomerVoted = clone $this->getSelect();

            $countHelpful->reset();
            $countUnHelpful->reset();
            $countCustomerVoted->reset();

            $countHelpful->from($this->_reviewHelpfulTable, "count(review_id)")
                ->where("is_helpful = 1 AND review_id = cur_rev_id");

            $countUnHelpful->from($this->_reviewHelpfulTable, "count(review_id)")
                ->where("is_helpful = 0 AND review_id = cur_rev_id");


            $customerId = (int)Mage::getSingleton('customer/session')->getCustomerId();
            $countCustomerVoted->from($this->_reviewHelpfulTable, "count(review_id)")
                ->where("(customer_id={$customerId}) AND review_id = cur_rev_id");

            $this->getSelect()
                ->joinLeft(
                array('rh' => $this->_reviewHelpfulTable),
                'rh.review_id = main_table.review_id',
                array(
                    'cur_rev_id' => 'rh.review_id',
                    'count_helpful' => "({$countHelpful})",
                    'count_unhelpful' => "({$countUnHelpful})",
                    'count_rh' => "(({$countHelpful})-($countUnHelpful))",
                    'is_customer_voted' => "({$countCustomerVoted})"
                )
            )
                ->group('main_table.review_id');
            $this->_appliedFilters['helpful'] = true;
        }
        return $this;
    }

    protected function setHelpfulOrder()
    {
        $this->addHelpfulInfo();
        $this->setOrder('count_rh', 'DESC');
    }

    public function addOwnershipInfo()
    {
        if (!isset($this->_appliedFilters['ownership'])) {
            $select = clone $this->getSelect();
            $select->reset();
            $select->from(array('so' => $this->_salesFlatOrderTable), "so.created_at")
                ->join(array('soi' => $this->_salesFlatOrderItemTable), 'so.entity_id=soi.order_id', null)
                ->where('so.customer_id=detail.customer_id AND soi.product_id=main_table.entity_pk_value')
                ->where('so.status="complete"')
                ->order('so.created_at ASC')
                ->limit(1);

            $this->getSelect()->columns(array('ownership' => "({$select})"));
            $this->_appliedFilters['ownership'] = true;
        }
        return $this;
    }

    protected function setOwnershipOrder()
    {
        $this->addOwnershipInfo();
        $this->setOrder('ownership', 'DESC');
    }

    public function addVerifiedBuyersFilter()
    {

        $this->addOwnershipInfo();
        $this->getSelect()
            ->having('ownership IS NOT NULL');

        return $this;
    }

    public function addVideoFilter()
    {
        $this->addFieldToFilter('detail.video', array('neq' => ''));
        return $this;
    }

    public function addImagesFilter()
    {
        $this->addFieldToFilter('detail.image', array('neq' => ''));
        return $this;
    }

    public function addManuResponseFilter()
    {
        $this->addFieldToFilter('detail.response', array('neq' => ''));
        return $this;
    }

    public function addHighestContributorFilter()
    {
        $select = clone $this->getSelect();
        $select->reset();
        $select->from(Mage::getSingleton('core/resource')->getTableName('review/review_detail'), array('customer_id'))
            ->group('customer_id')
            ->having("customer_id IS NOT NULL")
            ->having('COUNT(1) > ?', Mage::getStoreConfig('detailedreview/filters/qty_items_in_highest_contributors'));
        $this->getSelect()->where('detail.customer_id IN ?', $select);
        return $this;
    }

    public function addDateRangeFilter($range)
    {
        $quoteDate = 0;
        if ($range == 2) {
            $quoteDate = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
        } else if ($range == 3) {
            $quoteDate = mktime(0, 0, 0, date("m"), date("d") - 7 * 4, date("Y"));
        } else if ($range == 4) {
            $quoteDate = mktime(0, 0, 0, date("m") - 6, date("d"), date("Y"));
        }
        $quoteDate = Mage::getSingleton('core/date')->gmtDate(null, $quoteDate);
        $this->addFilter('date_range',
            $this->getConnection()->quoteInto('main_table.created_at > ?', $quoteDate),
            'string');
        return $this;
    }

    public function addKeywordsFilter($q)
    {
        $this->addFilter('keywords',
            $this->getConnection()->quoteInto('(detail.title LIKE ? or detail.detail LIKE ? or detail.good_detail LIKE ? or detail.no_good_detail LIKE ?)', '%' . $q . '%'),
            'string');
        return $this;
    }

    public function addUserReviewFilter()
    {
        $this->addFieldToFilter('detail.customer_id', array('eq' =>  Mage::getSingleton('customer/session')->getCustomer()->getId()));
        return $this;
    }

    public function getSelectCountSql()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::getSelectCountSql();
        }

        $this->_renderFilters();

        $select = clone $this->getSelect();

        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $havingArray = $select->getPart(Zend_Db_Select::HAVING);
        $havingKeys = array();
        if (count($havingArray)) {
            foreach ($havingArray as $having) {
                $havingKeys[] = preg_replace('/.*?([A-Za-z0-9_.-]+) .*/', '$1', $having);
            }

            $columns = $select->getPart(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::COLUMNS);

            foreach ($columns as $column) {
                if (in_array($column[2], $havingKeys)) {
                    $select->columns(array($column[2] => $column[1]));
                    $havingKeys = array_diff($havingKeys, array($column[2]));
                }
            }
        } else {
            $select->reset(Zend_Db_Select::COLUMNS);
        }

        $select->columns('main_table.review_id');

        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $countSelect->from(array('virtual' => new Zend_Db_Expr("({$select})")), "count(1)");

        return $countSelect;
    }

    /**
     * @return float
     */
    public function getAverageSizing()
    {
        $select = clone $this->getSelect();
        $select->columns(new Zend_Db_Expr("avg(sizing) AS avg_sizing"))
            ->group('entity_pk_value');
        $result = $this->getConnection()->fetchRow($select);
        return round($result['avg_sizing']);
    }

    public function  __clone()
    {
        $this->_select = clone $this->_select;
        return $this;
    }
}
