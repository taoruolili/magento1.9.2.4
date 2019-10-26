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
 * @copyright   Copyright (c) 2014 Magegiant (http://www.magegiant.com/)
 * @license     http://www.magegiant.com/license-agreement.html
 */
class Magegiant_Onestepcheckout_Adminhtml_Customer_Address_AttributeController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Customer Address Entity Type instance
     *
     * @var Mage_Eav_Model_Entity_Type
     */
    protected $_entityType;

    /**
     * Return Customer Address Entity Type instance
     *
     * @return Mage_Eav_Model_Entity_Type
     */
    protected function _getEntityType()
    {
        if (is_null($this->_entityType)) {
            $this->_entityType = Mage::getSingleton('eav/config')->getEntityType('customer_address');
        }

        return $this->_entityType;
    }

    /**
     * Load layout, set breadcrumbs
     *
     * @return Magegiant_Onestepcheckout_Adminhtml_Customer_Address_AttributeController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('onestepcheckout')
            ->_addBreadcrumb(
                Mage::helper('onestepcheckout')->__('Customer'),
                Mage::helper('onestepcheckout')->__('Customer'))
            ->_addBreadcrumb(
                Mage::helper('onestepcheckout')->__('Manage Customer Address Attributes'),
                Mage::helper('onestepcheckout')->__('Manage Customer Address Attributes'));

        return $this;
    }

    /**
     * Retrieve customer attribute object
     *
     * @return Mage_Customer_Model_Attribute
     */
    protected function _initAttribute()
    {
        $attribute = Mage::getModel('customer/attribute');
        $websiteId = $this->getRequest()->getParam('website');
        if ($websiteId) {
            $attribute->setWebsite($websiteId);
        }

        return $attribute;
    }

    /**
     * Attributes grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('Manage Customer Address Attributes'));
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Create new attribute action
     *
     */
    public function newAction()
    {
        $this->addActionLayoutHandles();
        $this->_forward('edit');
    }

    /**
     * Edit attribute action
     *
     */
    public function editAction()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        /* @var $attributeObject Mage_Customer_Model_Attribute */
        $attributeObject = $this->_initAttribute()
            ->setEntityTypeId($this->_getEntityType()->getId());

        $this->_title($this->__('Manage Customer Address Attributes'));

        if ($attributeId) {
            $attributeObject->load($attributeId);
            if (!$attributeObject->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('onestepcheckout')->__('Attribute is no longer exists.')
                );
                $this->_redirect('*/*/');

                return;
            }
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('onestepcheckout')->__('You cannot edit this attribute.')
                );
                $this->_redirect('*/*/');

                return;
            }

            $this->_title($attributeObject->getFrontendLabel());
        } else {
            $this->_title($this->__('New Attribute'));
        }

        // restore attribute data
        $attributeData = $this->_getSession()->getAttributeData(true);
        if (!empty($attributeData)) {
            $attributeObject->setData($attributeData);
        }

        // register attribute object
        Mage::register('entity_attribute', $attributeObject);

        $label = $attributeObject->getId()
            ? Mage::helper('onestepcheckout')->__('Edit Customer Address Attribute')
            : Mage::helper('onestepcheckout')->__('New Customer Address Attribute');

        $this->_initAction()
            ->_addBreadcrumb($label, $label)
            ->renderLayout();
    }

    /**
     * Validate attribute action
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if (!$attributeId) {
            $attributeCode   = $this->getRequest()->getParam('attribute_code');
            $attributeObject = $this->_initAttribute()
                ->loadByCode($this->_getEntityType()->getId(), $attributeCode);
            if ($attributeObject->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('onestepcheckout')->__('Attribute with the same code already exists')
                );

                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        return Mage::helper('onestepcheckout/attribute_address')->filterPostData($data);
    }

    /**
     * Save attribute action
     *
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($this->getRequest()->isPost() && $data) {
            /* @var $attributeObject Mage_Customer_Model_Attribute */
            $attributeObject = $this->_initAttribute();
            /* @var $helper Magegiant_Onestepcheckout_Helper_Data */
            $helper = Mage::helper('onestepcheckout');

            //filtering
            try {
                $data = $this->_filterPostData($data);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                if (isset($data['attribute_id'])) {
                    $this->_redirect('*/*/edit', array('_current' => true));
                } else {
                    $this->_redirect('*/*/new', array('_current' => true));
                }

                return;
            }

            $attributeId = $this->getRequest()->getParam('attribute_id');
            if ($attributeId) {
                $attributeObject->load($attributeId);
                if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()) {
                    $this->_getSession()->addError(
                        Mage::helper('onestepcheckout')->__('You cannot edit this attribute.')
                    );
                    $this->_getSession()->addAttributeData($data);
                    $this->_redirect('*/*/');

                    return;
                }

                $data['attribute_code']  = $attributeObject->getAttributeCode();
                $data['is_user_defined'] = $attributeObject->getIsUserDefined();
                $data['frontend_input']  = $attributeObject->getFrontendInput();
                $data['is_user_defined'] = $attributeObject->getIsUserDefined();
                $data['is_system']       = $attributeObject->getIsSystem();
            } else {
                $data['backend_model']   = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
                $data['source_model']    = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_type']    = $helper->getAttributeBackendTypeByInputType($data['frontend_input']);
                $data['is_user_defined'] = 1;
                $data['is_system']       = 0;

                // add set and group info
                $data['attribute_set_id']   = $this->_getEntityType()->getDefaultAttributeSetId();
                $data['attribute_group_id'] = Mage::getModel('eav/entity_attribute_set')
                    ->getDefaultGroupId($data['attribute_set_id']);
            }

            if (isset($data['used_in_forms']) && is_array($data['used_in_forms'])) {
                $data['used_in_forms'][] = 'adminhtml_customer_address';
            }

            $defaultValueField = $helper->getAttributeDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $scopeKeyPrefix                          = ($this->getRequest()->getParam('website') ? 'scope_' : '');
                $data[$scopeKeyPrefix . 'default_value'] = $helper->stripTags(
                    $this->getRequest()->getParam($scopeKeyPrefix . $defaultValueField));
            }

            $data['entity_type_id'] = $this->_getEntityType()->getId();
            $data['validate_rules'] = $helper->getAttributeValidateRules($data['frontend_input'], $data);

            $attributeObject->addData($data);

            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $key) {
                    $attributeObject->setData('scope_' . $key, null);
                }
            }

            try {
                Mage::dispatchEvent('onestepcheckout_address_attribute_before_save', array(
                    'attribute' => $attributeObject
                ));
                $attributeObject->save();
                Mage::dispatchEvent('onestepcheckout_address_attribute_after_save', array(
                    'attribute' => $attributeObject
                ));

                $this->_getSession()->addSuccess(
                    Mage::helper('onestepcheckout')->__('The customer address attribute has been saved.')
                );
                $this->_getSession()->setAttributeData(false);
                if ($this->getRequest()->getParam('back', false)) {
                    $this->_redirect('*/*/edit', array(
                        'attribute_id' => $attributeObject->getId(),
                        '_current'     => true
                    ));
                } else {
                    $this->_redirect('*/*/');
                }

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));

                return;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('onestepcheckout')->__('An error occurred while saving the customer address attribute.')
                );
                $this->_getSession()->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));

                return;
            }
        }
        $this->_redirect('*/*/');

        return;
    }

    /**
     * Delete attribute action
     *
     */
    public function deleteAction()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if ($attributeId) {
            $attributeObject = $this->_initAttribute()->load($attributeId);
            if ($attributeObject->getEntityTypeId() != $this->_getEntityType()->getId()
                || !$attributeObject->getIsUserDefined()
            ) {
                $this->_getSession()->addError(
                    Mage::helper('onestepcheckout')->__('You cannot delete this attribute.')
                );
                $this->_redirect('*/*/');

                return;
            }
            try {
                $attributeObject->delete();
                Mage::dispatchEvent('onestepcheckout_address_attribute_delete', array(
                    'attribute' => $attributeObject
                ));
                $this->_getSession()->addSuccess(
                    Mage::helper('onestepcheckout')->__('The customer address attribute has been deleted.')
                );
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $attributeId, '_current' => true));

                return;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('onestepcheckout')->__('An error occurred while deleting the customer address attribute.')
                );
                $this->_redirect('*/*/edit', array('attribute_id' => $attributeId, '_current' => true));

                return;
            }
        }

        $this->_redirect('*/*/');

        return;
    }

    /**
     * Check whether attributes management functionality is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('onestepcheckout/attributes/customer_address_attributes');
    }
}
