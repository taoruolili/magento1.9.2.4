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

/**
 * Class MageWorkshop_DetailedReview_Model_Review
 *
 * @method int getStoreId()
 */
class MageWorkshop_DetailedReview_Model_Review extends Mage_Review_Model_Review
{
    const XML_PATH_EMAIL_TEMPLATE               = 'detailedreview/email_notify/template';
    const XML_PATH_EMAIL_RECEIVER               = 'detailedreview/email_notify/receiver';
    const XML_PATH_EMAIL_SENDER                 = 'detailedreview/email_notify/sender';
    const XML_PATH_EMAIL_COPY_TO                = 'detailedreview/email_notify/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'detailedreview/email_notify/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'detailedreview/email_notify/enabled';

    protected $_reviewsCountWithoutFilters = 0;

    protected $_reviewsCollection;

    public function getStore()
    {
        if ($storeId = $this->getStoreId()) {
            return Mage::app()->getStore($storeId);
        }
        return Mage::app()->getStore();
    }

    public function getOwnership()
    {
        if ( $dateOrder =  $this->getData('ownership') ) {
            $dateOrder = getdate(strtotime($dateOrder));
            $currentDate = getdate(time());
            $helper = Mage::helper('detailedreview');
            if ($ownershipYears = $currentDate['year'] - $dateOrder['year']) {
                return $helper->__('more than ') . $ownershipYears . $helper->__(' year(s)');
            }
            if ($ownershipMonths = $currentDate['mon'] - $dateOrder['mon']) {
                return $helper->__('more than ') . $ownershipMonths . $helper->__(' month(s)');
            }
            $ownershipDays = $currentDate['mday'] - $dateOrder['mday'];
            return ($ownershipDays / 7 < 1) ? $helper->__('less than 1 week') : $helper->__('more than ') . round($ownershipDays / 7) . $helper->__(' month(s)');
        }
        return false;
    }

    public function getHelpfulVotes()
    {
        return Mage::getModel('detailedreview/review_helpful')->getQtyHelpfulVotesForReview($this->getId());
    }

    public function getAllVotes()
    {
        return Mage::getModel('detailedreview/review_helpful')->getQtyVotesForReview($this->getId());
    }

//    public function getIsCustomerVoted() {
//        return Mage::getModel('detailedreview/review_helpful')->getIsCustomerVoted($this->getId());
//    }

    public function checkGuestIsVoted()
    {
        if(!Mage::getSingleton('customer/session')->IsLoggedIn() && Mage::getStoreConfig('detailedreview/settings/allow_guest_vote')){
            $helpfulCollection = Mage::getModel('detailedreview/review_helpful')->getCollection()
                    ->addFieldToFilter('remote_addr', array('eq' => Mage::helper('core/http')->getRemoteAddr()))
                    ->addFieldToFilter('review_id', array('eq' => $this->getReviewId()));
            if(!$helpfulCollection->getSize()){
                return false;
            }
        }else{
            return false;
        }
        return true;
    }

    public function validate()
    {

        if ( !Mage::getStoreConfig('detailedreview/settings/enable') ) {
            return parent::validate();
        }

        $errors = array();

        $helper = Mage::helper('customer');

        if (!Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
            $errors[] = $helper->__('Review summary can\'t be empty');
        }

        if (!Zend_Validate::is($this->getNickname(), 'NotEmpty')) {
            $errors[] = $helper->__('Nickname can\'t be empty');
        }

        if (!Zend_Validate::is($this->getDetail(), 'NotEmpty')) {
            $errors[] = $helper->__('Review can\'t be empty');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    public function getProductCollection()
    {
        if ( !Mage::getStoreConfig('detailedreview/settings/enable') ) {
            return parent::getProductCollection();
        }
        return Mage::getResourceModel('detailedreview/review_product_collection');
    }

    public function sendNewReviewEmail()
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('detailedreview')->canSendNewReviewEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $storeEmailAddresses = Mage::getStoreConfig('trans_email');

        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);
        $recipientName = $storeEmailAddresses['ident_support']['name'];
        $recipientEmail = $storeEmailAddresses['ident_support']['email'];
        $emailInfo->addTo($recipientEmail, $recipientName);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $senderKey = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId);
        $mailer->setSender($senderKey);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'review'        => $this,
                'product'        => Mage::getModel('catalog/product')->load($this->getEntityPkValue()),
                'review_link'        => Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product_review/edit/",array("id" => $this->getId()))

            )
        );
        $mailer->send();

        $this->setEmailSent(true);
        return $this;
    }

    /**
     * @param bool $processForce
     * @param int $range
     * @return Mage_Review_Model_Resource_Review_Collection|MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection
     */
    public function getReviewsCollection($processForce = false, $range = null)
    {
        if (is_null($this->_reviewsCollection) || $processForce) {
            $params = Mage::app()->getRequest()->getParams();
            $product = Mage::registry('product');
            /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $reviewsCollection */
            $reviewsCollection = Mage::getModel('review/review')->getCollection();
            $reviewsCollection->addOwnershipInfo()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', $product->getId());

            if (!$processForce) {
                $this->_reviewsCountWithoutFilters = $reviewsCollection->getSize();
                $reviewsCollection->resetTotalRecords();
            }

            $range = (isset($range) && $range != 0) ? $range : ((isset($params['st'])) ? $params['st'] : 0);
            if ($range != 0 && $range != 999 && $range != 1) {
                $reviewsCollection->addDateRangeFilter($range);
            }
            if ($range == 1) {
                $reviewsCollection->addUserReviewFilter();
            }

            if (isset($params['keywords'])) {
                $reviewsCollection->addKeywordsFilter($params['keywords']);
            }
            if (isset($params['vb'])) {
                $reviewsCollection->addVerifiedBuyersFilter();
            }
            if (isset($params['vr'])) {
                $reviewsCollection->addVideoFilter();
            }
            if (isset($params['ir'])) {
                $reviewsCollection->addImagesFilter();
            }
            if (isset($params['mr'])) {
                $reviewsCollection->addManuResponseFilter();
            }
            if (isset($params['hc'])) {
                $reviewsCollection->addHighestContributorFilter();
            }
            $reviewsCollection->setCustomOrder(Mage::getSingleton('detailedreview/review_sorting')->getCurrentSorting());

            if ($processForce) {
                return $reviewsCollection;
            }
            $this->_reviewsCollection = $reviewsCollection;
        }

        return $this->_reviewsCollection;
    }

    /**
     * @return int
     */
    public function getReviewsCountWithoutFilters()
    {
        return isset($this->_reviewsCountWithoutFilters) ? $this->_reviewsCountWithoutFilters : 0;
    }

    /**
     * @param $configPath
     * @return array|bool
     */
    protected function _getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

}
