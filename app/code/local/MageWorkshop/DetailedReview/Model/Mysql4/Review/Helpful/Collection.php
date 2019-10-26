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

class MageWorkshop_DetailedReview_Model_Mysql4_Review_Helpful_Collection extends Varien_Data_Collection_Db
{
//    protected $_reviewTable;
    protected $_reviewHelpfulTable;

    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');
        parent::__construct($resources->getConnection('detailedreview_read'));

//        $this->_reviewTable         = $resources->getTableName('review/review');
        $this->_reviewHelpfulTable  = $resources->getTableName('detailedreview/review_helpful');

        $this->_select->from(array('main_table'=>$this->_reviewHelpfulTable));
    }

    public function addCustomerFilter($customerId)
    {
        $this->addFilter('customer',
            $this->getConnection()->quoteInto('main_table.customer_id=?', $customerId),
            'string');
        return $this;
    }

    public function addRemoteAddressFilter($remoteAddress)
    {
        $this->addFilter('main_table.remote_addr', $remoteAddress);
        return $this;
    }
    
    public function addReviewFilter($reviewId)
    {
        $this->addFilter('customer',
            $this->getConnection()->quoteInto('main_table.review_id=?', $reviewId),
            'string');
        return $this;
    }
    
    public function addHelpfulFilter()
    {
        $this->addFilter('customer',
            $this->getConnection()->quoteInto('main_table.is_helpful=?', 1),
            'string');
        return $this;
    }
}
