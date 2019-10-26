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

require_once Mage::getModuleDir('controllers', 'Mage_Review') . DS . 'ProductController.php';
require_once Mage::getModuleDir('', 'MageWorkshop_DetailedReview') . DS . 'lib'. DS . 'recaptchalib.php';

class MageWorkshop_DetailedReview_ProductController extends Mage_Review_ProductController {

    public function postAction() {
        $helper = Mage::helper('detailedreview');
        $session = Mage::getSingleton('core/session');
        if ( !Mage::getStoreConfig('detailedreview/settings/enable') ) {
            return parent::postAction();
        }

        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }
        $data = $this->_processData($data);

        if (($product = $this->_initProduct()) && !empty($data)) {
            $validCaptcha = true;
            if (Mage::getStoreConfig('detailedreview/captcha/enabled')) {
                $validateCaptcha = $session->getCaptcha();
                if ($validateCaptcha != 'valid') {
                    $session->addError($helper->__('You have entered wrong captcha.'));
                    $validCaptcha = false;
                }
            }

            if ( Mage::helper('detailedreview')->checkFieldAvailable('user_pros_and_cons') ){
                $types = array('user_pros' => 'pros', 'user_cons' => 'cons');
                foreach ($types as $type => $value){
                    if (isset($data["$type"])){
                        $storeId = array(Mage::app()->getStore()->getId());
                        $user_proscons = explode(',',$data["$type"]);
                        foreach ($user_proscons as $item){
                            $item = trim(htmlspecialchars($item));
                            if ($item != ''){
                                if ($value == 'pros'){
                                    $entity_type = MageWorkshop_DetailedReview_Model_Source_EntityType::PROS;
                                }else{
                                    $entity_type = MageWorkshop_DetailedReview_Model_Source_EntityType::CONS;
                                }
                                $item_find = Mage::getModel('detailedreview/review_proscons')->getCollection()
                                    ->setType($entity_type)
                                    ->addFieldToFilter('name',array('eq' => $item));
                                $item_find->load();
                                if ($item_find->count()){
                                    if (!isset($data["$value"]) || !is_array($data["$value"])){
                                        $data["$value"] = array();
                                    }
                                    $data["$value"][] = $item_find->getFirstItem()->getEntityId();
                                }else{
                                    $model = Mage::getModel('detailedreview/review_proscons')
                                        ->setEntityType($entity_type)
                                        ->setStoreIds($storeId)
                                        ->setName($item)
                                        ->setStatus(MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_DISABLED)
                                        ->setWroteBy(MageWorkshop_DetailedReview_Model_Source_Common_Wroteby::CUSTOMER);
                                    $model->save();
                                    if (!isset($data["$value"]) || !is_array($data["$value"])){
                                        $data["$value"] = array();
                                    }
                                    $data["$value"][] = $model->getEntityId();
                                }
                            }
                        }
                    }
                }
            }

            // Check if customer write reviews without approving
            $autoApproveFlag = false;
            $customerGroup = Mage::getSingleton('customer/session')->getCustomerGroupId();

            if ($autoApproveGroups = Mage::getStoreConfig('detailedreview/settings/auto_approve')) {
                $autoApproveGroups = explode(',', $autoApproveGroups);
                $autoApproveFlag = in_array($customerGroup, $autoApproveGroups);
            }

            /* @var $session Mage_Core_Model_Session */
            $review = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */
            $checkImage = true;

            $files = $helper->uploadImages();
            if(!empty($files['images'])){
                $review->setData('image', implode(",",$files['images']));
            }

            $responseJson = array('success' => false);
            $helperJson = Mage::helper('core');
            $validate = $review->validate();
            if ($validate === true && $files['success'] && $validCaptcha) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId())
                            ->setStatusId($autoApproveFlag ? Mage_Review_Model_Review::STATUS_APPROVED : Mage_Review_Model_Review::STATUS_PENDING)
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->setStores(array(Mage::app()->getStore()->getId()))
                            ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                                ->addOptionVote($optionId, $product->getId());
                    }

                    $review->sendNewReviewEmail();
                    $review->aggregate();
                    if (Mage::getStoreConfig('detailedreview/settings/submit_review_ajax')) {
                        $responseJson['success'] = true;
                        if($autoApproveFlag){

                            $responseJson['messages'][] = $helper->__('Your review has been added.');
                        } else {
                            $responseJson['messages'][] = $helper->__('Your review has been accepted for moderation.');
                        }
                    } else {
                        if($autoApproveFlag){

                            $session->addSuccess($helper->__('Your review has been added.'));
                        } else {
                            $session->addSuccess($helper->__('Your review has been accepted for moderation.'));
                        }
                    }

                } catch (Exception $e) {
                    if (Mage::getStoreConfig('detailedreview/settings/submit_review_ajax')) {
                        $responseJson['content'] = '<p>' . $helper->__('Unable to post the review.') . '</p>';
                        $this->getResponse()->setBody($helperJson->jsonEncode($responseJson));
                        return $e;
                    } else {
                        $session->setFormData($data);
                        $session->addError($helper->__('Unable to post the review.'));
                    }
                }
            } else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $responseJson['messages'][] = $errorMessage;
                        $session->addError($errorMessage);
                    }
                } else {
                    if (Mage::getStoreConfig('detailedreview/settings/submit_review_ajax')) {
                        $responseJson['messages'][] = $helper->__('Unable to post the review.');
                    } else {
                        $session->addError($helper->__('Unable to post the review.'));
                    }

                }
                if (!$files['success']) {
                    foreach ($files['errors'] as $imageName => $errorMessages) {
                        foreach($errorMessages as $message) {
                            $responseJson['messages'][] = $this->__('Image \'%s\' has the following problem: ', $imageName) . $message;
                            $session->addError($message);
                        }
                    }
                }
            }
            if (Mage::getStoreConfig('detailedreview/settings/submit_review_ajax')) {
                $responseJson['html'] = false;
                if($responseJson['success'] && $autoApproveFlag) {
                    $this->loadLayout();
                    $block = $this->getLayout()->getBlock('reviews_wrapper');
                    if($html = $block->getChildHtml()) {
                        $responseJson['html'] = $this->_escapeTags($html);
                    }
                }

                $this->_wrapMessages($responseJson)
                    ->getResponse()
                    ->setBody($this->_escapeTags($helperJson->jsonEncode($responseJson)));
            } else {
                if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
                    $this->_redirectUrl($redirectUrl);
                    return;
                }
                $refererUrl = $this->_getRefererUrl();
                if ( preg_match('/.*\&show_popup=1.*/', $refererUrl) ) {
                    $this->_redirectUrl(preg_replace('/(.*)\&show_popup=1(.*)/', '$1$2', $refererUrl));
                    return;
                }
                $this->_redirectReferer();
            }
        }
    }

    protected function _escapeTags($string)
    {
        return str_replace('<', '[[', $string);
    }

    public function checkCaptchaAction(){
        $params = $this->getRequest()->getParams();
        $privateKey = Mage::getStoreConfig('detailedreview/captcha/private_key');
        if (isset($params["recaptcha_challenge_field"]) && isset($params["recaptcha_response_field"])) {
            $resp = recaptcha_check_answer ($privateKey,
                $_SERVER["REMOTE_ADDR"],
                $params["recaptcha_challenge_field"],
                $params["recaptcha_response_field"]);
            if (!$resp->is_valid) {
                Mage::getSingleton('core/session')->setCaptcha('invalid');
                $this->getResponse()->setBody('invalid');
            } else {
                Mage::getSingleton('core/session')->setCaptcha('valid');
                $this->getResponse()->setBody('valid');
            }
        } else {
            Mage::getSingleton('core/session')->setCaptcha('invalid');
            $this->getResponse()->setBody('invalid');
        }
    }



    private function _processData($data)
    {
        if (isset($data['title'])) { $data['title'] = htmlspecialchars($data['title']); }
        if (isset($data['video'])) { $data['video'] = htmlspecialchars($data['video']); }
        if (isset($data['image'])) { $data['image'] = htmlspecialchars(implode(",",$data['image'])); }
        if (isset($data['detail'])) { $data['detail'] = htmlspecialchars($data['detail']); }
        if (isset($data['good_detail'])) { $data['good_detail'] = htmlspecialchars($data['good_detail']); }
        if (isset($data['no_good_detail'])) { $data['no_good_detail'] = htmlspecialchars($data['no_good_detail']); }
        if (isset($data['nickname'])) { $data['nickname'] = htmlspecialchars($data['nickname']); }
        if (isset($data['location'])) { $data['location'] = htmlspecialchars($data['location']); }
        if (isset($data['age'])) { $data['age'] = htmlspecialchars($data['age']); }
        if (isset($data['height'])) { $data['height'] = htmlspecialchars($data['height']); }
        return $data;
    }

    /**
     * Show list of product's reviews
     *
     */
    public function listAction()
    {
        if(!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::listAction();
        } else {
            if ($product = $this->_initProduct()) {
                Mage::register('productId', $product->getId());
                $this->getResponse()->setRedirect($product->getProductUrl());
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }

    /**
     * Show details of one review
     *
     */
    public function viewAction()
    {
        if(!Mage::getStoreConfig('detailedreview/settings/enable')) {
            parent::viewAction();
        } else {
            $review = $this->_loadReview((int) $this->getRequest()->getParam('id'));
            if (!$review) {
                $this->_forward('noroute');
                return;
            }

            $product = $this->_loadProduct($review->getEntityPkValue());
            if (!$product) {
                $this->_forward('noroute');
                return;
            }
            $this->getResponse()->setRedirect($product->getProductUrl());

            $this->loadLayout();
            $this->_initLayoutMessages('review/session');
            $this->_initLayoutMessages('catalog/session');
            $this->renderLayout();
        }
    }

    protected function _wrapMessages(&$responseJson) {
        $responseJson['content'] = '';
        foreach($responseJson['messages'] as $message) {
            $responseJson['content'] .= '<p>' . $message . '</p>';
        }
        $responseJson['messages'] = $responseJson['content'];
        unset($responseJson['content']);
        return $this;
    }
}