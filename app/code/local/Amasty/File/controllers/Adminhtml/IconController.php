<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2014 Amasty (http://www.amasty.com)
 */

class Amasty_File_Adminhtml_IconController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/amfile');
        $this->_addContent($this->getLayout()->createBlock('amfile/adminhtml_icon'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction();
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('amfile/icon');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This icon no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Icon'));

        $data = Mage::getSingleton('adminhtml/session')->getIconData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('amfile_icon', $model);
        $this->_addContent($this->getLayout()->createBlock('amfile/adminhtml_icon_edit'));
        $this->_addLeft($this->getLayout()->createBlock('amfile/adminhtml_icon_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $id = $this->getRequest()->getParam('id');

            $model = Mage::getModel("amfile/icon")->load($id);

            unset($data['image']);
            if (isset($_FILES['image']['error']) && $_FILES['image']['error'] == 0)
            {
                $uploader = new Varien_File_Uploader('image');

                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

                $result = $uploader->save(Mage::getBaseDir('media') . DS . 'amfile' . DS);

                if ($result['error'] == 0)
                    $data['image'] = $result['file'];
            }

            $model->setData($data)->save();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The icon has been saved.'));

            if ($this->getRequest()->getParam('back'))
            {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
            $this->_redirect('*/*/');
        }
    }

    protected function _initAction() 
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/amfile_icons')
            ->_addBreadcrumb($this->__('Catalog'), $this->__('Catalog'))
            ->_addBreadcrumb($this->__('Product Attachments'), $this->__('Product Attachments'))
            ->_addBreadcrumb($this->__('Icons'), $this->__('Icons'));

        return $this;
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        $this->_deleteIcon($id);

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Icon deleted.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = Mage::app()->getRequest()->getParam('icons');

        foreach ($ids as $id)
            $this->_deleteIcon($id);

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Icons deleted.'));
        $this->_redirect('*/*/');
    }

    protected function _deleteIcon($id)
    {
        try {
            Mage::getModel('amfile/icon')
                ->load($id)
                ->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
    }
}
