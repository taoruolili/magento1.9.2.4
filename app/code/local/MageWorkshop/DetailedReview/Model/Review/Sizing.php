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

class MageWorkshop_DetailedReview_Model_Review_Sizing extends Varien_Object {
    const SIZING_RUNS_SMALL = 1;
    const SIZING_SNUG = 2;
    const SIZING_LITTLE_BIT_TIGHT = 3;
    const SIZING_TRUE_TO_SIZE = 4;
    const SIZING_LITTLE_BIT_LOOSE = 5;
    const SIZING_ROOMY = 6;
    const SIZING_RUNS_LARGE = 7;
    const STANDARD_THEME_COEF = 7;
    const BEIGE_THEME_COEF = 9;

    static public function getOptionArray() {
        $helper = Mage::helper('detailedreview');
        return array(
            self::SIZING_RUNS_SMALL => $helper->__('runs small'),
            self::SIZING_SNUG => $helper->__('snug'),
            self::SIZING_LITTLE_BIT_TIGHT => $helper->__('little bit tight'),
            self::SIZING_TRUE_TO_SIZE => $helper->__('true to size'),
            self::SIZING_LITTLE_BIT_LOOSE => $helper->__('little bit loose'),
            self::SIZING_ROOMY => $helper->__('roomy'),
            self::SIZING_RUNS_LARGE => $helper->__('runs large')
        );
    }

    public function getOptionValue($value) {
        foreach ($this->getOptionArray() as $key => $option) {
            if ($value == $key)
                return $option;
        }
        return $this->getOptionValue($this->getDefaultSizing());
    }

    public function getOptionWidth($sizing) {
        $theme = Mage::helper('detailedreview')->getCurrentTheme();
        switch ($theme) {
            case 'standard' : $coef = MageWorkshop_DetailedReview_Model_Review_Sizing::STANDARD_THEME_COEF; break;
            case 'beige'    : $coef = MageWorkshop_DetailedReview_Model_Review_Sizing::BEIGE_THEME_COEF; break;
        }
        foreach ($this->getOptionArray() as $key => $option) {
            if ($sizing == $key)
                return $coef + (100 - $coef) / ($this->count() - 1) * ($sizing - 1);
        }
        return $this->getOptionWidth($this->getDefaultSizing());
    }

    static public function getDefaultSizing(){
        return self::SIZING_TRUE_TO_SIZE;
    }

    public function count() {
        return count($this->getOptionArray());
    }

}