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
class MageWorkshop_DetailedReview_Block_Adminhtml_Userproscons_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @inherit
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('detailedreview/review_proscons')->getCollection();
        $this->setCollection($collection);
        $collection->addUserFilter();
        parent::_prepareCollection();
        $collection->addStoreData();
        return $this;
    }

    /**
     * @inherit
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('detailedreview');

        $this->addColumn('entity_id', array(
            'header'    => $helper->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('entity_type', array(
            'width'     => '150px',
            'header'    => $helper->__('Type'),
            'index'     => 'entity_type',
            'type'    => 'options',
            'options' => Mage::getModel('detailedreview/source_entityType')->toShortOptionPCOnlyArray()
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('status',
            array(
                'header'  => $helper->__('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getModel('detailedreview/source_common_status')->toOptionArray()
            ));

        $this->addColumn('wrote_by',
            array(
                'header'  => $helper->__('Wrote By'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'wrote_by',
                'type'    => 'options',
                'options' => array(MageWorkshop_DetailedReview_Model_Source_Common_Wroteby::CUSTOMER => $helper->__('Customer'))
            ));

        $this->addColumn('sort_order', array(
            'header'    => $helper->__('Sort Order'),
            'width'     => '50px',
            'index'     => 'sort_order',
            'type'  => 'number',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids', array(
                'header'    => Mage::helper('review')->__('Visible In'),
                'index'     => 'store_ids',
                'type'      => 'store',
                'store_view' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    /**
     * @inherit
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('review_proscons');

        $helper = Mage::helper('detailedreview');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => $helper->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $helper->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('update_status', array(
            'label' => $helper->__('Update status'),
            'url' => $this->getUrl('*/*/massUpdateStatus'),
            'additional' => array(
                'status' => array(
                    'name'   => 'update_status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $helper->__('What do?'),
                    'values' => array(
                        1 => $helper->__('Enable'),
                        0 => $helper->__('Disable')
                    )
                ))
        ));

        return $this;
    }

    /**
     * @inherit
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getId()));
    }

}