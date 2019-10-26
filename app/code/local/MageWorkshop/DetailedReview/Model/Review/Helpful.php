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

class MageWorkshop_DetailedReview_Model_Review_Helpful extends Mage_Core_Model_Abstract
{

    public function __construct()
    {
        $this->_init('detailedreview/review_helpful');
    }

    public function validate()
    {
        $errors = array();

        $helper = Mage::helper('detailedreview');

        if ($this->getCustomerId() == 0 && !Mage::getStoreConfig('detailedreview/settings/allow_guest_vote')) {
            $errors[] = $helper->__("Guest can't vote");
        }

        if (!Zend_Validate::is($this->getReviewId(), 'NotEmpty')) {
            $errors[] = $helper->__("Review Id can't be empty");
        }

        if (empty($errors)) {

            if ($this->getCustomerId() == 0) {
                if ($this->getCollection()
                    ->addReviewFilter($this->getReviewId())
                    ->addRemoteAddressFilter($this->getRemoteAddr())
                    ->getSize()
                ) {
                    $errors[] = $helper->__('Guest can\'t vote twice');
                }
            }elseif ($this->getCollection()
                    ->addReviewFilter($this->getReviewId())
                    ->addCustomerFilter($this->getCustomerId())
                    ->getSize()
            ) {
                $errors[] = $helper->__("Customer can't vote twice");
            } elseif (Mage::getModel('review/review')->load($this->getReviewId())->getCustomerId() == $this->getCustomerId()) {
                $errors[] = $helper->__("Customer can't vote for his own review");
            }
        }

        if ($this->getIsHelpful() != 0 &&  $this->getIsHelpful() != 1) {
            $errors[] = $helper->__('It can be 0 or 1');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    public function getIsCustomerVoted($reviewId){
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($customerId == 0) { return 0; }
        return $this->getCollection()->addReviewFilter($reviewId)->addCustomerFilter($customerId)->count();
    }

    public function getQtyHelpfulVotesForReview($reviewId){
        return $this->getCollection()->addReviewFilter($reviewId)->addHelpfulFilter()->count();
    }

    public function getQtyUnhelpfulVotesForReview($reviewId){
        return $this->getCollection()->addReviewFilter($reviewId)->addUnhelpfulFilter()->count();
    }

    public function getQtyVotesForReview($reviewId){
        return $this->getCollection()->addReviewFilter($reviewId)->count();
    }
}
