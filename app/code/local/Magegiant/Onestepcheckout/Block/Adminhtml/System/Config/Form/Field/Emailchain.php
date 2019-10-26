<?php
/**
 * Magegiant
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the magegiant.com license that is
 * available through the world-wide-web at this URL:
 * http://magegiant.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magegiant
 * @package     Magegiant_CheckoutPromotion
 * @copyright   Copyright (c) 2014 Magegiant (http://magegiant.com/)
 * @license     http://magegiant.com/license-agreement/
 */

/**
 * Onestepcheckout Adminhtml Controller
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magegiant Developer
 */
class Magegiant_Onestepcheckout_Block_Adminhtml_System_Config_Form_Field_Emailchain
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }
        $days    = $element->setStyle('width:10%;')->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $hours   = $element->setStyle('width:10%;')->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $minutes = $element->setStyle('width:10%;')->setValue(isset($values[2]) ? $values[2] : null)->getElementHtml();

        return $days . $this->__('day(s)') . $hours.$this->__('hour(s)').$minutes.$this->__('minute(s)');
    }
}