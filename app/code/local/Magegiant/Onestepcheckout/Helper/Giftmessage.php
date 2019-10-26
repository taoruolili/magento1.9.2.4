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
class Magegiant_Onestepcheckout_Helper_Giftmessage extends Mage_GiftMessage_Helper_Message
{
    public function isEnabled($store = null)
    {
        return Mage::helper('onestepcheckout/config')->isEnabledGiftMessage($store);
    }

    /**
     *
     */
    public function getInline($type, Varien_Object $entity, $dontDisplayContainer = false)
    {
        if (in_array($type, array('onepage_checkout', 'multishipping_adress'))) {
            if (!$this->isMessagesAvailable('items', $entity)) {
                return '';
            }
        } elseif (!$this->isMessagesAvailable($type, $entity)) {
            return '';
        }

        return Mage::getSingleton('core/layout')->createBlock('giftmessage/message_inline')
            ->setId('giftmessage_form_' . $this->_nextId++)
            ->setDontDisplayContainer($dontDisplayContainer)
            ->setEntity($entity)
            ->setType($type)
            ->setTemplate('magegiant/onestepcheckout/container/form/review/giftmessage/inline.phtml')
            ->setFormData(Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values'))
            ->toHtml();
    }
}