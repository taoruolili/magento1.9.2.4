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

class MageWorkshop_DetailedReview_HelpfulController extends Mage_Core_Controller_Front_Action {

    public function voteAction() {
        $params = $this->getRequest()->getParams();
        $response = '';
        if (!empty($params)) {
            $helper = Mage::helper('detailedreview');
            //$session = Mage::getSingleton('core/session');
            $reviewHelpful = Mage::getModel('detailedreview/review_helpful')->setData($params);

            if(Mage::getSingleton('customer/session')->IsLoggedIn()){
                $reviewHelpful->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            }elseif(Mage::getStoreConfig('detailedreview/settings/allow_guest_vote')){
                $reviewHelpful->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr());
            }else{
//                echo json_encode(array('msg' => array('type' => 'error', 'text' => $helper->__('Unable to add your vote.'))));
//                $session->addError($helper->__('Unable to add your vote.'));
                //$this->_redirectReferer();
            }

            $validation = $reviewHelpful->validate();

            if ($validation === true) {
                try {
                    $reviewHelpful->save();
                    //$session->addSuccess($helper->__('Your vote has been added successfully.'));
                    $reviewId = $params['review_id'];
                    $helpful = $reviewHelpful->getQtyHelpfulVotesForReview($reviewId);
                    $unhelpful = $reviewHelpful->getQtyVotesForReview($reviewId) - $helpful;
                    $response = json_encode(array('helpful' => $helpful, 'unhelpful' => $unhelpful, 'msg'=> array('type' => 'success', 'text'=> $helper->__('Your vote has been added successfully.'))));
                } catch (Exception $e) {
                    //$session->addError($helper->__('Unable to add your vote.'));
                    $response = json_encode(array('msg' => array('type' => 'error', 'text' => $helper->__('Unable to add your vote.'))));
                }
            } else {
               // $session->addError($helper->__('Unable to add your vote. %s', implode(', ', $validation)));
                $response = json_encode(array('msg' => array('type' => 'error', 'text' => $helper->__('Unable to add your vote. %s', implode(', ', $validation)))));
            }
        }
        $this->getResponse()->setBody($response);
       // $this->_redirectReferer();
    }

}

