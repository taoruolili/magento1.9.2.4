<?php

/**
 * Magegiant Customer Data Helper
 *
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Helper_Attribute_Address extends Magegiant_Onestepcheckout_Helper_Eav_Data
{
    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return 'customer_address';
    }

    /**
     * Return available customer address attribute form as select options
     *
     * @return array
     */
    public function getAttributeFormOptions()
    {
        return array(
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Address Registration'),
                'value' => 'customer_register_address'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Account Address'),
                'value' => 'customer_address_edit'
            ),
        );
    }
}
