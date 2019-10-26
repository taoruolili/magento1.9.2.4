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
class MageWorkshop_DetailedReview_Block_Product_View_List extends Mage_Review_Block_Product_View_List
{
    const XML_PATH_ALLOW_VIDEO_PREVIEW = 'detailedreview/show_settings/allow_video_preview';

    /**
     * @return Mage_Review_Model_Resource_Review_Collection|MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     */
    public function getReviewsCollection()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::getReviewsCollection();
        }
        if (is_null($this->_reviewsCollection)) {
            $this->_reviewsCollection = Mage::getSingleton('detailedreview/review')->getReviewsCollection();
        }
        return $this->_reviewsCollection;
    }

    /**
     * @inherit
     */
    protected function _beforeToHtml()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_beforeToHtml();
        }
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $reviewCollection */
        $reviewCollection = $this->getReviewsCollection();
        $reviewCollection->addHelpfulInfo();
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_beforeToHtml();
    }

    public function getReviewsCountWithoutFilters()
    {
        return Mage::getSingleton('detailedreview/review')->getReviewsCountWithoutFilters();
    }
}
