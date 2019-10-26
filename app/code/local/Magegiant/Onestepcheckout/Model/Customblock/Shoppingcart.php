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
 * Onestepcheckout Model
 *
 * @category    MageGiant
 * @package     Magegiant_Onestepcheckout
 * @author      MageGiant Developer
 */
class Magegiant_Onestepcheckout_Model_Customblock_Shoppingcart extends Mage_Rule_Model_Rule
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('onestepcheckout/customblock_shoppingcart');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        if ($this->hasStaticBlocksIds()) {
            $blockIds = $this->getStaticBlocksIds();
            if (is_array($blockIds) && !empty($blockIds)) {
                $this->setStaticBlocksIds(implode(',', $blockIds));
            }
        }


        return $this;
    }

    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->loadArray($arr['conditions'][1]);
        }

        return $this;
    }


    public function getConditionsInstance()
    {
        return Mage::getModel('onestepcheckout/rule_condition_combine');
    }


    /**
     * Fix error when load and save with collection
     */
    protected function _afterLoad()
    {
        $this->setConditions(null);

        return parent::_afterLoad();
    }

    public function checkRule($quote)
    {
        $this->afterLoad();

        return $this->validate($quote);
    }


}