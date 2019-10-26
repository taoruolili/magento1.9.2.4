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

class MageWorkshop_DetailedReview_Model_Mysql4_Review extends Mage_Review_Model_Mysql4_Review
{
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
//        $object->setData('image', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $object->getImage());
        return parent::_afterLoad($object);
    }
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            if (!$object->getCreatedAt())
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores(array($object->getStores(), 0));
        }
        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ( !Mage::getStoreConfig('detailedreview/settings/enable') ) {
            return parent::_afterSave($object);
        }

        $image = $object->getImage();
        $pros = Mage::app()->getRequest()->getParam('pros');
        $user_pros = Mage::app()->getRequest()->getParam('user_pros');
        if ($object->getPros() && (is_null($pros) && is_null($user_pros))) {
            $object->setPros(null);
        }
        $cons = Mage::app()->getRequest()->getParam('cons');
        $user_cons = Mage::app()->getRequest()->getParam('user_cons');
        if ($object->getCons() && (is_null($cons) && is_null($user_cons))) {
            $object->setCons(null);
        }

        /**
         * save detail
         */
        $detail = array(
            'title'         => $object->getTitle(),
            'video'         => $object->getVideo(),
            'image'         => ($image === null) ? '' : $image,
            'detail'        => $object->getDetail(),
            'good_detail'   => $object->getGoodDetail(),
            'no_good_detail'=> $object->getNoGoodDetail(),
            'pros'          => (is_array($object->getPros())) ? implode(',', $object->getPros()) : $object->getPros(),
            'cons'          => (is_array($object->getCons())) ? implode(',', $object->getCons()) : $object->getCons(),
            'recommend_to'  => $object->getRecommendTo(),
            'nickname'      => $object->getNickname(),
            'response'      => ($object->getResponse() === null) ? '' : $object->getResponse(),
            'sizing'        => $object->getSizing(),
            'body_type'     => $object->getBodyType(),
            'location'      => $object->getLocation(),
            'age'           => $object->getAge(),
            'height'        => $object->getHeight()
        );


        $select = $this->_getWriteAdapter()->select()
            ->from($this->_reviewDetailTable, 'detail_id')
            ->where('review_id=?', $object->getId());
        $detailId = $this->_getWriteAdapter()->fetchOne($select);

        if ($detailId) {
            $this->_getWriteAdapter()->update($this->_reviewDetailTable,
                $detail,
                'detail_id='.$detailId
            );
        }
        else {
            $detail['store_id']   = $object->getStoreId();
            $detail['customer_id']= $object->getCustomerId();
            $detail['review_id']  = $object->getId();
            $detail['remote_addr'] = Mage::helper('core/http')->getRemoteAddr();
            $this->_getWriteAdapter()->insert($this->_reviewDetailTable, $detail);
        }


        /**
         * save stores
         */
        $stores = $object->getStores();
        if(!empty($stores)) {
            $condition = $this->_getWriteAdapter()->quoteInto('review_id = ?', $object->getId());
            $this->_getWriteAdapter()->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'review_id'=> $object->getId()
                );
                $this->_getWriteAdapter()->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // reaggregate ratings, that depend on this review
        $this->_aggregateRatings(
            $this->_loadVotedRatingIds($object->getId()),
            $object->getEntityPkValue()
        );

        return $this;
    }

}
