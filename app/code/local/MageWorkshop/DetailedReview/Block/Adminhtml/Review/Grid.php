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
class MageWorkshop_DetailedReview_Block_Adminhtml_Review_Grid extends Mage_Adminhtml_Block_Review_Grid
{
    /**
     * @inherit
     */
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $helper = Mage::helper('detailedreview');
        $this->getMassactionBlock()->addItem('ban_author_for', array(
            'label'      => $helper->__('Prevent this Author from Posting Reviews'),
            'url'        => $this->getUrl('detailedreviewadmin/adminhtml_customer/massBanning'),
            'additional' => array(
                'status' => array(
                    'name'   => 'ban_author_for',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $helper->__("For how long Author’s IP should be banned."),
                    'values' => array(
                        30   => $helper->__('30 Days'),
                        90   => $helper->__('90 Days'),
                        180  => $helper->__('180 Days'),
                        360  => $helper->__('360 Days'),
                        9999 => $helper->__('Permanently'),
                    )
                ))
            )
        );

        return $this;
    }
}
