<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */
class Amasty_File_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action 
{
    public function reportAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/amfile/amfile_report')
            ->_addBreadcrumb($this->__('Catalog'), $this->__('Catalog'))
            ->_addBreadcrumb($this->__('Product Attachments'), $this->__('Product Attachments'))
            ->_addBreadcrumb($this->__('Download Report'), $this->__('Download Report'));

        $this
            ->_title($this->__('Catalog'))
            ->_title($this->__('Product Attachments'))
            ->_title($this->__('Download Report'));

        $this->_addContent($this->getLayout()->createBlock('amfile/adminhtml_report_report'));

        $this->renderLayout();
    }
 
    public function exportSimpleCsvAction()
    {
        $fileName   = 'report.csv';
        $content    = $this->getLayout()->createBlock('amfile/adminhtml_report_report_grid')->getCsv();
 
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function deleteAction()
    {
        Mage::getResourceModel("amfile/stat")->deleteStat();
        $this->_redirectReferer();
    }
}
