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


class Magegiant_Onestepcheckout_Model_Rule_Condition_Product_Subselect extends Magegiant_Onestepcheckout_Model_Rule_Condition_Product_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('onestepcheckout/rule_condition_product_subselect')
            ->setValue(null);
    }

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' . $this->getAttribute() . '</attribute>'
            . '<operator>' . $this->getOperator() . '</operator>'
            . parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            array(
                 'qty'       => Mage::helper('onestepcheckout')->__('total quantity'),
                 'row_total' => Mage::helper('onestepcheckout')->__('total amount'),
            )
        );
        return $this;
    }

    public function loadOperatorOptions()
    {
        $helper = Mage::helper('onestepcheckout');
        $this->setOperatorOption(
            array(
                 '=='  => $helper->__('is'),
                 '!='  => $helper->__('is not'),
                 '>='  => $helper->__('equals or greater than'),
                 '<='  => $helper->__('equals or less than'),
                 '>'   => $helper->__('greater than'),
                 '<'   => $helper->__('less than'),
                 '()'  => $helper->__('is one of'),
                 '!()' => $helper->__('is not one of'),
            )
        );
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('onestepcheckout')->__(
                "If %s %s %s for a subselection of items in cart matching %s of these conditions:",
                $this->getAttributeElement()->getHtml(), $this->getOperatorElement()->getHtml(),
                $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml()
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
        if (!$this->getConditions()) {
            return false;
        }
        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getAllItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }
        return $this->validateAttribute($total);
    }
}