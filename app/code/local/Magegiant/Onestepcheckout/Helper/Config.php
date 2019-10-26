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
class Magegiant_Onestepcheckout_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * "Enable One Step Checkout" from system config
     */
    const GENERAL_IS_ENABLED = 'onestepcheckout/general/is_enabled';
    /**
     * General configuaration path
     */
    const GENERAL_CONFIGUARATION = 'onestepcheckout/general/';
    /**
     * Display configuaration path
     */
    const DISPLAY_CONFIGUARATION = 'onestepcheckout/display_configuration/';
    /**
     * Design configuaration path
     */
    const DESIGN_CONFIGUARATION = 'onestepcheckout/design_configuration/';
    /**
     * Term and condition config path
     */
    const TERM_AND_CONDITION_CONFIGUARATION = 'onestepcheckout/terms_conditions/';
    const TERM_AND_CONDITION_ID = 'giant_osc_term';
    /**
     * Abandoned cart
     */
    const ABANDONED_CART_CONFIGUARATION = 'onestepcheckout/abandoned_cart/';
    /**
     * Survey config path
     */
    const SURVEY_CONFIGUARATION = 'onestepcheckout/survey/';
    const TEMPLATE_PATH = 'magegiant/onestepcheckout/';

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabled($store = null)
    {
        $isModuleEnabled       = $this->isModuleEnabled();
        $isModuleOutputEnabled = $this->isModuleOutputEnabled();

        return $isModuleOutputEnabled && $isModuleEnabled && Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store);
    }

    /**
     * get general config by code
     *
     * @param      $code
     * @param null $store
     * @return mixed
     */
    public function getGeneralConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_CONFIGUARATION . $code, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function allowShipToDifferent($store = null)
    {
        return $this->getGeneralConfig('allow_ship_to_defferent_address', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCheckoutTitle($store = null)
    {
        return $this->getGeneralConfig('title', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCheckoutDescription($store = null)
    {
        return $this->getGeneralConfig('description', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultPaymentMethod($store = null)
    {
        return $this->getGeneralConfig('default_payment_method', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultShippingMethod($store = null)
    {
        return $this->getGeneralConfig('default_shipping_method', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultCountryId($store = null)
    {
        return $this->getGeneralConfig('default_country_id', $store);
    }

    /**
     * @param null $store
     */
    public function getAddressTriggerElements($store = null)
    {
        return $this->getGeneralConfig('address_trigger_elements', $store);
    }

    public function isIntegratedSocialLogin($store = null)
    {
        return $this->getGeneralConfig('integrated_sociallogin', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isAutoDetectedAddress($store = null)
    {

        return $this->getGeneralConfig('auto_detect_address', $store) == 'google';
    }

    public function  isEnabledAddressByCountry($store = null)
    {
        return $this->getGeneralConfig('auto_detect_address', $store) == 'pca';
    }

    public function getGoogleSpecificCountry($store = null)
    {
        return $this->getGeneralConfig('google_specific_country', $store);
    }

    public function  getPcaWebsiteKey($store = null)
    {
        return $this->getGeneralConfig('pca_website_key', $store);
    }

    public function  getPcaCountryLookup($store = null)
    {
        return $this->getGeneralConfig('pca_country_lookup', $store);
    }

    public function getDisplayConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::DISPLAY_CONFIGUARATION . $code, $store);
    }

    public function disableShippingAddress($store = null)
    {
        return $this->getDisplayConfig('is_disabled_shipping_address', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isCoupon($store = null)
    {
        return $this->getDisplayConfig('is_enabled_coupon', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabledCommments($store = null)
    {
        return $this->getDisplayConfig('is_enabled_comments', $store);
    }

    public function isEnabledGiftMessage($store = null)
    {
        return $this->getDisplayConfig('is_enabled_giftmessage', $store);
    }

    public function isEnabledGiftWrap($store = null)
    {
        return $this->getDisplayConfig('is_enabled_giftwrap', $store);
    }

    public function getGiftWrapType($store = null)
    {
        return $this->getDisplayConfig('giftwrap_type', $store);
    }

    public function getOrderGiftwrapAmount($store = null)
    {
        return $this->getDisplayConfig('giftwrap_amount', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabledDelivery($store = null)
    {
        return $this->getDisplayConfig('is_enabled_delivery', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isNewsletter($store = null)
    {
        return $this->getDisplayConfig('is_enabled_newsletter', $store);
    }

    public function isSubscribedByDefault($store = null)
    {
        return $this->getDisplayConfig('is_checked_newsletter', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isRelatedProducts($store = null)
    {
        return $this->getDisplayConfig('is_enabled_related_products', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getBlockBelowBillingAddress($store = null)
    {
        return $this->getDisplayConfig('display_block_below_billing_address', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getBlockBelowShippingAddress($store = null)
    {
        return $this->getDisplayConfig('display_block_below_shipping_address', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getBlockBelowShippingMethod($store = null)
    {
        return $this->getDisplayConfig('display_block_below_shipping_method', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getBlockBelowPaymentMethod($store = null)
    {
        return $this->getDisplayConfig('display_block_below_payment_method', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isApplyCouponButton($store = null)
    {
        return $this->getDisplayConfig('display_apply_coupon_button', $store);
    }


    public function showGrandTotal($store = null)
    {
        return $this->getDisplayConfig('is_show_grand_total', $store);
    }

    public function isShowNumbering($store = null)
    {
        return $this->getDisplayConfig('is_show_numbering', $store);
    }

    public function isEnabledMorphEffect($store = null)
    {
        return $this->getDisplayConfig('is_enabled_morph_effect', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDesignConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::DESIGN_CONFIGUARATION . $code, $store);
    }

    /**
     * get layout tempate: 1 or 2 or 3 columns
     *
     * @param null $store
     * @return string
     */
    public function getLayoutTemplate($store = null)
    {

        $templateFile = self::TEMPLATE_PATH . 'onestep-' . $this->getDesignConfig('page_layout', $store) . '.phtml';

        return $templateFile;
    }

    public function getLoginPopupWidth($store = null)
    {
        return $this->getDesignConfig('login_popup_width', $store);
    }

    public function getLoginPopupHeight($store = null)
    {
        return $this->getDesignConfig('login_popup_height', $store);
    }

    /**
     * @param $color
     * @return string
     */
    public function mappingColor($color)
    {
        if ($color == 'orange')
            return '#F39801';
        if ($color == 'green')
            return '#B6CE5E';
        if ($color == 'black')
            return '#4D4D4D';
        if ($color == 'blue')
            return '#3398CC';
        if ($color == 'darkblue')
            return '#004BA0';
        if ($color == 'pink')
            return '#E13B91';
        if ($color == 'red')
            return '#E10E03';
        if ($color == 'violet')
            return '#B962d5';

        return $color;
    }

    /**
     * @param null $store
     * @return mixed|string
     */
    public function getStyleColor($store = null)
    {
        $style_config = $this->getDesignConfig('style_color', $store);
        if ($style_config != 'custom')
            return $this->mappingColor($style_config);

        return '#' . $this->getDesignConfig('style_custom');
    }

    public function getHeadingTextColor($store = null)
    {
        $style_config = $this->getDesignConfig('style_color', $store);
        if ($style_config != 'custom')
            return false;

        return '#' . $this->getDesignConfig('style_heading_custom', $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getPlaceOrderColor($store = null)
    {
        return '#' . $this->getDesignConfig('place_order_color', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCustomCss($store = null)
    {
        return $this->getDesignConfig('custom_css', $store);
    }

    /**
     * @param      $code
     * @param null $store
     * @return mixed
     */
    public function getTermAndConditionConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::TERM_AND_CONDITION_CONFIGUARATION . $code, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabledTerm($store = null)
    {
        return $this->getTermAndConditionConfig('enabled_terms', $store);
    }

    public function isRequiredReadTerm($store = null)
    {
        return $this->getTermAndConditionConfig('require_read', $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getTermTitle($store = null)
    {
        return $this->getTermAndConditionConfig('term_title', $store);
    }

    public function getTermCheckboxText($store = null)
    {
        return $this->getTermAndConditionConfig('checkbox_text', $store);
    }

    public function getRequiredAgreementIds()
    {
        $agreements          = array();
        $agreements[]        = self::TERM_AND_CONDITION_ID;
        $defaultAgreementIds = Mage::helper('checkout')->getRequiredAgreementIds();
        $agreements          = array_merge($agreements, $defaultAgreementIds);

        return $agreements;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getTermContent($store = null)
    {
        return $this->getTermAndConditionConfig('term_html', $store);
    }

    /**
     * @param      $code
     * @param null $store
     * @return mixed
     */
    public function getSurveyConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::SURVEY_CONFIGUARATION . $code, $store);
    }

    public function isEnabledSurvey($store = null)
    {
        return $this->getSurveyConfig('enabled_survey', $store);
    }

    public function getSurveyQuestion($store = null)
    {
        return $this->getSurveyConfig('survey_question', $store);
    }

    public function getSurveyValues($store = null)
    {
        return $this->getSurveyConfig('survey_values', $store);
    }

    /**
     * Abandoned Cart Configuaration
     *
     * @param      $code
     * @param null $store
     * @return mixed
     */
    public function getAbandonedCartConfig($code, $store = null)
    {
        return Mage::getStoreConfig(self::ABANDONED_CART_CONFIGUARATION . $code, $store);
    }

    public function isEnabledAbandonedCart($store = null)
    {
        return $this->getAbandonedCartConfig('is_enabled', $store);
    }

    public function getAbandonedActiveFrom($store = null)
    {
        return $this->getAbandonedCartConfig('active_from', $store);
    }

    public function getAbandonedActiveTo($store = null)
    {
        return $this->getAbandonedCartConfig('active_to', $store);
    }

    public function getAbandonedCustomerGroup($store = null)
    {
        return $this->getAbandonedCartConfig('customer_group', $store);
    }

    public function getAbandonedSku($store = null)
    {
        return $this->getAbandonedCartConfig('sku', $store);
    }

    public function getAbandonedSaleRule($store = null)
    {
        return $this->getAbandonedCartConfig('sale_amount', $store);
    }

    public function getAbandonedSchedule($store = null)
    {
        return $this->getAbandonedCartConfig('email_chain_schedule', $store);
    }

    public function getAbandonedEmailSender($store = null)
    {
        return $this->getAbandonedCartConfig('sender', $store);
    }

    public function getAbandonedEmailTemplate($store = null)
    {
        return $this->getAbandonedCartConfig('email_chain_template', $store);
    }

}