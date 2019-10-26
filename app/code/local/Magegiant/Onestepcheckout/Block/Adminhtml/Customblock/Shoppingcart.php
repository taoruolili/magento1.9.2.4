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
class Magegiant_Onestepcheckout_Block_Adminhtml_Customblock_Shoppingcart extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_customblock_shoppingcart';
        $this->_blockGroup = 'onestepcheckout';
        $this->_headerText = Mage::helper('onestepcheckout')->__('Shopping Cart Rule Blocks');
        $this->_addButtonLabel = Mage::helper('onestepcheckout')->__('Add Block');
        parent::__construct();
    }
}