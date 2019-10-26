<?php
/**
 * MageGiant
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MageGiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    MageGiant
 * @package     MageGiant_CheckoutPromotion
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Onestepcheckout Resource Model
 * 
 * @category    MageGiant
 * @package     Magegiant_Onestepcheckou
 * @author      MageGiant Developer
 */
class Magegiant_Onestepcheckout_Model_Mysql4_Customblock_Shoppingcart extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('onestepcheckout/customblock_shoppingcart', 'rule_id');
    }
}