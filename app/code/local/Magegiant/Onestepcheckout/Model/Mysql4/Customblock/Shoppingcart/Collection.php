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
 * Onestepcheckout Resource Collection Model
 *
 * @category    MageGiant
 * @package     Magegiant_Onestepcheckout
 * @author      MageGiant Developer
 */
class Magegiant_Onestepcheckout_Model_Mysql4_Customblock_Shoppingcart_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('onestepcheckout/customblock_shoppingcart');
    }

    public function isActiveFilter()
    {
        $currentDate = Mage::getModel('core/date')->gmtDate('Y-m-d');
        $this->getSelect()
            ->where('is_active = ?', 1);
        $this->getSelect()->where('(from_date IS NULL) OR (date(from_date) <= date(?))', $currentDate);
        $this->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', $currentDate);

        return $this;
    }

    public function addFilterByCustomerGroup($customerGroupId)
    {
        $this->getSelect()
            ->where('FIND_IN_SET(?, customer_group_ids)', $customerGroupId);

        return $this;
    }

    public function addFilterByWebsiteId($websiteId)
    {
        $this->getSelect()
            ->where('FIND_IN_SET(?, website_ids)', $websiteId);

        return $this;
    }
}