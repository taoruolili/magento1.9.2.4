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
class MageWorkshop_DetailedReview_Block_Adminhtml_Statistics_Grid_MostReviewedProducts extends Mage_Adminhtml_Block_Dashboard_Grid
{
    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mostReviewedProductsGrid');
        $this->setDefaultLimit(Mage::getStoreConfig('detailedreview/statistics_options/qty_items_in_reviewed_grid'));
    }

    /**
     * @inherit
     */
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $collection = Mage::getResourceModel('reports/review_product_collection')
            ->joinReview()
            ->addAttributeToSort('review_cnt', Zend_Db_Select::SQL_DESC );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize($this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        // Remove count of total orders $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    /**
     * @inherit
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('detailedreview');
        $this->addColumn('name', array(
            'header'    => $helper->__('Product Name'),
            'sortable'  => false,
            'index'     => 'name',
        ));

        $this->addColumn('review_cnt', array(
            'header'    => $helper->__('Number of Reviews'),
            'align'     => 'right',
            'width'     => '120',
            'sortable'  => false,
            'index'     => 'review_cnt'
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'align'     => 'center',
                'width'     => '50',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('View'),
                        'url'       => array('base'=> 'adminhtml/catalog_product/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * @inherit
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId()));
    }
}
