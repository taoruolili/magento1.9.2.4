<?php
/**
 * MageWorkshop
 * Copyright (C) 2012  MageWorkshop <mageworkshophq@gmail.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2012 MageWorkshop Co. (http://mage-workshop.com)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <mageworkshophq@gmail.com>
 */

class MageWorkshop_DetailedReview_Controller_Adminhtml_Common extends Mage_Adminhtml_Controller_Action
{
    protected $_entityType;
    protected $_entityName;
    protected $_className;

    protected function _construct()
    {
        $this->_entityName = MageWorkshop_DetailedReview_Model_Source_EntityType::getEntityNameByType($this->_entityType);
        $this->_className = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($this->_entityType);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_' . $this->_className))
            ->renderLayout();
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Manage %s', $this->_entityName));
        $this->_initAction();
    }

    public function massUpdateStatusAction()
    {
        $entityIds = $this->getRequest()->getParam('review_proscons');
        if(!is_array($entityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select %s(s).', $this->_className));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('update_status');
                foreach ($entityIds as $entityId) {
                    $model = Mage::getModel('detailedreview/review_proscons')->load($entityId);
                    $model->setStatus($status)
                        ->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($entityIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e->getMessage(),'An error has been occured');
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected %s(s).', $this->_className));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massDeleteAction()
    {
        $entityIds = $this->getRequest()->getParam('review_proscons');
        if(!is_array($entityIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select %s(s).', $this->_className));
        } else {
            try {
                foreach ($entityIds as $entityId) {
                    $model = Mage::getModel("detailedreview/review_proscons")->load($entityId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($entityIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function saveAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id', false);
        if ($data = $this->getRequest()->getPost()) {
            $entity = Mage::getModel('detailedreview/review_proscons');
            if (!isset($data['entity_type']) || empty($data['entity_type'])){
                $data['entity_type'] = $this->_entityType;
            }
            try {
                if ($entityId) {
                    $entity->load($entityId);
                    $entity->addData($data);
                } else {
                    $entity
                        ->setData($data);
                }

                $entity->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The cons has been saved.'));

                /* Chech if Save and Continue */
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('entity_id' => $entityId, '_current'=>true));
                    return;
                }

                $this->getResponse()->setRedirect($this->getUrl('*/*/'));

                return;
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Review %s', $this->_entityName));

        $entityId = $this->getRequest()->getParam('entity_id');
        $entity = Mage::getModel("detailedreview/review_proscons")->load($entityId);

        if ($entity->getId() || $entityId == 0) {
            $this->_title($this->__('%s %s', $this->_entityName, $entityId ? '#'.$entityId : ''));

            Mage::register('proscons_data', $entity);

            $this->loadLayout();
            $this->_setActiveMenu("catalog/review");
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('%s Manager', $this->_entityName), Mage::helper('adminhtml')->__('%s Manager', $this->_entityName), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit %s', $this->_entityName), Mage::helper('adminhtml')->__('Edit %s', $this->_entityName));

            $this->_addContent($this->getLayout()->createBlock('detailedreview/adminhtml_' . $this->_className . '_edit'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('detailedreview')->__('The %s does not exist.', $this->_entityName));
            $this->_redirect('*/*/');
        }
    }

    public function deleteAction()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        try {
            Mage::getModel('detailedreview/review_proscons')->load($entityId)->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('detailedreview')->__('The %s has been deleted.', $this->_entityName)
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }


}