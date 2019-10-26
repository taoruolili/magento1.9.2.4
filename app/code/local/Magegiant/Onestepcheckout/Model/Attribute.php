<?php

class Magegiant_Onestepcheckout_Model_Attribute extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('onestepcheckout/attribute');
    }

    /**
     * get all customer attribute used for onetepcheckout by postion
     *
     * @param null $store
     * @return Magegiant_Onestepcheckout_Model_Mysql4_Attribute_Collection
     */
    public function getBillingFields()
    {
        $attributes = $this->getCollection()
            ->addFieldToFilter(
                'is_used_for_onestepcheckout', array(
                    'neq' => '',
                )
            )
            ->addFieldToFilter(
                'is_billing', array(
                    'eq' => 1,
                )
            );
        $attributes->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);

        return $attributes;
    }

    /**
     * @return Magegiant_Onestepcheckout_Model_Mysql4_Attribute_Collection
     */
    public function getAvailableFields()
    {
        $attributes = $this->getCollection()
            ->addFieldToFilter(
                'is_used_for_onestepcheckout', array(
                    'neq' => '',
                )
            )
            ->addFieldToFilter(
                'is_billing', array(
                    'eq' => 0,
                )
            );
        $attributes->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);

        return $attributes;
    }

    public function getFieldColspanByCode($attribute_code)
    {
        $attributeField = $this->load($attribute_code, 'attribute_code');
        if ($attributeField && $attributeField->getId())
            return $attributeField->getColspan();

        return false;
    }

}