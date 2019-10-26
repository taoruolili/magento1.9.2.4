<?php

/**
 * MageGiant
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
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement.html
 */
class Magegiant_Onestepcheckout_Enterprise_AjaxController extends Mage_Checkout_Controller_Action
{
    public function getBlockHelper()
    {
        return Mage::helper('onestepcheckout/block');
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();

        return $this;
    }

    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }

        return false;
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Get Checkout Onepage Quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    /**
     * Get Billing Address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * get Shipping Address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Save Enterprise printed card (Giftwrap)
     */
    public function saveEnterpriseGiftwrapAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array(
            'success'  => true,
            'messages' => array(),
            'blocks'   => array(),
        );
        if (!$this->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                $this->checkoutProcessWrappingInfo($this->getRequest());
                $this->getShippingAddress()->setCollectShippingRates(true);
                $this->getQuote()->setTotalsCollectedFlag(false);
                $this->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            } catch (Mage_Core_Exception $e) {
                $result['success']    = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success']    = false;
                $result['messages'][] = $this->__('Cannot add Printed Card.');
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Process gift wrapping options on checkout proccess
     *
     * @param Varien_Object $observer
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    public function checkoutProcessWrappingInfo($request)
    {
        $giftWrappingInfo = $request->getParam('giftwrapping');
        if (is_array($giftWrappingInfo)) {
            $quote           = $this->getQuote();
            $giftOptionsInfo = $request->getParam('giftoptions');
            foreach ($giftWrappingInfo as $entityId => $data) {
                $info = array();
                if (!is_array($giftOptionsInfo) || empty($giftOptionsInfo[$entityId]['type'])) {
                    continue;
                }
                switch ($giftOptionsInfo[$entityId]['type']) {
                    case 'quote':
                        $entity = $quote;
                        $this->_saveOrderInfo($entity, $data);
                        break;
                    case 'quote_item':
                        $entity = $quote->getItemById($entityId);
                        $this->_saveItemInfo($entity, $data);
                        break;
                    case 'quote_address':
                        $entity = $quote->getAddressById($entityId);
                        $this->_saveOrderInfo($entity, $data);
                        break;
                    case 'quote_address_item':
                        $entity = $quote
                            ->getAddressById($giftOptionsInfo[$entityId]['address'])
                            ->getItemById($entityId);
                        $this->_saveItemInfo($entity, $data);
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Prepare quote item info about gift wrapping
     *
     * @param mixed $entity
     * @param array $data
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    protected function _saveItemInfo($entity, $data)
    {
        if (is_array($data)) {
            $wrapping = Mage::getModel('enterprise_giftwrapping/wrapping')->load($data['design']);
            $entity->setGwId($wrapping->getId())
                ->save();
        }

        return $this;
    }

    /**
     * Prepare entire order info about gift wrapping
     *
     * @param mixed $entity
     * @param array $data
     * @return Enterprise_GiftWrapping_Model_Observer
     */
    protected function _saveOrderInfo($entity, $data)
    {
        if (is_array($data)) {
            $wrappingInfo = array();
            if (isset($data['design'])) {
                $wrapping              = Mage::getModel('enterprise_giftwrapping/wrapping')->load($data['design']);
                $wrappingInfo['gw_id'] = $wrapping->getId();
            }
            $wrappingInfo['gw_allow_gift_receipt'] = isset($data['allow_gift_receipt']);
            $wrappingInfo['gw_add_card']           = isset($data['add_printed_card']);
            if ($entity->getShippingAddress()) {
                $entity->getShippingAddress()->addData($wrappingInfo);
            }
            $entity->addData($wrappingInfo)->save();
        }

        return $this;
    }

    public function applyEnterpriseStorecreditAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                $quote = $this->getOnepage()->getQuote();

                $store = Mage::app()->getStore($quote->getStoreId());
                if (
                    !$quote
                    || !$quote->getCustomerId()
                    || $quote->getBaseGrandTotal() + $quote->getBaseCustomerBalanceAmountUsed() <= 0
                ) {
                    $result['success'] = false;
                }

                $quote->setUseCustomerBalance((bool)$this->getRequest()->getParam('use_customer_balance'));
                if ($quote->getUseCustomerBalance()) {
                    $balance = Mage::getModel('enterprise_customerbalance/balance')
                        ->setCustomerId($quote->getCustomerId())
                        ->setWebsiteId($store->getWebsiteId())
                        ->loadByCustomer();
                    if ($balance) {
                        $quote->setCustomerBalanceInstance($balance);
                        if (!$quote->getPayment()->getMethod()) {
                            $quote->getPayment()->setMethod('free');
                        }
                        $result['messages'][] = $this->__('Store credit was applied.');
                    } else {
                        $quote->setUseCustomerBalance(false);
                        $result['messages'][] = $this->__(
                            'Store Credit payment is not being used in your shopping cart.'
                        );
                    }
                } else {
                    $quote->setUseCustomerBalance(false);
                    $result['messages'][] = $this->__('The store credit payment has been removed from the order.');
                }

                $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $this->getOnepage()->getQuote()->collectTotals()->save();

                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            } catch (Mage_Core_Exception $e) {
                $result['success']    = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success']    = false;
                $result['messages'][] = $this->__('Cannot apply the Store Credit.');
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyEnterprisePointsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result = array(
            'success'     => true,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        if (!$this->getOnepage()->getQuote()->getItemsCount()) {
            $result['success'] = false;
        } else {
            try {
                $quote = $this->getOnepage()->getQuote();
                if (
                    !$quote
                    || !$quote->getCustomerId()
                    || $quote->getBaseGrandTotal() + $quote->getBaseRewardCurrencyAmount() <= 0
                ) {
                    $result['success'] = false;
                }

                $quote->setUseRewardPoints((bool)$this->getRequest()->getParam('use_reward_points'));
                if ($quote->getUseRewardPoints()) {
                    /* @var $reward Enterprise_Reward_Model_Reward */
                    $reward = Mage::getModel('enterprise_reward/reward')
                        ->setCustomer($quote->getCustomer())
                        ->setWebsiteId($quote->getStore()->getWebsiteId())
                        ->loadByCustomer();

                    $minPointsBalance = (int)Mage::getStoreConfig(
                        Enterprise_Reward_Model_Reward::XML_PATH_MIN_POINTS_BALANCE,
                        $quote->getStoreId()
                    );

                    if ($reward->getId() && $reward->getPointsBalance() >= $minPointsBalance) {
                        $quote->setRewardInstance($reward);
                        if (!$quote->getPayment()->getMethod()) {
                            $quote->getPayment()->setMethod('free');
                        }
                        $result['messages'][] = $this->__('Reward points was applied.');
                    } else {
                        $quote->setUseRewardPoints(false);
                        $result['messages'][] = $this->__('Reward points will not be used in this order.');
                    }
                } else {
                    $quote->setUseRewardPoints(false);
                    $result['messages'][] = $this->__('The reward points have been removed from the order.');
                }

                $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
                $this->getOnepage()->getQuote()->collectTotals()->save();

                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
            } catch (Mage_Core_Exception $e) {
                $result['success']    = false;
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['success']    = false;
                $result['messages'][] = $this->__('Cannot apply the %s.', Mage::helper('onestepcheckout/enterprise_points')->getPointsUnitName());
                Mage::logException($e);
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyEnterpriseGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result       = array(
            'success'     => false,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        $giftcardCode = (string)$this->getRequest()->getParam('enterprise_giftcard_code');
        if (isset($giftcardCode)
            || !(strlen($giftcardCode) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH)
        ) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($giftcardCode)
                    ->addToCart();
                $result['success']    = true;
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Gift Card "%s" was added.',
                    Mage::helper('core')->escapeHtml($giftcardCode));
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks']      = $this->getBlockHelper()->getActionBlocks();
                $result['grand_total'] = Mage::helper('onestepcheckout')->getGrandTotal(
                    $this->getOnepage()->getQuote());
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent(
                    'enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $giftcardCode)
                );
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Cannot apply gift card.');
            }
        } else {
            $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function removeEnterpriseGiftcardAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $result       = array(
            'success'     => false,
            'messages'    => array(),
            'blocks'      => array(),
            'grand_total' => ""
        );
        $giftcardCode = (string)$this->getRequest()->getParam('enterprise_giftcard_code');
        if (isset($giftcardCode)
            || !(strlen($giftcardCode) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH)
        ) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($giftcardCode)
                    ->removeFromCart();
                $result['success']    = true;
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Gift Card "%s" was removed.',
                    Mage::helper('core')->escapeHtml($giftcardCode));
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks']      = $this->getBlockHelper()->getActionBlocks();
                $result['grand_total'] = Mage::helper('onestepcheckout')->getGrandTotal(
                    $this->getOnepage()->getQuote());
            } catch (Mage_Core_Exception $e) {
                $result['messages'][] = $e->getMessage();
            } catch (Exception $e) {
                $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Cannot remove gift card.');
            }
        } else {
            $result['messages'][] = Mage::helper('enterprise_giftcardaccount')->__('Wrong gift card code.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}