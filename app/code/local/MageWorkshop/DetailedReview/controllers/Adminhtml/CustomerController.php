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

class MageWorkshop_DetailedReview_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{

    public function massCustomerBanningAction()
    {
        $helper = Mage::helper('detailedreview');
        $customerIds = $this->getRequest()->getParam('customer');

        if (!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Please select item(s)'));
        }
        else
        {
            try
            {
                foreach ($customerIds as $customerId)
                {
                    $model = Mage::getModel('customer/customer');
                    $model->load($customerId);
                    $model->setIsBannedWriteReview($this->getRequest()->getParam('is_banned_write_review'))->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $helper->__(
                        'Total of %d record(s) were successfully saved', count($customerIds)
                    )
                );
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/customer');
    }

    public function massBanningAction()
    {
        $helper = Mage::helper('detailedreview');
        $reviewIds = $this->getRequest()->getParam('reviews');

        if (!is_array($reviewIds)) {
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Please select item(s)'));
        }
        else
        {
            try
            {
                foreach ($reviewIds as $reviewId)
                {
                    $reviewModel = Mage::getModel('review/review')->load($reviewId);
                    if ( $customerId = $reviewModel->getCustomerId() ) {
                        $customerModel = Mage::getModel('customer/customer')->load($customerId);
                        $customerModel->setIsBannedWriteReview(1)->save();
                        $authorIpModel = Mage::getModel('detailedreview/authorIps')->load($customerId, 'customer_id');
                    } else {
                        if ( !$authorIp = $reviewModel->getRemoteAddr() ) continue;
                        $authorIpModel = Mage::getModel('detailedreview/authorIps')->load($authorIp, 'remote_addr');
                    }

                    $date = Mage::app()->getLocale()->date();
                    $date->addDay($this->getRequest()->getParam('ban_author_for'));

                    $authorIpModel->setExpirationTime(Mage::getSingleton('core/date')->gmtDate(null, $date->getTimestamp()));
                    if ( !$authorIpModel->getId() ) {
                        $authorIpModel
                            ->setRemoteAddr($authorIp)
                            ->setCustomerId($customerId);
                    }
                    $authorIpModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $helper->__(
                        'Total of %d record(s) were processed', count($reviewIds)
                    )
                );
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/catalog_product_review');
    }
}

