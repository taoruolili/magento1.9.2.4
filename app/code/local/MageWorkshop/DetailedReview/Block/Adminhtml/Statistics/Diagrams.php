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
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    /**
     * @inherit
     */
    protected function _prepareLayout()
    {
        /** @var MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Tab_Activity $activityBlock */
        $activityBlock = $this->getLayout()->createBlock('detailedreview/adminhtml_statistics_tab_activity');
        $this->addTab('activity', array(
            'label'     => $this->__('Activity'),
            'content'   => $activityBlock->toHtml(),
            'active'    => true
        ));
        return parent::_prepareLayout();
    }
}
