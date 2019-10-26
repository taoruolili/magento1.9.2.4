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

class MageWorkshop_DetailedReview_Helper_Adminhtml_Statistics_Activity extends Mage_Adminhtml_Helper_Dashboard_Abstract
{

    protected  $_items;

    public function __construct(){}

    protected function _initCollection()
    {
        $this->_items = Mage::getResourceSingleton('detailedreview/review_reports_activity')
            ->getActivity($this->getParam('period'), 0, 0);

    }

    public function getCollection(){
        return $this;
    }

    public function getItems(){
        if ( !$this->_items ){
            $this->_initCollection();
        }
        return $this->_items;
    }

    public function getCount(){
        foreach ( $this->getItems() as $item ){
            if ( $item->getQuantity() ) return true;
        }
    }

}
