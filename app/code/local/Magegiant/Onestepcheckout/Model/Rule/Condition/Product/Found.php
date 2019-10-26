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
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 MageGiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */


class Magegiant_Onestepcheckout_Model_Rule_Condition_Product_Found extends Magegiant_Onestepcheckout_Model_Rule_Condition_Product_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('onestepcheckout/rule_condition_product_found');
    }

    public function loadValueOptions()
    {
        $this->setValueOption(
            array(
                 1 => 'FOUND',
                 0 => 'NOT FOUND',
            )
        );
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('onestepcheckout')->__(
                'If an item is %s in the cart with %s of these conditions true:', $this->getValueElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     *
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        $found = false;
        foreach ($object->getAllItems() as $item) {
            $found = $all ? true : false;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if ($all && !$validated) {
                    $found = false;
                    break;
                } elseif (!$all && $validated) {
                    $found = true;
                    break 2;
                }
            }
            if ($found && $true) {
                break;
            }
        }
        if ($found && $true) {
            // found an item and we're looking for existing one
            return true;
        } elseif (!$found && !$true) {
            // not found and we're making sure it doesn't exist
            return true;
        }
        return false;
    }
}