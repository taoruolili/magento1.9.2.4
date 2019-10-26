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
class Magegiant_Onestepcheckout_Block_Container_Form_Review_Term extends Mage_Checkout_Block_Agreements
{

    /**
     * enable gift message or not
     *
     */
    public function canShow()
    {
        return count($this->getTermAndConditions());
    }

    public function getTermAndConditions()
    {
        $agreements        = array();
        $agreementsDefault = $this->getAgreements();
        if (Mage::helper('onestepcheckout/config')->isEnabledTerm()) {
            $agreementConfig = array(
                'id'            => 'giant_osc_term',
                'checkbox_text' => Mage::helper('onestepcheckout/config')->getTermCheckboxText(),
                'name'         => Mage::helper('onestepcheckout/config')->getTermTitle(),
                'content'       => Mage::helper('onestepcheckout/config')->getTermContent(),
                'is_html'       => true
            );
            $agreements[]    = new Varien_Object($agreementConfig);
        }
        foreach ($agreementsDefault as $agree) {
            $agreements[] = $agree;
        }

        return $agreements;
    }

    public function isRequiredReadTerm()
    {
        return Mage::helper('onestepcheckout/config')->isRequiredReadTerm();
    }

    public function getFormData()
    {
        return Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values');
    }

}