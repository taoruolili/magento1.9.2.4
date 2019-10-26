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

/**
 * Class MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed
 *
 * @method MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed setSummary(float $float)
 * @method MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed setCountReviewsWithRating(float)
 */
class MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed extends Mage_Core_Block_Template
{
    protected $_reviewCollections = array();
    protected $_ratingCollection;
    protected $_qtyMarks = array();
    protected $_availableSorts = array();

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('detailedreview/rating/detailed.phtml');
    }

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        $entityId = Mage::app()->getRequest()->getParam('id');
        if (intval($entityId) <= 0) {
            return '';
        }

        $reviewsCount = Mage::getModel('review/review')
            ->getTotalReviews($entityId, true, Mage::app()->getStore()->getId());
        if ($reviewsCount == 0) {
            $this->setTemplate('detailedreview/rating/empty.phtml');
            return parent::_toHtml();
        }

        $ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection();
        $ratingCollection->addEntityFilter('product')
            ->setPositionOrder()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->load();

        if ($entityId) {
            $ratingCollection->addEntitySummaryToItem($entityId, Mage::app()->getStore()->getId());
        }
        $this->calculateSummary();
        $this->_ratingCollection = $ratingCollection;
        $this->assign('collection', $ratingCollection);
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_toHtml();
    }

    /**
     * @return $this
     */
    public function calculateSummary()
    {
        $summary = $sum = 0;
        foreach ($this->getQtyMarks() as $key => $value) {
            if (!$key) continue;
            $summary += $key * $value * 20;
            $sum += $value;
        }
        if($sum) {
            $this->setSummary(round($summary / $sum))
                 ->setCountReviewsWithRating($sum);
        }
        return $this;
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getQtyMarks($range = 0)
    {
        if (!isset($this->_qtyMarks[$range])) {
            $reviewsIds = array();
            /** @var MAge_Review_Model_Review $review */
            foreach ($this->getReviewCollection($range) as $review) {
                $reviewsIds[] = $review->getId();
            }
            $this->_qtyMarks[$range] = Mage::getModel('detailedreview/rating_option_vote')->getQtyMarks($reviewsIds);
        }
        return $this->_qtyMarks[$range];
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getQtyByRange($range = 0) {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $collection */
        $collection = $this->getReviewCollection($range);
        return $collection->count();
    }

    /**
     * @return float
     */
    public function getAverageSizing()
    {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $collection */
        $collection = $this->getReviewCollection();
        return $collection->getAverageSizing();
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getReviewCollection($range = 0)
    {
        $params = Mage::app()->getRequest()->getParams();
        $range = ($range != 0) ? $range : ((isset($params['st'])) ? $params['st'] : 0);
        if (!isset($this->_reviewCollections[$range])) {
            $reviewCollection = Mage::getSingleton('detailedreview/review')->getReviewsCollection(true, $range);
            $this->_reviewCollections[$range] = $reviewCollection;
        }
        return $this->_reviewCollections[$range];
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * @param bool $ratingsEnabled
     * @return array
     */
    public function getAvailableSorts($ratingsEnabled)
    {
        $options = Mage::getSingleton('detailedreview/review_sorting')->getAvailableOptions();
        if (!$ratingsEnabled){
            unset($options['rate_desc']);
            unset($options['rate_asc']);
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getCurrentSorting()
    {
        return Mage::getSingleton('detailedreview/review_sorting')->getCurrentSorting();
    }

    /**
     * @return array
     */
    public function getAvailableFilterAttributes()
    {
        $helper = Mage::helper('detailedreview');
        $availableFilterAttributes = array(
            'vb' => $helper->__('Verified Buyers')
        );

        if ($helper->checkFieldAvailable('image')) {
            $availableFilterAttributes['ir'] = $helper->__('Reviews with Images');
        }
        if ($helper->checkFieldAvailable('video')) {
            $availableFilterAttributes['vr'] = $helper->__('Reviews with Video');
        }
        if ($helper->checkFieldAvailable('response')) {
            $availableFilterAttributes['mr'] = $helper->__('Administration Response');
        }
        $availableFilterAttributes['hc'] = $helper->__('Highest Contributors');

        return $availableFilterAttributes;
    }

    /**
     * @return array
     */
    public function getAvailableDateRanges()
    {
        $helper = Mage::helper('detailedreview');
        return array(
            1 => $helper->__('My Reviews'),
            2 => $helper->__('Last Week'),
            3 => $helper->__('Last 4 Weeks'),
            4 => $helper->__('Last 6 Months'),
            999 => $helper->__('All Reviews')
        );
    }

    /**
     * @return string
     */
    public function getClearFiltersUrl()
    {
        /** @var Mage_Core_Helper_Url $coreUrl */
        $coreUrl = $this->helper('core/url');
        $url = preg_replace('/\?.*/', '', $coreUrl->getCurrentUrl());
        $params = $_GET;
        $filters = array('st', 'vb', 'ir', 'vr', 'hc', 'mr', 'keywords');
        foreach ($params as $key => $value){
            if (in_array($key, $filters)) {
                unset($params[$key]);
            }
        }
        $params['feedback'] = 1;
        $url = Mage::helper('detailedreview')->addRequestParam($url, $params);
        return $url;
    }
}
