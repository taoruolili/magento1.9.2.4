<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_CheckoutPromotion
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Customblock_Shoppingcart_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('onestepcheckoutGrid');
        $this->setDefaultSort('onestepcheckout_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magegiant_CheckoutPromotion_Block_Adminhtml_Checkoutpromotion_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('onestepcheckout/customblock_shoppingcart')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magegiant_CheckoutPromotion_Block_Adminhtml_Checkoutpromotion_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => Mage::helper('salesrule')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'rule_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('salesrule')->__('Rule Name'),
            'align'  => 'left',
            'index'  => 'name',
        ));

        $this->addColumn('from_date', array(
            'header' => Mage::helper('salesrule')->__('Date Start'),
            'align'  => 'left',
            'width'  => '120px',
            'type'   => 'date',
            'index'  => 'from_date',
        ));

        $this->addColumn('to_date', array(
            'header'  => Mage::helper('salesrule')->__('Date Expire'),
            'align'   => 'left',
            'width'   => '120px',
            'type'    => 'date',
            'default' => '--',
            'index'   => 'to_date',
        ));

        $this->addColumn('is_active', array(
            'header'  => Mage::helper('salesrule')->__('Status'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'is_active',
            'type'    => 'options',
            'options' => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('rule_website', array(
                'header'   => Mage::helper('salesrule')->__('Website'),
                'align'    => 'left',
                'index'    => 'website_ids',
                'type'     => 'options',
                'sortable' => false,
                'options'  => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'width'    => 200,
            ));
        }

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('salesrule')->__('Priority'),
            'align'  => 'right',
            'index'  => 'sort_order',
            'width'  => 100,
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('onestepcheckout')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('onestepcheckout')->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    )),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('onestepcheckout')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('onestepcheckout')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magegiant_CheckoutPromotion_Block_Adminhtml_Checkoutpromotion_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('onestepcheckout_id');
        $this->getMassactionBlock()->setFormFieldName('onestepcheckout');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('onestepcheckout')->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('onestepcheckout')->__('Are you sure?')
        ));

        $status = Mage::getSingleton('onestepcheckout/status')->getOptionArray();
//        array_unshift($status, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'      => Mage::helper('onestepcheckout')->__('Change status'),
            'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => Mage::helper('onestepcheckout')->__('Status'),
                    'values' => $status
                ))
        ));

        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}