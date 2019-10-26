<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magegiant.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */

/**
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_Totals_Order_View_Tab_Delivery
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magegiant/onestepcheckout/totals/order/view/tab/delivery.phtml');
    }

    public function getTabLabel()
    {
        return Mage::helper('onestepcheckout')->__('Delivery Information');
    }

    public function getTabTitle()
    {
        return Mage::helper('onestepcheckout')->__('Delivery Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getLastItem($orderId = null)
    {
        if (!$orderId) {
            $order_id = Mage::app()->getRequest()->getParam('order_id');
        } else {
            $order_id = $orderId;
        }
        $invoice_id    = Mage::app()->getRequest()->getParam('invoice_id');
        $shipment_id   = Mage::app()->getRequest()->getParam('shipment_id');
        $creditmemo_id = Mage::app()->getRequest()->getParam('creditmemo_id');
        if ($order_id) {
            $order = Mage::getModel('sales/order')->load($order_id);
        } else if ($invoice_id) {
            $order = Mage::getModel('sales/order_invoice')->load($invoice_id)->getOrder();
        } else if ($shipment_id) {
            $order = Mage::getModel('sales/order_shipment')->load($shipment_id)->getOrder();
        } else if ($creditmemo_id) {
            $order = Mage::getModel('sales/order_creditmemo')->load($creditmemo_id)->getOrder();
        }
        $itemcollection = $order->getItemsCollection();
        //Zend_Debug::dump(get_class_methods($itemcollection));die();

        $item     = $this->getParentBlock()->getItem();
        $lastItem = $itemcollection->getLastItem();
        if ($lastItem->getParentItemId()) {
            $lastId = $lastItem->getParentItemId();
        } else {
            $lastId = $lastItem->getId();
        }
        if ($lastId != $this->getParentBlock()->getItem()->getId()) {
            return;
        }

        return true;
    }
}