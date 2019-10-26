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
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics extends Mage_Adminhtml_Block_Template
{
    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('detailedreview/index.phtml');
    }

    /**
     * @inherit
     */
    protected function _prepareLayout()
    {
        $this->setChild('mostReviewedProducts',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostReviewedProducts')
        );
        $this->setChild('mostLikedProducts',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostLikedProducts')
        );
        $this->setChild('mostDislikedProducts',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostDislikedProducts')
        );
        $this->setChild('mostActiveUsers',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_grid_mostActiveCustomers')
        );

        $this->setChild('diagrams',
            $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_diagrams')
        );

        parent::_prepareLayout();
    }
}
