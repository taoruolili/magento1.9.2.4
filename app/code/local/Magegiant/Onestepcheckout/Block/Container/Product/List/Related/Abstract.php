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
abstract class Magegiant_Onestepcheckout_Block_Container_Product_List_Related_Abstract extends Mage_Checkout_Block_Cart_Crosssell
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 15;

    protected $_compareListProductIds = null;

    protected $_wishlistProductIds = null;

    /**
     * Copy-paste logic from Mage_Wishlist_IndexController->preDispatch
     *
     * @return bool
     */
    public function isCanManageWishlist()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            return false;
        }

        return true;
    }

    public function isNotAddedToWishlist($_item)
    {
        return !in_array($_item->getId(), $this->_getWishlistProductIds());
    }

    public function isNotAddedToCompareList($_item)
    {
        return !in_array($_item->getId(), $this->_getCompareListProductIds());
    }

    protected function _getWishlistProductIds()
    {
        if (is_null($this->_wishlistProductIds)) {
            $this->_wishlistProductIds = array();
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                if ($customerId) {
                    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
                    if ($wishlist->getId() && $wishlist->getCustomerId() === $customerId) {
                        $this->_wishlistProductIds = $this->_getWishlistProductCollection($wishlist)->getAllIds();
                    }
                }
            }
        }

        return $this->_wishlistProductIds;
    }

    protected function _getCompareListProductIds()
    {
        if (is_null($this->_compareListProductIds)) {
            $this->_compareListProductIds = array();
            $collection                   = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $collection->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            } else {
                $collection->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }
            $this->_compareListProductIds = $collection->getAllIds();
        }

        return $this->_compareListProductIds;
    }

    protected function _getWishlistProductCollection(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        return Mage::getResourceModel('wishlist/product_collection')
            ->setStoreId($wishlist->getStore()->getId())
            ->addWishlistFilter($wishlist)
            ->addWishListSortOrder();
    }
}
