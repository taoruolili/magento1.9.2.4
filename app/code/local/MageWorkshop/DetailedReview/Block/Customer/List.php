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
class MageWorkshop_DetailedReview_Block_Customer_List extends Mage_Review_Block_Customer_List
{
    /**
     * @return float
     */
    public function getAverageRating()
    {
        $sum = 0;
        /** @var Mage_Catalog_Model_Product $item */
        foreach ($this->getCollection() as $item) {
            $sum += $item->getSum() / (20 * $item->getCount());
        }
        return round($sum / $this->getCollection()->count());
    }

    /**
     * @return int
     */
    public function getHelpfulVotes()
    {
        $sum = 0;
        /** @var Mage_Catalog_Model_Product $item */
        foreach ($this->getCollection() as $item) {
            $sum += $item->getCountHelpful();
        }
        return $sum;
    }

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
        $helper = $this->helper('detailedreview');
        $helper->applyTheme($this);
        return parent::_toHtml();
    }
}
