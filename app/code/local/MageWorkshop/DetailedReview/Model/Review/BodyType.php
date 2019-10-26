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

class MageWorkshop_DetailedReview_Model_Review_BodyType
{
    protected $_options;

    const BODY_TYPE_TRIANGLE    = 1;
    const BODY_TYPE_PEAR        = 2;
    const BODY_TYPE_RECTANGLE   = 3;
    const BODY_TYPE_HOURGLASS   = 4;
    const BODY_TYPE_DIAMOND     = 5;
    const BODY_TYPE_ROUNDED     = 6;

    public function getOptionArray()
    {
        return array(
            self::BODY_TYPE_TRIANGLE    => 'triangle',
            self::BODY_TYPE_PEAR        => 'pear',
            self::BODY_TYPE_RECTANGLE   => 'rectangle',
            self::BODY_TYPE_HOURGLASS   => 'hourglass',
            self::BODY_TYPE_DIAMOND     => 'diamond',
            self::BODY_TYPE_ROUNDED     => 'rounded'
        );
    }

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $this->_options = $this->getOptionArray();
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, Mage::helper('adminhtml')->__('-- Please Select --'));
        }

        return $options;
    }

    public function getOptionValue($value){
        foreach ( $this->getOptionArray() as $key => $option ) {
            if ( $value == $key ) return $option;
        }
        return $this->getOptionValue(self::BODY_TYPE_PEAR);
    }
}