<?php

class Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter('paypal_account', array(
            'header'=> Mage::helper('sales')->__('Paypal Account'),
            'width' => '180px',
            'type'  => 'text',
            'index' => 'paypal_account',
            'renderer'  => 'Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer',
            'sortable'  => false,
            'filter'    => false,
        ), 'status');

        $this->addColumnAfter('coupon_code', array(
            'header'=> Mage::helper('sales')->__('Coupon Code'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'coupon_code',
            'renderer'  => 'Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer',
            'sortable'  => false,
            'filter'    => false,
        ), 'paypal_account');

        $this->addColumnAfter('payment_method', array(
            'header'=> Mage::helper('sales')->__('Payment Method'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'payment_method',
            'renderer'  => 'Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer',
            'sortable'  => false,
            'filter'    => false,
        ), 'coupon_code');

        $this->addColumnAfter('shipping_country_id', array(
            'header'=> Mage::helper('sales')->__('Shipping Country'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'shipping_country_id',
            'renderer'  => 'Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer',
            'sortable'  => false,
            'filter'    => false,
        ), 'payment_method');

        $this->addColumnAfter('shipping_telephone', array(
            'header'=> Mage::helper('sales')->__('Shipping Tel'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'shipping_telephone',
            'renderer'  => 'Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer',
            'sortable'  => false,
            'filter'    => false,
        ), 'shipping_country_id');

        $this->addColumnAfter('track_number', array(
            'header'=> Mage::helper('sales')->__('Track Number'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'track_number',
            'renderer'  => 'Jeasy_Sales_Block_Adminhtml_Sales_Order_Grid_Renderer',
            'sortable'  => false,
            'filter'    => false,
        ), 'shipping_telephone');

        $this->sortColumnsByOrder();
    }


}
