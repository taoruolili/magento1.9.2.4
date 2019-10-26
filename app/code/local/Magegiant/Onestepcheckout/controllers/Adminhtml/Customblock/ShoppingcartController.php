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
class Magegiant_Onestepcheckout_Adminhtml_Customblock_ShoppingcartController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magegiant_Onestepcheckout_Adminhtml_CheckoutpromotionController
     */
    protected function _initAction()
    {
        $this->_title($this->__('Onestepcheckout'))
            ->_title($this->__('Custom Block Rule'));
        $this->loadLayout()
            ->_setActiveMenu('onestepcheckout/customblock_shoppingcart')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Blocks Manager'),
                Mage::helper('adminhtml')->__('Block Manager')
            );

        return $this;
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction()
    {
        $this->_title($this->__('Onestepcheckout'))
            ->_title($this->__('Custom Block Rule'));
        $rule_id = $this->getRequest()->getParam('id');
        $model   = Mage::getModel('onestepcheckout/customblock_shoppingcart');
        if ($rule_id) {
            $this->_title($this->__('Edit Block'));
            $model->load($rule_id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('onestepcheckout')->__('This item no longer exists')
                );

                return $this->_redirect('*/*');
            }
        }
        else{
            $this->_title($this->__('Add Block'));
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        Mage::register('customblock_shoppingcart_data', $model);
        $this->loadLayout();
        $this->_setActiveMenu('onestepcheckout/customblock_shoppingcart');

        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Custom Block Manager'),
            Mage::helper('adminhtml')->__('Custom Block Manager')
        );
        $this->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Custom Block News'),
            Mage::helper('adminhtml')->__('Custom Block News')
        );

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true)
            ->addItem('js', 'tiny_mce/tiny_mce.js')
            ->addItem('js', 'mage/adminhtml/wysiwyg/tiny_mce/setup.js')
            ->addJs('mage/adminhtml/browser.js')
            ->addJs('prototype/window.js')
            ->addJs('lib/flex.js')
            ->addJs('mage/adminhtml/flexuploader.js');

        $this->_addContent($this->getLayout()->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_edit'))
            ->_addLeft($this->getLayout()->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_edit_tabs'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('onestepcheckout/customblock_shoppingcart');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                if (isset($data['from_date']) && $data['from_date'] instanceof Zend_Date) {
                    $data['from_date'] = $data['from_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
                }
                if (isset($data['to_date']) && $data['to_date'] instanceof Zend_Date) {
                    $data['to_date'] = $data['to_date']->toString(VARIEN_DATE::DATE_INTERNAL_FORMAT);
                }
                if (!empty($data['from_date']) && !empty($data['to_date'])) {
                    $fromDate = new Zend_Date($data['from_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);
                    $toDate   = new Zend_Date($data['to_date'], VARIEN_DATE::DATE_INTERNAL_FORMAT);

                    if ($fromDate->compare($toDate) === 1) {
                        throw new Exception($this->__("'To Date' must be equal or more than 'From Date'"));
                    }
                }
                $data['conditions'] = $data['rule']['conditions'];

                unset($data['rule']);
                $model->loadPost($data);

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('onestepcheckout')->__('Rule was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('checkoutpromotion')->__('Unable to find rule to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('onestepcheckout/customblock_shoppingcart');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Rule was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction()
    {
        $customblockIds = $this->getRequest()->getParam('onestepcheckout');
        if (!is_array($customblockIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($customblockIds as $customblockId) {
                    $customblock = Mage::getModel('onestepcheckout/customblock_shoppingcart')->load($customblockId);
                    $customblock->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted',
                        count($customblockIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction()
    {
        $customblockIds = $this->getRequest()->getParam('onestepcheckout');
        if (!is_array($customblockIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($customblockIds as $customblockId) {
                    Mage::getSingleton('onestepcheckout/customblock_shoppingcart')
                        ->load($customblockId)
                        ->setIsActive($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($customblockIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction()
    {
        $fileName = 'customblock.csv';
        $content  = $this->getLayout()
            ->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction()
    {
        $fileName = 'customblock.xml';
        $content  = $this->getLayout()
            ->createBlock('onestepcheckout/adminhtml_customblock_shoppingcart_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('onestepcheckout/customblock_shoppingcart');
    }
}