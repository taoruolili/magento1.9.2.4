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
class Magegiant_Onestepcheckout_Model_Rule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{

    public function __construct()
    {
        parent::__construct();
        $this->setType('onestepcheckout/rule_condition_product_combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition  = Mage::getModel('onestepcheckout/rule_condition_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes       = array();
        $iAttributes       = array();
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = array('value' => 'onestepcheckout/rule_condition_product|' . $code, 'label' => $label);
            } else {
                $pAttributes[] = array('value' => 'onestepcheckout/rule_condition_product|' . $code, 'label' => $label);
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'value' => 'onestepcheckout/rule_condition_product_combine',
                    'label' => Mage::helper('catalog')->__('Conditions Combination'),
                ),
                array(
                    'label' => Mage::helper('onestepcheckout')->__('Cart Item Attribute'),
                    'value' => $iAttributes,
                ),
                array(
                    'label' => Mage::helper('onestepcheckout')->__('Product Attribute'),
                    'value' => $pAttributes,
                ),
            )
        );

        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}