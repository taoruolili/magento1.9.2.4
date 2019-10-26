<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */

class Amasty_File_Block_Adminhtml_Report_Report_Grid extends Mage_Adminhtml_Block_Report_Grid
{
    protected $_subReportSize = false;

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridIcon');
    }
 
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('amfile/report_collection');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    =>Mage::helper('amfile')->__('Product ID'),
            'index'     =>'product_id',
            'sortable'  => true
        ));

        $this->addColumn('name', array(
            'header'    =>Mage::helper('amfile')->__('Product Name'),
            'index'     =>'name',
            'sortable'  => true,
        ));
        $this->addColumn('file_name', array(
            'header'    =>Mage::helper('amfile')->__('File Name'),
            'index'     =>'file_name',
            'sortable'  => true
        ));
        $this->addColumn('label', array(
            'header'    =>Mage::helper('amfile')->__('Default File Label'),
            'index'     =>'label',
            'sortable'  => true
        ));
        $this->addColumn('file_url', array(
            'header'    =>Mage::helper('amfile')->__('File Url'),
            'index'     =>'file_url',
            'sortable'  => true
        ));
        $this->addColumn('rating', array(
            'header'    =>Mage::helper('amfile')->__('File Rating'),
            'index'     =>'rating',
            'sortable'  => true
        ));

        $this->addExportType('*/*/exportSimpleCsv', Mage::helper('reports')->__('CSV'));

        return parent::_prepareColumns();
    }
}
