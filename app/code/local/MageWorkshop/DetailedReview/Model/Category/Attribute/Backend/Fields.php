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

class MageWorkshop_DetailedReview_Model_Category_Attribute_Backend_Fields
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Before Attribute Save Process
     *
     * @param Varien_Object $object
     * @return MageWorkshop_DetailedReview_Model_Category_Attribute_Backend_Fields
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'review_fields_available') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = array();
            }
            $object->setData($attributeCode, join(',', $data));
        }
        if (is_null($object->getData($attributeCode))) {
            $object->setData($attributeCode, false);
        }
        return $this;
    }

    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'review_fields_available') {
            $data = $object->getData($attributeCode);
            if ($data) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }

        return $this;
    }

    public function validate($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $postDataConfig = ($object->getData('use_post_data_config'))? $object->getData('use_post_data_config') : array();

        $isUseConfig = false;
        if ($postDataConfig) {
            $isUseConfig = in_array($attributeCode, $postDataConfig);
        }

        if ($this->getAttribute()->getIsRequired()) {
            $attributeValue = $object->getData($attributeCode);
            if ($this->getAttribute()->isValueEmpty($attributeValue)) {
                if (is_array($attributeValue) && count($attributeValue)>0) {
                } else {
                    if(!$isUseConfig) {
                        return false;
                    }
                }
            }
        }

        if ($attributeCode == 'review_fields_available') {
            if ($available = $object->getData('review_fields_available')) {
                if ($object->getReviewFieldsAvailable() && is_array($object->getReviewFieldsAvailable())) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (!in_array('available_sort_by', $postDataConfig)) {
                    Mage::throwException(Mage::helper('eav')->__('Default Product Listing Sort by not exists on Available Product Listing Sort By'));
                }
            }
        }

        return true;



    }
}