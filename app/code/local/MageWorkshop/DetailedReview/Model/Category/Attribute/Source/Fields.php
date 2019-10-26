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

class MageWorkshop_DetailedReview_Model_Category_Attribute_Source_Fields
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions($withoutEmptyOption = false)
    {
        if (is_null($this->_options)) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array(
                    'label' => Mage::helper('catalog')->__('None'),
                    'value' => 'none'
                ),
                array(
                    'label' => $helper->__('Good and Bad details'),
                    'value' => 'good_and_bad_detail'
                ),
                array(
                    'label' => $helper->__('Pros and Cons'),
                    'value' => 'pros_and_cons'
                ),
                array(
                    'label' => $helper->__('User-defined Pros and Cons'),
                    'value' => 'user_pros_and_cons'
                ),
                array(
                    'label' => $helper->__('Video'),
                    'value' => 'video'
                ),
                array(
                    'label' => $helper->__('Image'),
                    'value' => 'image'
                ),
                array(
                    'label' => $helper->__('Sizing'),
                    'value' => 'sizing'
                ),
                array(
                    'label' => $helper->__('About You Section'),
                    'value' => 'about_you'
                ),
                array(
                    'label' => $helper->__('Response'),
                    'value' => 'response'
                ),
            );
        }

        return $this->_options;
    }
}
