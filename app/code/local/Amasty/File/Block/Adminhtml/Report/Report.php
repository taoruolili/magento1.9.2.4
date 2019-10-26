<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */
class Amasty_File_Block_Adminhtml_Report_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amfile';
        $this->_controller = 'adminhtml_report_report';
        $this->_headerText = $this->__('Download Report');

        $this->_addButton('Reset Report', array(
            'label' => $this->__('Reset Report'),
            'onclick' => "setLocation('" . $this->getUrl('*/*/delete', array('page_key' => 'collection')) . "')",
            'class' => 'delete'
        ));
        parent::__construct();

        $this->_removeButton('add');
    }
}
