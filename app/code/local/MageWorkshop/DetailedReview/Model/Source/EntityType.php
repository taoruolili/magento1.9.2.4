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

class MageWorkshop_DetailedReview_Model_Source_EntityType
{
    const CONS = 'C';
    const PROS = 'P';
    const ALL = 'A';

    static function toShortOptionArray(){
        $helper = Mage::helper('detailedreview');
        return array(
            self::PROS     => $helper->__('Pros'),
            self::CONS     => $helper->__('Cons'),
            self::ALL     => $helper->__('User-defined Pros and Cons')
        );
    }

    static function toShortOptionPCOnlyArray(){
        $helper = Mage::helper('detailedreview');
        return array(
            self::PROS     => $helper->__('Pros'),
            self::CONS     => $helper->__('Cons')
        );
    }

    static function getEntityNameByType($type){
        if ($type != null) {
            $entities = self::toShortOptionArray();
            return $entities[$type];
        }
        return false;
    }

    static function getClassNameByType($type){
        if ($type) {
            $entities = array(
                self::PROS     => 'pros',
                self::CONS     => 'cons',
                self::ALL     => 'userproscons'
            );
            return $entities[$type];
        }
        return false;
    }
}