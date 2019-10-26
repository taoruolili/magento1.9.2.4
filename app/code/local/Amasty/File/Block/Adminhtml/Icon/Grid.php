<?php
class Amasty_File_Block_Adminhtml_Icon_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
         
   parent::__construct();
        $this->setId('iconGrid');
        $this->setDefaultSort('id');
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'amfile/icon_collection';
    }
     
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'id'
            )
        );
         
        $this->addColumn('type',
            array(
                'header'=> $this->__('Type'),
                'index' => 'type'
            )
        );
        $this->addColumn('image', array(
          'header'    => __('Icon Image'),
          'align'     =>'left',
          'index'     => 'image',
          'renderer'  => 'amfile/adminhtml_renderer_icon'
      ));
        $this->addColumn('active',
            array(
                'header'=> $this->__('Active'),
                'index' => 'active',
                'type'      => 'options',
                'options' => array(1 => "Yes",0 =>"No"),
            )
        );
         
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('icon_id');
        $this->getMassactionBlock()->setFormFieldName('icons');
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('amfile')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('amfile')->__('Are you sure?')
        ));
        
        return $this; 
    }
}