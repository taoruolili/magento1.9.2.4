<?php
/**
 * Magento Magegiant Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Magegiant Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/Magegiant-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/Magegiant-edition
 */


/**
 * EAV entity Attribute Form Renderer Abstract Block
 *
 * @category    Magegiant
 * @package     Magegiant_Onestepcheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Magegiant_Onestepcheckout_Block_Eav_Form_Renderer_Abstract extends Mage_Core_Block_Template
{
    /**
     * Attribute instance
     *
     * @var Mage_Eav_Model_Attribute
     */
    protected $_attribute;

    /**
     * EAV Entity Model
     *
     * @var Mage_Core_Model_Abstract
     */
    protected $_entity;

    /**
     * Format for HTML elements id attribute
     *
     * @var string
     */
    protected $_fieldIdFormat = '%1$s';

    /**
     * Format for HTML elements name attribute
     *
     * @var string
     */
    protected $_fieldNameFormat = '%1$s';

    /**
     * Set attribute instance
     *
     * @param Mage_Eav_Model_Attribute $attribute
     * @return Magegiant_Onestepcheckout_Block_Eav_Form_Renderer_Abstract
     */
    public function setAttributeObject(Mage_Eav_Model_Attribute $attribute)
    {
        $this->_attribute = $attribute;

        return $this;
    }

    /**
     * Return Attribute instance
     *
     * @return Mage_Eav_Model_Attribute
     */
    public function getAttributeObject()
    {
        return $this->_attribute;
    }

    /**
     * Set Entity object
     *
     * @param Mage_Core_Model_Abstract
     * @return Magegiant_Onestepcheckout_Block_Eav_Form_Renderer_Abstract
     */
    public function setEntity(Mage_Core_Model_Abstract $entity)
    {
        $this->_entity = $entity;

        return $this;
    }

    /**
     * Return Entity object
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Return Data Form Filter or false
     *
     * @return Varien_Data_Form_Filter_Interface
     */
    protected function _getFormFilter()
    {
        $filterCode = $this->getAttributeObject()->getInputFilter();
        if ($filterCode) {
            $filterClass = 'Varien_Data_Form_Filter_' . ucfirst($filterCode);
            if ($filterCode == 'date') {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $filter = new $filterClass($format);
            } else {
                $filter = new $filterClass();
            }

            return $filter;
        }

        return false;
    }

    /**
     * Apply output filter to value
     *
     * @param string $value
     * @return string
     */
    protected function _applyOutputFilter($value)
    {
        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    /**
     * Return validate class by attribute input validation rule
     *
     * @return string|false
     */
    protected function _getInputValidateClass()
    {
        $class         = false;
        $validateRules = $this->getAttributeObject()->getValidateRules();
        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
                case 'alphanumeric':
                    $class = 'validate-alphanum';
                    break;
                case 'numeric':
                    $class = 'validate-digits';
                    break;
                case 'alpha':
                    $class = 'validate-alpha';
                    break;
                case 'email':
                    $class = 'validate-email';
                    break;
                case 'url':
                    $class = 'validate-url';
                    break;
                case 'date':
                    // @todo DATE FORMAT
                    break;
            }
        }

        return $class;
    }

    /**
     * Return array of validate classes
     *
     * @param boolean $withRequired
     * @return array
     */
    protected function _getValidateClasses($withRequired = true)
    {
        $classes = array();
        if ($withRequired && $this->isRequired()) {
            $classes[] = 'required-entry';
        }
        $inputRuleClass = $this->_getInputValidateClass();
        if ($inputRuleClass) {
            $classes[] = $inputRuleClass;
        }

        return $classes;
    }

    /**
     * Return original entity value
     * Value didn't escape and filter
     *
     * @return string
     */
    public function getValue()
    {
        $value = $this->getEntity()->getData($this->getAttributeObject()->getAttributeCode());
        if (strpos($this->getHtmlId(), 'billing') !== false) {
            $value = $this->getBillingDataFromSession($this->getAttributeObject()->getAttributeCode());
        }
        if (strpos($this->getHtmlId(), 'shipping') !== false) {
            $value = $this->getShippingDataFromSession($this->getAttributeObject()->getAttributeCode());
        }

        return $value;
    }

    public function getBillingDataFromSession($path)
    {
        $formData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values/billing');
        if (!empty($formData[$path])) {
            return $formData[$path];
        }

        return null;
    }

    public function getShippingDataFromSession($path)
    {
        $formData = Mage::getSingleton('checkout/session')->getData('onestepcheckout_form_values/shipping');
        if (!empty($formData[$path])) {
            return $formData[$path];
        }

        return null;
    }

    /**
     * Return HTML id for element
     *
     * @param string|null $index
     * @return string
     */
    public function getHtmlId($index = null)
    {
        $format = $this->_fieldIdFormat;
        if (!is_null($index)) {
            $format .= '_%2$s';
        }

        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    /**
     * Return HTML id for element
     *
     * @param string|null $index
     * @return string
     */
    public function getFieldName($index = null)
    {
        $format = $this->_fieldNameFormat;
        if (!is_null($index)) {
            $format .= '[%2$s]';
        }

        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    /**
     * Return HTML class attribute value
     * Validate and rules
     *
     * @return string
     */
    public function getHtmlClass()
    {
        $classes = $this->_getValidateClasses(true);

        return empty($classes) ? '' : ' ' . implode(' ', $classes);
    }

    /**
     * Check is attribute value required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->getAttributeObject()->getIsRequired();
    }

    /**
     * Return attribute label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getAttributeObject()->getStoreLabel();
    }

    /**
     * Set format for HTML element(s) id attribute
     *
     * @param string $format
     * @return Magegiant_Onestepcheckout_Block_Eav_Form_Renderer_Abstract
     */
    public function setFieldIdFormat($format)
    {
        $this->_fieldIdFormat = $format;

        return $this;
    }

    /**
     * Set format for HTML element(s) name attribute
     *
     * @param string $format
     * @return Magegiant_Onestepcheckout_Block_Eav_Form_Renderer_Abstract
     */
    public function setFieldNameFormat($format)
    {
        $this->_fieldNameFormat = $format;

        return $this;
    }
}
