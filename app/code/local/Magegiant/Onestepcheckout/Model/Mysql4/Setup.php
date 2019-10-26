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

/**
 * Onestepcheckout Resource Model
 *
 * @category    MageGiant
 * @package     Magegiant_Onestepcheckout
 * @author      MageGiant Developer
 */
class Magegiant_Onestepcheckout_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function addCustomerFieldToOnestepcheckout($installer)
    {
        // add field that indicates that attribute is used for customer segments to attribute properties
        $installer->getConnection()
            ->addColumn($installer->getTable('customer/eav_attribute'), 'is_used_for_onestepcheckout', 'varchar(10) default NULL');
        // use specific required attributes for onestepcheckout
        $reqAttributesOfEntities = array(
            'customer'         => array('email', 'firstname', 'lastname'),
            'customer_address' => array('street', 'city', 'region_id', 'postcode', 'country_id'),
        );

        foreach ($reqAttributesOfEntities as $entityTypeId => $attributes) {
            foreach ($attributes as $attributeCode) {
                $installer->updateAttribute($entityTypeId, $attributeCode, 'is_used_for_onestepcheckout', 'req');
            }
        }
        // use specific optional attributes for onestepcheckout
        $optAttributesOfEntities = array(
            'customer_address' => array('telephone', 'company'),
        );

        foreach ($optAttributesOfEntities as $entityTypeId => $attributes) {
            foreach ($attributes as $attributeCode) {
                $installer->updateAttribute($entityTypeId, $attributeCode, 'is_used_for_onestepcheckout', 'opt');
            }
        }
        $availableAttributes = array(
            'customer' => array('gender', 'dob'),
        );
        foreach ($availableAttributes as $entityTypeId => $attributes) {
            foreach ($attributes as $attributeCode) {
                $installer->updateAttribute($entityTypeId, $attributeCode, 'is_visible', 1);
                $installer->updateAttribute($entityTypeId, $attributeCode, 'is_used_for_onestepcheckout', 'opt');
            }
        }
    }

    /**
     * Insert Magegiant Static block
     */
    public function  insertStaticBlocks()
    {
        Mage::getSingleton('onestepcheckout/generator_block')->importStaticBlocks('cms/block', 'blocks', true);
    }

    /**
     * Insert Default fields for onestepcheckout
     */
    public function insertFieldsPosition()
    {
        Mage::getResourceModel('onestepcheckout/attribute')->initFields();
    }
}
