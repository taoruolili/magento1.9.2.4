<?php


/**
 *
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 */
class Magegiant_Onestepcheckout_Helper_Attribute_Customer extends Magegiant_Onestepcheckout_Helper_Eav_Data
{
    /**
     * Default attribute entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return 'customer';
    }

    /**
     * Return available customer attribute form as select options
     *
     * @return array
     */
    public function getAttributeFormOptions()
    {
        return array(
            array(
                'label' => Mage::helper('onestepcheckout')->__('Magegiant Onestepcheckout'),
                'value' => 'checkout_register'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Registration'),
                'value' => 'customer_account_create'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Customer Account Edit'),
                'value' => 'customer_account_edit'
            ),
            array(
                'label' => Mage::helper('onestepcheckout')->__('Admin Checkout'),
                'value' => 'adminhtml_checkout'
            ),
        );
    }
}
