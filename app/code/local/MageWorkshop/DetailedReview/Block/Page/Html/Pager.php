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
class MageWorkshop_DetailedReview_Block_Page_Html_Pager extends Mage_Page_Block_Html_Pager
{
    /**
     * @inherit
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_pageVarName    = 'r_p';
        $this->_limitVarName   = 'r_limit';

        $availableLimitsArray = array();
        if ($limitsFlatArray = explode(',', Mage::getStoreConfig('detailedreview/list_options/qty_available'))) {
            foreach ($limitsFlatArray as $limit) {
                $availableLimitsArray[$limit] = $limit;
            }
        }
        $this->_availableLimit = $availableLimitsArray;

        $reviewsPerPage = (int) $this->getRequest()->getParam('r_limit');
        if ($reviewsPerPage && in_array($reviewsPerPage, $availableLimitsArray)) {
            $this->setLimit($reviewsPerPage);
        } else {
            $this->setLimit(Mage::getStoreConfig('detailedreview/list_options/qty_default'));
        }
    }

    /**
     * @param string $limit
     * @return string
     */
    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl(array('feedback' => 1, $this->getLimitVarName() => $limit));
    }

    /**
     * @param int $page
     * @return string
     */
    public function getPageUrl($page)
    {
        return $this->getPagerUrl(array('feedback' => 1, $this->getPageVarName() => $page));
    }
}
