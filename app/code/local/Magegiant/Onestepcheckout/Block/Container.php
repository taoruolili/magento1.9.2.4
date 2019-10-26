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
 * @category   Magegiant
 * @package    Magegiant_Onestepcheckout
 * @version    3.0.0
 * @copyright   Copyright (c) 2014 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */
class Magegiant_Onestepcheckout_Block_Container extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $_helperData;
    protected $_helperConfig;
    protected $_isSecure;
    protected $_blockSection = array(
        'shipping_method'               => '#one-step-checkout-shipping-method .shipping-methods',
        'payment_method'                => '#one-step-checkout-payment-method .one-step-checkout-payment-methods',
        'review_cart'                   => '.one-step-checkout-order-review-cart',
        'review_coupon'                 => '#one-step-checkout-review-coupon',
        'review_enterprise_giftcard'    => '#one-step-checkout-order-review-enterprise-giftcard-wrapper',
        'review_enterprise_storecredit' => '#one-step-checkout-order-review-enterprise-storecredit-wrapper',
        'review_enterprise_points'      => '#one-step-checkout-order-review-enterprise-points-wrapper',
        'custom_block_top'              => '#one-step-checkout-custom-block-top',
        'custom_block_bottom'           => '#one-step-checkout-custom-block-bottom',
        'related'                       => '#one-step-checkout-related',
        'cart_sidebar'                  => '.header-minicart'
    );

    public function __construct()
    {
        parent::__construct();
        $this->_helperData   = Mage::helper('onestepcheckout');
        $this->_helperConfig = Mage::helper('onestepcheckout/config');
        $this->_isSecure     = Mage::app()->getStore()->isCurrentlySecure();
    }

    /**
     * get helper config
     *
     * @return
     */
    public function getHelperConfig()
    {
        return $this->_helperConfig;
    }

    public function getHelperData()
    {
        return $this->_helperData;
    }

    /**
     * get current url is http or https
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->_isSecure;
    }

    public function getBlockMapping()
    {
        $blocks       = array();
        $blockMapping = Mage::helper('onestepcheckout/block')->getBlockMapping();
        foreach ($blockMapping as $action => $blockName) {
            $blocks[$action] = array_keys((array)$blockName);
        }

        return Mage::helper('core')->jsonEncode($blocks);
    }

    public function getBlockSection()
    {
        $blocks = new Varien_Object();
        $blocks->setBlocks($this->_blockSection);
        Mage::dispatchEvent('one_step_checkout_prepare_block_section_after', array(
            'blocks' => $blocks
        ));

        return Mage::helper('core')->jsonEncode($blocks->getBlocks());
    }

    public function getNumbering($increment = true)
    {
        return Mage::helper('onestepcheckout')->getNumbering($increment);
    }

    /**
     * Enterprise Gitf Wrapping
     *
     * @return string
     */
    public function getEnterpriseGiftWrappingHtml()
    {
        $html = '';
        if (Mage::helper('core')->isModuleEnabled('Enterprise_GiftWrapping')) {
            $html .= Mage::app()->getLayout()
                ->createBlock('enterprise_giftwrapping/checkout_options')
                ->setTemplate('giftwrapping/checkout/options.phtml')
                ->toHtml();
            $html .= Mage::app()->getLayout()
                ->createBlock('onestepcheckout/compatible_enterprise_shipping_giftwrap')
                ->setTemplate('magegiant/onestepcheckout/compatible/enterprise/shipping/giftwrap.phtml')
                ->toHtml();
        }

        return $html;
    }

    public function getGrandTotal()
    {
        return Mage::helper('onestepcheckout')->getGrandTotal($this->getQuote());
    }

    public function showGrandTotal()
    {
        return $this->getHelperConfig()->showGrandTotal();
    }

    public function getPlaceOrderUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/placeOrder', array('_secure' => $this->isSecure()));
    }

    public function getCheckoutSuccessUrl()
    {
        return Mage::getUrl('checkout/onepage/success', array('_secure' => $this->isSecure()));
    }

    /**
     * Checkout title config
     *
     * @return string
     */
    public function getCheckoutTitle()
    {
        return $this->escapeHtml($this->getHelperConfig()->getCheckoutTitle());
    }

    /**
     * Checkout description config
     *
     * @return mixed
     */
    public function getCheckoutDescription()
    {
        return $this->getHelperConfig()->getCheckoutDescription();
    }

    public function allowShipToDifferent()
    {
        return $this->getHelperConfig()->allowShipToDifferent();
    }

    /**
     * @return string
     */
    public function getChangeAddressUrl()
    {

        return Mage::getUrl('onestepcheckout/ajax/saveAddress', array('_secure' => $this->isSecure()));
    }

    /**
     * @return string
     */
    public function getSaveFormUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/saveFormValues', array('_secure' => $this->isSecure()));
    }

    /**
     * get save shipping method url
     *
     * @return string
     */
    public function getSaveShippingMethodUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/saveShippingMethod');
    }

    /**
     * get save Enterprise Gift Wrap Url
     *
     * @return string
     */
    public function getSaveEnterpriseGiftwrapUrl()
    {
        return Mage::getUrl('onestepcheckout/enterprise_ajax/saveEnterpriseGiftwrap', array('_secure' => $this->isSecure()));
    }

    /**
     * @return string
     */
    public function getSavePaymentUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/savePaymentMethod', array('_secure' => $this->isSecure()));
    }

    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }

    public function getApplyCouponAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/applyCoupon', array('_secure' => $this->isSecure()));
    }

    public function getCancelCouponAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/cancelCoupon', array('_secure' => $this->isSecure()));
    }

    public function getFormData()
    {
        return Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
    }

    public function getCommentsData()
    {
        $data = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
        if (isset($data['comments'])) {
            return $data['comments'];
        }

        return '';
    }

    /**
     * get Customer Name
     *
     * @return string
     */
    public function getUsername()
    {
        $username = Mage::getSingleton('customer/session')->getUsername(true);

        return $this->escapeHtml($username);
    }

    /**
     *
     */
    public function getActionPattern()
    {
        $actionPattern = '/onestepcheckout\/ajax\/([^\/]+)\//';

        return $actionPattern;
    }

    public function getActionEEPattern()
    {
        $actionPattern = '/onestepcheckout\/enterprise_ajax\/([^\/]+)\//';

        return $actionPattern;
    }

    public function isEnabledMorphEffect()
    {
        return $this->getHelperConfig()->isEnabledMorphEffect();
    }
}