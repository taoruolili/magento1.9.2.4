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
class Magegiant_Onestepcheckout_Adminhtml_Field_PositionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Load layout, set breadcrumbs
     *
     * @return Magegiant_Onestepcheckout_Adminhtml_Customer_AttributeController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('onestepcheckout')
            ->_addBreadcrumb(
                Mage::helper('onestepcheckout')->__('Customer'),
                Mage::helper('onestepcheckout')->__('Customer'))
            ->_addBreadcrumb(
                Mage::helper('onestepcheckout')->__('Manage Fields Position'),
                Mage::helper('onestepcheckout')->__('Manage Fields Position'));

        return $this;
    }

    /**
     * Attributes grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Billing Fields'));
        $this->_initAction();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction()
    {
        $billing_fields   = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('billing_fields'));
        $available_fields = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('available_fields'));
        $cnt              = 1;
        /*Update billing field*/
        foreach ($billing_fields as $attribute) {
            $entity_type_id    = $attribute[0];
            $attribute_code    = $attribute[1];
            $attribute_colspan = $attribute[2];
            Mage::getResourceModel('onestepcheckout/attribute')->updatePosition($entity_type_id, $attribute_code, $attribute_colspan, $cnt);
            $cnt++;
        }
        /*Update available field*/
        foreach ($available_fields as $attribute) {
            $entity_type_id    = $attribute[0];
            $attribute_code    = $attribute[1];
            $attribute_colspan = $attribute[2];
            $attribute         = Mage::getModel('eav/entity_attribute')->loadByCode($entity_type_id, $attribute_code);
            if ($attribute && $attribute->getId()) {
                $attribute->setIsRequired(false);
                $attribute->save();
            }
            Mage::getResourceModel('onestepcheckout/attribute')->updatePosition($entity_type_id, $attribute_code, $attribute_colspan, $cnt, false);
            $cnt++;
        }
        /*Generate Css*/
        $websiteCode   = Mage::app()->getRequest()->getParam('website');
        $storeCode     = Mage::app()->getRequest()->getParam('store');
        $css_generator = Mage::getSingleton('onestepcheckout/generator_css');
        $css_generator->generateCss($websiteCode, $storeCode, 'design');

        return 'success';
    }
}