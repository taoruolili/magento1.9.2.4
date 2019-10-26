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


class Magegiant_Onestepcheckout_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $helper = Mage::helper('onestepcheckout');
        $attributes['quote_item_qty'] = $helper->__('Quantity in cart');
        $attributes['quote_item_price'] = $helper->__('Price in cart');
        $attributes['quote_item_row_total'] = $helper->__('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $product = Mage::getModel('catalog/product')
            ->load($object->getProductId())
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getPrice())
            ->setQuoteItemRowTotal($object->getRowTotal());

        return parent::validate($product);
    }
}
