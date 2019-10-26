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

class MageWorkshop_DetailedReview_Model_Review_Sorting
{
    protected $_options;

    protected $_availableOptions = array();
    protected $_currentSorting;
    protected $_queryVar = 'sort';


    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $helper = Mage::helper('detailedreview');
            $this->_options = array(
                array('value'=>'date_desc', 'label'=> $helper->__('Date - Newest First')),
                array('value'=>'date_asc', 'label'=> $helper->__('Date - Latest First')),
                array('value'=>'rate_desc', 'label'=> $helper->__('Highest Rated')),
                array('value'=>'rate_asc', 'label'=> $helper->__('Lowest Rated')),
                array('value'=>'most_helpful', 'label'=> $helper->__('Most Helpful')),
                array('value'=>'ownership', 'label'=> $helper->__('Ownership'))
            );
        }
        
        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('-- Please Select --')));
        }

        return $options;
    }

    public function getAvailableOptions() {
        if ( !$this->_availableOptions ) {
            $availableSorting = explode(',',Mage::getStoreConfig('detailedreview/sorting_options/allow_sorting_by'));
            foreach ( $this->toOptionArray(false) as $sorting){
                if ( in_array( $sorting['value'], $availableSorting ) )
                    $this->_availableOptions[$sorting['value']] = $sorting['label'];
            }
        }
        return $this->_availableOptions;
    }

    /**
     * @return string
     */
    public function getCurrentSorting() {
        if ( !$this->_currentSorting ) {
            if ( !isset($_GET[$this->_queryVar]) || ( isset($_GET[$this->_queryVar]) && !$this->_currentSorting = array_intersect_key( $this->getAvailableOptions(), array($_GET[$this->_queryVar]=>1) ) ) ) {
                $defaultOrdering = Mage::getStoreConfig('detailedreview/list_options/ordering');
                if ( !$this->_currentSorting = array_intersect_key( $this->getAvailableOptions(), array($defaultOrdering=>1) ) ) {
                    return key($this->getAvailableOptions());
                }
            }
            end($this->_currentSorting);
            $this->_currentSorting = key($this->_currentSorting);
        }
        return $this->_currentSorting;
    }

}