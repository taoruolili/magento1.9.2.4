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

class MageWorkshop_DetailedReview_Model_Mysql4_Review_Reports_Activity
{
    public function getConnection(){
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    public function getActivity($range, $customStart, $customEnd)
    {

        $connection = $this->getConnection();
        $select = new Zend_Db_Select($connection);

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

//        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
//            $range, 'created_at', $dateRange['from'], $dateRange['to']
//        );
        $tzRangeOffsetExpression = $this->_getRangeExpressionForAttribute($range, 'created_at');

        $from = new Zend_Date($dateRange['from']);
        $from = $from->toString($this->getTimeFormat($range));

        $to = new Zend_Date($dateRange['to']);
        $to = $to->toString($this->getTimeFormat($range));

        $select->from(Mage::getSingleton('core/resource')->getTableName('review'),array(
                'quantity' => 'COUNT(entity_id)',
                'range' => $tzRangeOffsetExpression,
            ))
            ->where('created_at > ?', $from )
            ->where('created_at < ?', $to )
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group($tzRangeOffsetExpression);

        $items = array();
        foreach ( $connection->fetchAll($select) as $key => $value ) {
            $items[$key] = new Varien_Object();
            $items[$key]->setData('quantity', $value['quantity']);
            $items[$key]->setData('range', $value['range']);
        }

        return $items;
    }

    protected function getTimeFormat ( $period ){
        $format = '';
        switch ($period) {
            case '24h':
                $format = 'yyyy-MM-dd HH:00';
                break;
            case '7d':
            case '1m':
                $format = 'yyyy-MM-dd';
                break;
            case '1y':
            case '2y':
                $format = 'yyyy-MM';
                break;
        }
        return $format;
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param boolean $returnObjects
     * @return array
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range)
        {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range)
        {
            case '24h':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';

                break;
            case '7d':
            case '1m':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }

        return $expression;
    }

    /**
     * Retriev range exression adapteted for attribute
     *
     * @param string $range
     * @param unknown_type $attribute
     */
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }
}
