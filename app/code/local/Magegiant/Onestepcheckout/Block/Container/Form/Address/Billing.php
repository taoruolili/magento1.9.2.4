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
class Magegiant_Onestepcheckout_Block_Container_Form_Address_Billing extends Magegiant_Onestepcheckout_Block_Container_Form_Address
{

    /**
     * @param null $store
     * @return Magegiant_Onestepcheckout_Model_Mysql4_Attribute_Collection
     */
    public function getAttributePosition()
    {
        return Mage::getModel('onestepcheckout/attribute')->getBillingFields();
    }

    /**
     * @param $attribute_code
     * @param $entity_type
     * @return mixed
     */
    public function getAttributeLabel($attribute_code, $entity_type)
    {
        return Mage::helper('onestepcheckout')->getAttributeFrontendLabel($attribute_code, $entity_type);
    }

    public function getBillingTriggerElements()
    {
        $triggers = array();
        foreach ($this->getAddressTriggerElements() as $element) {
            $triggers[] = 'billing:' . $element;
        }

        return Mage::helper('core')->jsonEncode($triggers);
    }

}